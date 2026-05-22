<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class ListrfqreadyforprModel extends Model
{
    protected $table = 'rfq_header';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","rfq_no", "title", "rfq_group", "status", "priority", "allocation", "created_by", "flag", "assigned_to"];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function readyforprRfq($json){
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
		$q = "select ROW_NUMBER() OVER(order by s.rfq_no desc) AS no,  s.id, s.rfq_no, s.title, s.rfq_group, s.status, s.priority, s.allocation, s.flag, s.created_by, s.created_by_name, s.assigned_to, s.assigned_to_name 
        from (select  ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).",  u.name as created_by_name, a.name as assigned_to_name  FROM `$this->table` s
        left join pr on pr.rfq_id=s.id and pr.flag=1 
        left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1 
        left join users a on a.id = s.assigned_to
		left join users u on u.id = s.created_by
        where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null))s";

        $fields="*";
        if(isset($json->optionUser))
            $fields = "distinct created_by, created_by_name";
        if(isset($json->optionAssigned))
            $fields = "distinct assigned_to, assigned_to_name";
    
        $query = $db->query("select $fields from ($q) s $where $order ");
            
        if(!$query)
            return [false, $this->db->error(), 0]; 
        
        $totalQuery = $db->query("select count($fields) as total from ($q) s $where");
        $totalQuery = $totalQuery->getResult();
        return [$query->getResult(), $totalQuery[0]->total];
    }
}