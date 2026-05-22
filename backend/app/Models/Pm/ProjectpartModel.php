<?php namespace App\Models\Pm;
 
use CodeIgniter\Model;
 
class ProjectpartModel extends Model
{
    protected $table = 'pm_project_part';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","flag","project_id","item_id","internal_part_no","design_date","ref_design_no","design_modified_date","design_modified_by","rfq_no","rfq_date","rfq_modified_date","rfq_modified_by", "pr_no", "pr_modified_date", "pr_modified_by","po_no","po_date","po_modified_date","po_modified_by","delivery_date","delivery_finished","delivery_modified_date","delivery_modified_by","complete_installation_date","complete_modified_date","complete_modified_by", "progress"];
	
	protected function initialize()
    {
        $this->db = db_connect();
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
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select 
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).",
			a.name as design_modified_by_name,
			b.name as rfq_modified_by_name,
			c.name as po_modified_by_name,
			d.name as delivery_modified_by_name,
			e.name as complete_modified_by_name,
			f.name as pr_modified_by_name,
			(
				IF(design_date IS NULL, 0,16) + IF(rfq_date IS NULL, 0,16) + IF(pr_no is NULL, 0, 17) + IF((select id from pm_po where pm_project_part_id = s.id limit 1) IS NULL, 0,17) + IF(delivery_finished = 0, 0,17) + IF(complete_installation_date IS NULL, 0,17)
			)as progress_done,
			(select id from pm_delivery where pm_project_part_id = s.id limit 1) as delivery,
			(select id from pm_po where pm_project_part_id = s.id limit 1) as po
			from `$this->table` s
			left join users a on a.id = s.design_modified_by
			left join users b on b.id = s.rfq_modified_by
			left join users c on c.id = s.po_modified_by
			left join users d on d.id = s.delivery_modified_by
			left join users e on e.id = s.complete_modified_by
			left join users f on e.id = s.pr_modified_by
			
			";
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		if(isset($json->debug))
			return [true, "select * from
			($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"), $totalQuery[0]->total];
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}