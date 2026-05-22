<?php namespace App\Models\Monitoring;
 
use CodeIgniter\Model;
 
class ListprreadyforpoModel extends Model
{
    protected $table = 'pr';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","pr_no", "pr_subject","status", "ask_canceled_date", "created_by", "flag"];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function readyforpoPr($json){
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
        $q = "select ROW_NUMBER() OVER (PARTITION by s.id) AS rn, s.id, s.pr_no, s.pr_subject, s.status, s.flag, s.created_by, created_by_name , in_po
from (
    
select s.*, u.name as created_by_name,
(select count(pi.id) from purchase_order_item pi left join purchase_order po on po.id=pi.purchase_order_id 
 where  pi.pr_part_id = pp.id  and ps.id=pi.subledger_id and po.approved>=0 and pi.flag=1
) as in_po
    
FROM `pr` s
left join pr_part pp on pp.pr_id=s.id
left join pr_subledger ps on ps.pr_part_id=pp.id
left join users u on u.id = s.created_by
where s.flag=1 and s.status=3 and s.ask_canceled_date is null and pp.flag=1 and ps.flag=1
    
)s where s.in_po=0";

        $fields="*";
        if(isset($json->optionUser))
			$fields = "distinct created_by, created_by_name";


            $query = $db->query("select $fields from ($q) s $where $order ");
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
            $totalQuery = $db->query("select count($fields) as total from ($q) s $where");
            $totalQuery = $totalQuery->getResult();
            return [$query->getResult(), $totalQuery[0]->total];
    }
}