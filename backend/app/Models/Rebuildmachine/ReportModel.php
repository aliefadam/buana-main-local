<?php namespace App\Models\Rebuildmachine;
 
use CodeIgniter\Model;
 
class ReportModel extends Model
{
    protected $table = 'report_parent_rebuild';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "pic", "date", "flag", "created_by", "created_date", "modified_by", "modified_date"];
	
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
		$q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date,
			c.name as created_by_name, 
            p.name as pic_name,
			m.name as modified_by_name,
            rpi.section_ri as section_id, rpi.type_ri as type, rpi.subassy_ri as s_subassy, rpi.partno_ri as partno_id, '' as s_section, '' as sub_register_id,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
            left join users m on m.id = s.modified_by
            left join users p on p.id = s.pic
            left join (SELECT group_concat(concat(';',ri.section_id,';')) as section_ri, group_concat(concat(';',ri.type,';')) as type_ri, group_concat(concat(';',ri.subassy_id,';')) as subassy_ri, group_concat(concat(';',ri.partno_id,';')) as partno_ri, ri.parent_id  FROM `report_item_rebuild` ri
            group by ri.parent_id)rpi on rpi.parent_id=s.id
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
    
    function info($json){
        $query = $this->db->query("select ri.subassy_id from report_parent_rebuild s
        left join report_item_rebuild ri on ri.parent_id = s.id 
        where s.date='2024-01-25'");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
    }

    function report($json){
        $query = $this->db->query("select ROW_NUMBER() OVER() AS no, a.*
			FROM (select b.date, a.*, case when a.type=1 then 'Replacement' when a.type=2 then 'Repair' when a.type=3 then 'Installation' when a.type=4 then 'Pre-assembled' end as type_report, u.name, i.manufacture_pn from report_item_rebuild a left join report_parent_rebuild b on b.id = a.parent_id  left join m_item i on i.id =a.item_replacement_id left join users u on u.id=b.created_by where a.flag=1 and b.flag=1 ) a");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
    }

}
