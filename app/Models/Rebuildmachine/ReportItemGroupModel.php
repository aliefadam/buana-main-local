<?php namespace App\Models\Rebuildmachine;
 
use CodeIgniter\Model;
 
class ReportItemGroupModel extends Model
{
    protected $table = 'report_item_rebuild';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","parent_id","start_time","end_time","section_id", "partno_id", "art_no", "subassy_id", "npb_id", "item_replacement_id", "qty", "attachment", "notes", "flag", "ismatch", "type"];
	
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
		$q = "select  u.name as pic_name, i.item_no, concat(LPAD(npb.idx, 4, 0),'/NPB/',date_format(npb.created_date, '%m/%Y'), 
        case when rev > 1 then
            concat('-REV', LPAD(npb.REV, 2, 0))
        else '' end
    ) as npb_no, i.manufacture_pn,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
            left join npb on npb.id=s.npb_id
			left join pic_report_rebuild pr on pr.parent_id=s.parent_id
			left join users u on u.id = pr.user_id
            left join m_item i on i.id=s.item_replacement_id";
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

    function report($json){
        $query = $this->db->query("select ROW_NUMBER() OVER() AS no, a.*
			FROM (select b.date, a.*, case when a.type=1 then 'Replacement' when a.type=2 then 'Repair' when a.type=3 then 'Installation' when a.type=4 then 'Pre-assembled' end as type_report, u.name, i.manufacture_pn from report_item_rebuild a left join report_parent_rebuild b on b.id = a.parent_id  left join m_item i on i.id =a.item_replacement_id left join users u on u.id=b.created_by where a.flag=1 and b.flag=1 ) a");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
    }
}