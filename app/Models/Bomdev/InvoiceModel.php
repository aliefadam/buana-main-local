<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class InvoiceModel extends Model
{
    protected $table = 'invoice';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
	"invoice_no",
	"invoice_date",
	"invoice_doc_url",
	"ref_invoice_no",
	"po_id",
	"payment_pct",
	"invoice_charge",
	"invoice_reduction",
	"notes", 
	"supplier_id",
	"item_id",
	"charge1",
	"charge2",
	"grand_total_price",
	"charge1_desc",
	"charge2_desc",
	"total_price", 'flag'];
	
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
		$q = "select i.payment_id, py.flag as payment_flag, py.approved as payment_approved, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", p.po_no, p.po_date, p.currency, m.name as supplier_name, m.bic_swift_code, m.bank_account_name, m.currency as supplier_currency, m.bank_account_no, m.bank 
		, ((s.grand_total_price * (s.payment_pct/100)) + s.invoice_charge) - invoice_reduction as invoice_total_price
		from `$this->table` s left join purchase_order p on p.id = s.po_id and p.flag = 1 left join m_supplier m on m.id = p.supplier_id left join payment_item i on i.invoice_id = s.id and i.flag = 1
		left join payment py on py.id = i.payment_id";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");

		if(!$query)
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
	
		$totalQuery = $db->query("select count(*) as total from ($q)s");
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