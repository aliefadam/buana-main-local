<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class ListallocationModel extends Model
{
    protected $table = '';
    protected $returnType     = 'object';
    // protected $primaryKey = 'id';
    // protected $allowedFields = ["id","rfq_no", "title", "rfq_group", "status",  "allocation", "priority", "flag"];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function allocationList($json){
        helper(['Query_helper']);
        // $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        // if(trim($where)=='')
        //     $where = 'where s.flag = 1';
        // else
        //     $where .= ' and s.flag = 1';
		$q = "select SUM(s.total_rfq) FROM  (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='New' or s.status='RFQ Submitted') and s.allocation='Primary' UNION ALL  SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='New' OR r.status='RFQ') and s.allocation='Primary')s)s;";
        $query = $db->query("select * from
		($q)s $where $order");
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		$res = $query->getResult();

        /*
        $kemarin = date("Y-m-d")-1;
        $total2query = $db->query("select rfq_new as total2 from procurement_log where log_date = $kemarin");
        if(!$total2Query)
			return [false, $this->db->error()]; 
		$total2Query = $total2Query->getResult();
        $deltaquery = $total2query-$totalQuery[0]->total;
        */
		//return [true, $res, $totalQuery[0]->total, $deltaquery];
        return [true, $res, $totalQuery[0]->total];
    }
}