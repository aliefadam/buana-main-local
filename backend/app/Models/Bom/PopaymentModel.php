<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class PopaymentModel extends Model
{
    protected $table = 'popayment';
    protected $returnType     = 'object';
    protected $primaryKey = 'pkid';
    protected $allowedFields = ["pkid","po_no","inrp","isdone","po_month","isapproved","invoice_total_price","uraian","done","approved","payment_pct_fiat","as_reference",
	"total_price","exchange_rate","rate_date","currency","invoice_id","invoice_no","payment_id","payment_no","payment_pct","grand_total_price","po_id","invoice_charge",
	"invoice_reduction"];

	protected function initialize()
    {
        $this->db = db_connect();
        $this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function addPrefixUnion($field){
		$fromPO = [
			"pkid"=>"uuid()",
			"po_no"=>"s2.po_no",
			"inrp"=>"inrp",
			"isdone"=>"0",
			"po_month"=>"date_format(`s2`.`po_date`,'%Y%m')",
			"isapproved"=>"0",
			"uraian"=>"",
			"done"=>"0",
			"payment_pct_fiat"=>"s2.grand_total_price",
			"as_reference"=>"0",
			"total_price"=>"s2.grand_total_price",
			"exchange_rate"=>"s2.exchange_rate",
			"rate_date"=>"s2.rate_date",
			"currency"=>"s2.currency",
			"invoice_id"=>"-",
			"invoice_no"=>"-",
			"payment_id"=>"-",
			"payment_no"=>"-",
			"payment_pct"=>"0",
			"grand_total_price"=>"s2.grand_total_price",
			"po_id"=>"s2.id",
			"invoice_charge"=>"0",
			"invoice_reduction"=>"0"
		];
		if(in_array($field, $fromPO))
       		return $fromPO[$field]." as ".$field;
		else
			return "null as $field";
    }

    function read($json){
        helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
		$sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
		$unionAll = join(',', array_map(array($this, 'addPrefixUnion'), $this->allowedFields));
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			
			union all

			select ".join(',', array_map(array($this, 'addPrefixUnion'), $this->allowedFields))." from (select s2.*, i.grand_total_price,
case when currency = 'IDR' then i.grand_total_price
else i.grand_total_price*s2.exchange_rate end as inrp
from purchase_order s2
left join (
	select sum(order_qty * price_per_item) as grand_total_price, purchase_order_id
	from purchase_order_item where flag = 1
	group by purchase_order_id
) i on i.purchase_order_id = s2.id left join invoice iv on iv.po_id = s2.id where iv.po_id is null)s2
			
			";


        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}