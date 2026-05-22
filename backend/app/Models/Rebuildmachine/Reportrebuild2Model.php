<?php namespace App\Models\Rebuildmachine;
 
use CodeIgniter\Model;
 
class Reportrebuild2Model extends Model
{
    protected $table = 'report_rebuild_2';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "date", "start_time","end_time","part_id", "type_activity", "qty", "npb_id", "item_replacement_id", "notes", "attachment", "created_by", "created_date", "report_id", "modified_by", "modified_date", "flag"];
	
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
		$q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date, u.name as created_by_name, t.name as modified_by_name, i.item_no, npb.npb__no,  i.manufacture_pn,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", m.sub_assembly, m.sub_assembly_desc, m.part_number, m.part_number_desc, m.article_number, m.section
			from `$this->table` s
			left join npb on npb.id=s.npb_id
			left join users u on u.id = s.created_by
			left join users t on t.id = s.modified_by
            left join m_item i on i.id=s.item_replacement_id
            left join m_partlist_rebuild m on m.id=s.part_id";
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

    function lastNumber(){
		$query = $this->db->query("select LPAD(IFNULL( (SELECT s.report_id as no FROM `$this->table` s order by s.id desc limit 1), 0)+1, 5, 0) as no;");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}