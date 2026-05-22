<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class ListrfqprimaryModel extends Model
{
    protected $table = 'rfq_header';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","rfq_no", "title", "rfq_group", "status",  "allocation", "priority", "flag"];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function Listrfqprimary($json){
        helper(['Query_helper']);
        // $limit = $json->limit ?? 10;
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
		$q = "select ROW_NUMBER() OVER(order by s.rfq_no desc) AS no,  s.id, s.rfq_no, s.title, s.rfq_group, s.status, s.priority, s.allocation, s.flag from (select  ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." FROM `$this->table` s
        LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='New' or s.status='RFQ Submitted')
        UNION ALL
        select  s.id, s.rfq_no, s.title, s.rfq_group, s.status, s.priority,  s.allocation, s.flag
        FROM (select (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." FROM `$this->table` s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='New' OR r.status='RFQ'))s)s";
        $query = $db->query("select * from
		($q)s $where $order");
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		$res = $query->getResult();
        return [true, $res, $totalQuery[0]->total];
    }
}