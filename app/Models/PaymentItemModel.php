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
    	left join rfq_header rfq on rfq.id = pr.rfq_id
    	LEFT JOIN m_project mp 
                    on (mp.id = rfq.project_id)
                    or (mp.project_no = rfq.mr_task_no)
					or (i.project_id = mp.id)
		";
        
		$q2 = "
		select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", i.payment_value, i.invoice_no, i.total_price, i.invoice_date, i.as_reference, i.invoice_doc_url, i.uraian, i.payment_pct, i.proof_of_transfer, i.is_paid, i.paid_date, i.debited_acc, i.transfer_type, i.transaction_code, i.reimburse_id, mp.project_no as project_no, mp.project_name as project_name,
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