<?php

namespace App\Models;

use CodeIgniter\Model;

class invoiceFinanceModel extends Model

{

	protected $table = 'invoice';

	protected $returnType     = 'object';

	protected $primaryKey = 'id';

	protected $allowedFields = [
		"id",

		"invoice_no",

		"invoice_date",

		"invoice_doc_url",

		"ref_invoice_no",

		"po_id",

		"payment_pct",

		"payment_amount",

		"as_reference",

		"invoice_charge",

		"invoice_reduction",

		"notes",

		"supplier_id",

		"reimburse_id",

		"item_id",

		"charge1",

		"charge2",

		"grand_total_price",

		"charge1_desc",

		"charge2_desc",

		"total_price",
		'flag',

		"invoice_reduction_note",

		"invoice_charge_note",

		"uraian",

		'kode_pembayaran',

		'payment_pct_fiat',

		'is_paid',

		'paid_date',

		'proof_of_transfer',

		'petty_cash',

		'created_by',

		'created_date',

		'modified_by',

		'modified_date',

		'debited_acc',

		'charges_acc',

		'transfer_type',

		'transaction_code',

		'total_payment_po',

		'etd'

	];

protected function initialize()

	{

		$this->db = db_connect();

		$this->db->query("SET time_zone = '+07:00'");
	}

function addPrefix($field)
	{

		return 's.' . $field;
	}

function read($json)
	{

		helper(['Query_helper']);

		$limit = $json->limit ?? 10;

		$offset = $json->offset ?? 0;

		$sortBy = $json->sortBy ?? array();

		$sortDesc = $json->sortDesc ?? array();

		$order = order($sortBy, $sortDesc);

		$db = db_connect();

		if (isset($json->filter["id"]) && isset($json->is_payment)) {

			unset($json->filter["payment_id"]);

			unset($json->filterType["payment_id"]);
		}

		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);

		if (trim($where) == '')

			$where = 'where s.flag = 1';

		else

			$where .= ' and s.flag = 1';

		//print($json->filter["id"]);

//var_dump($json);

		//, case when as_reference = 0 then ((s.grand_total_price * (s.payment_pct/100)) + s.invoice_charge) - invoice_reduction 

		$q = "select date(s.created_date) as crea_date, s.use_credit_note as using_cn, cn.credit_no as nomor_cn,cn.id as credit_note_ids,s.credit_note_id as credit_note_id, date(s.modified_date) as mod_date, i.payment_id, py.flag as payment_flag, py.approved as payment_approved, " . join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)) . ", p.po_no, p.po_date, p.title as title, coalesce(p.currency, m.currency) as currency, m.name as supplier_name, m.bic_swift_code, m.bank_account_name, m.currency as supplier_currency, m.bank_account_no, m.bank, case when s.exchange_rate is null then coalesce(p.exchange_rate, 0) else s.exchange_rate end as exchange_rate, coalesce(s.admin_fee, 0) as admin_fee

		, (
			case when s.payment_pct_fiat != 0 then (payment_pct_fiat + s.invoice_charge) - invoice_reduction 
			when as_reference = 0 then ((s.grand_total_price * (s.payment_pct/100)) + s.invoice_charge) - invoice_reduction 
			else
				s.payment_amount + s.invoice_charge - invoice_reduction
			end
		) * (
			case 
				when upper(coalesce(p.currency, m.currency, 'IDR')) = 'IDR' then 1 
				else coalesce(nullif(p.exchange_rate, 0), 1) 
			end
		)
		as invoice_total_price_idr
		,
		
		cn.amount as credit_note_amount,

		coalesce((
			SELECT SUM(
				case 
					when ss.payment_pct_fiat != 0 then (ss.payment_pct_fiat + ss.invoice_charge) - ss.invoice_reduction
					when ss.as_reference = 0 then ((ss.grand_total_price * (ss.payment_pct/100)) + ss.invoice_charge) - ss.invoice_reduction
					else ss.payment_amount + ss.invoice_charge - ss.invoice_reduction
				end
			)
			FROM invoice ss
			left join payment_item pii on pii.invoice_id = ss.id and pii.flag = 1
			left join payment pyy on pyy.id = pii.payment_id
			where ss.credit_note_id = s.credit_note_id
			and pyy.flag = 1
		),0) as credit_note_used,

		(cn.amount - coalesce((
			SELECT SUM(
				case 
					when ss.payment_pct_fiat != 0 then (ss.payment_pct_fiat + ss.invoice_charge) - ss.invoice_reduction
					when ss.as_reference = 0 then ((ss.grand_total_price * (ss.payment_pct/100)) + ss.invoice_charge) - ss.invoice_reduction
					else ss.payment_amount + ss.invoice_charge - ss.invoice_reduction
				end
			)
			FROM invoice ss
			left join payment_item pii on pii.invoice_id = ss.id and pii.flag = 1
			left join payment pyy on pyy.id = pii.payment_id
			where ss.credit_note_id = s.credit_note_id
			and pyy.flag = 1
		),0)) as credit_note_remaining,

case when s.payment_pct_fiat != 0 then (payment_pct_fiat + s.invoice_charge) - invoice_reduction 

		when as_reference = 0 then ((s.grand_total_price * (s.payment_pct/100)) + s.invoice_charge) - invoice_reduction 

		else

			s.payment_amount + s.invoice_charge - invoice_reduction

		end

		as invoice_total_price, r.name as reimburse_name, r.bic_swift_code as r_bic_swift_code, r.bank_account_name as r_bank_account_name, r.currency as r_currency, r.bank_account_no as r_bank_account, r.bank as r_bank, s.po_Id as po_id2, u.name as created_by_name, v.name as modified_by_name,

		coalesce((

SELECT sum(case when ss.payment_pct_fiat != 0 then payment_pct_fiat

			when as_reference = 0 then (ss.grand_total_price * (ss.payment_pct/100))

			else

				s.payment_amount

			end

			) as total_paid_fiat FROM `invoice` ss where po_id = s.po_id and id != s.id and flag != 0 group by po_id

), 0) as total_paid_fiat, py.payment_no

		from `$this->table` s 

        left join purchase_order p on p.id = s.po_id and p.flag = 1 

		left join credit_note cn on cn.id = s.credit_note_id 

        left join m_supplier m on m.id = coalesce(p.supplier_id, s.supplier_id)

        left join m_supplier r on r.id = COALESCE(s.reimburse_id, s.supplier_id)

        left join (select * from payment_item where payment_id in (select id from payment where flag = 1)) i on i.invoice_id = s.id and i.flag = 1

		left join payment py on py.id = i.payment_id

		left join users u on u.id = s.created_by 

        left join users v on v.id = s.modified_by";

		$query = $db->query("select * from ($q) s $where $order limit $offset, $limit");

if (!$query)

			return [$this->db->error(), false];

$res = $query->getResult();

		/* foreach ($res as $value) {

			$id = $value->po_id;

			$query = $db->query("

				select sum(order_qty * price_per_item)+a.charge1+a.charge2 as grand_total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id

			");

			$query = $query->getResult();

			$value->grand_total = $query[0]->grand_total;

		} */

$totalQuery = $db->query("select count(*) as total from ($q)s $where");

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
