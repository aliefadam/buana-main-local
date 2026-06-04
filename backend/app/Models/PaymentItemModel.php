<?php namespace App\Models;

use CodeIgniter\Model;

class PaymentItemModel extends Model
{
	protected $table = 'payment_item';
	protected $returnType     = 'object';
	protected $primaryKey = 'id';
	protected $allowedFields = ["id",
	"invoice_id",
	"proof_url", 'flag', 'payment_id', 'invoice_payment_notes', ];
	
	protected function initialize()
	{
		$this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
	}

	private function cashAdvanceSource()
	{
		try {
			$financeDb = db_connect('finance');
			foreach (['cash_advances', 'cash_advance'] as $table) {
				if (
					$financeDb->tableExists($table) &&
					$financeDb->fieldExists('ticket_no', $table) &&
					$financeDb->fieldExists('reference_po', $table)
				) {
					return [$financeDb, $table];
				}
			}
		} catch (\Throwable $e) {
		}

		foreach (['cash_advances', 'cash_advance'] as $table) {
			if (
				$this->db->tableExists($table) &&
				$this->db->fieldExists('ticket_no', $table) &&
				$this->db->fieldExists('reference_po', $table)
			) {
				return [$this->db, $table];
			}
		}

		return null;
	}

	private function cashAdvanceSelectSql()
	{
		return "
		null as cash_advance_ticket_no,
		null as cash_advance_reference,
		null as cash_advance_match_type,";
	}

	private function applyCashAdvanceMatches(array $rows)
	{
		$source = $this->cashAdvanceSource();
		if (!$source || count($rows) === 0) {
			return;
		}

		[$cashAdvanceDb, $cashAdvanceTable] = $source;
		$references = [];

		foreach ($rows as $row) {
			foreach (['ref_invoice_no', 'po_no', 'pr_no', 'rfq_pr_no'] as $field) {
				$value = trim((string)($row->{$field} ?? ''));
				if ($value !== '') {
					$references[$value] = true;
				}
			}
		}

		if (count($references) === 0) {
			return;
		}

		$cashAdvances = $cashAdvanceDb->table($cashAdvanceTable)
			->select('ticket_no, reference_po')
			->whereIn('reference_po', array_keys($references))
			->get()
			->getResult();

		$cashAdvanceMap = [];
		foreach ($cashAdvances as $cashAdvance) {
			$reference = trim((string)($cashAdvance->reference_po ?? ''));
			$ticketNo = trim((string)($cashAdvance->ticket_no ?? ''));
			if ($reference === '' || $ticketNo === '') {
				continue;
			}

			if (!isset($cashAdvanceMap[$reference])) {
				$cashAdvanceMap[$reference] = [];
			}
			$cashAdvanceMap[$reference][$ticketNo] = true;
		}

		foreach ($rows as $row) {
			$matchedTickets = [];
			$matchedReferences = [];
			$matchType = null;

			foreach ([
				'Reference No' => 'ref_invoice_no',
				'PO' => 'po_no',
				'PR' => 'pr_no',
				'RFQ PR' => 'rfq_pr_no',
			] as $type => $field) {
				$reference = trim((string)($row->{$field} ?? ''));
				if ($reference === '' || !isset($cashAdvanceMap[$reference])) {
					continue;
				}

				if ($matchType === null) {
					$matchType = $type;
				}
				$matchedReferences[$reference] = true;
				foreach ($cashAdvanceMap[$reference] as $ticketNo => $_) {
					$matchedTickets[$ticketNo] = true;
				}
			}

			if (count($matchedTickets) === 0) {
				continue;
			}

			$tickets = array_keys($matchedTickets);
			$references = array_keys($matchedReferences);
			sort($tickets);
			sort($references);

			$row->cash_advance_ticket_no = implode(', ', $tickets);
			$row->cash_advance_reference = implode(', ', $references);
			$row->cash_advance_match_type = $matchType;
		}
	}

	function addPrefix($field){
		return 's.'.$field;
	}

	function read($json){
		helper(['Query_helper']);
		$limit = $json->limit ?? 10;
		$offset = $json->offset ?? 0;
		$sortBy = $json->sortBy ?? array();
		$sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
		$db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
		if(trim($where)=='')
			$where = 'where s.flag = 1';
		else
			$where .= ' and s.flag = 1';
		$q = "`$this->table` s left join invoice i on i.id = s.invoice_id left join m_supplier ms on ms.id = COALESCE(i.reimburse_id, i.supplier_id) 
		left join purchase_order p on p.id = i.po_id
		left join purchase_order po on po.id = p.id
		left join pr pr on pr.id = po.pr_id
		left join rfq_header rfq on rfq.id = coalesce(pr.rfq_id, po.rfq_id)
		LEFT JOIN m_project mp 
					on (mp.id = rfq.project_id)
					or (mp.project_no = rfq.mr_task_no)
					or (i.project_id = mp.id)
		";
		
		$cashAdvanceSelect = $this->cashAdvanceSelectSql();
		$q2 = "
		select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", i.payment_value, i.invoice_no, i.ref_invoice_no, i.ref_invoice_no as tef_invoice_no, i.total_price, i.invoice_date, i.as_reference, i.invoice_doc_url, i.uraian, i.payment_pct, i.proof_of_transfer, i.is_paid, i.paid_date, i.debited_acc, i.transfer_type, i.transaction_code, i.reimburse_id, mp.project_no as project_no, mp.project_name as project_name,
		pr.pr_no, rfq.pr_no as rfq_pr_no,
		$cashAdvanceSelect
		ms.name as supplier_name, ms.bank, ms.bank_account_no, ms.currency, bank_account_name, ms.bic_swift_code, p.title as title,
		case when i.payment_pct_fiat != 0 then (payment_pct_fiat + i.invoice_charge) - i.invoice_reduction
		when i.as_reference = 0 then
			((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - i.invoice_reduction
		else
			(i.payment_amount + i.invoice_charge) - i.invoice_reduction
		end as invoice_total_price, p.id as po_id, p.po_no
		from $q";
		$query = $db->query("select * from ($q2
		) s $where $order limit $offset, $limit");

		if(!$query)
			return [$this->db->error(), false];
		
		$res = $query->getResult();
		$this->applyCashAdvanceMatches($res);
	
		$totalQuery = $db->query("select count(*) as total from ($q2) s $where");
		$totalQuery = $totalQuery->getResult();
		return [$res, $totalQuery[0]->total];
	}

	/* function lastNumber($y, $id){
		$query = $this->db->query("select (select dept_code from m_department where id = '$id') as dept_code, LPAD(IFNULL( (SELECT no FROM `$this->table` where `year` = '$y' order by id desc limit 1), 0)+1, 4, 0) as no, IFNULL( (SELECT no FROM `$this->table` where `year` = '$y' order by id desc limit 1), 0)+1 as number");
		
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	} */
}
