<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class PaymentItemModel extends Model
{
    protected $table = 'payment_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
	"invoice_id",
	"proof_url", 'flag', 'payment_id', 'invoice_payment_notes'];
	
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
		$q = "`$this->table` s left join invoice i on i.id = s.invoice_id left join m_supplier ms on ms.id = i.supplier_id";
        $query = $db->query("select * from (
			select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", i.invoice_no, invoice_date,
			ms.name as supplier_name, ms.bank, ms.bank_account_no, ms.currency, bank_account_name, ms.bic_swift_code, 
			((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - i.invoice_reduction as invoice_total_price
			from $q
		) s $where $order limit $offset, $limit");

		if(!$query)
			return [$this->db->error(), false];
		
		$res = $query->getResult();
	
		$totalQuery = $db->query("select count(*) as total from $q");
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