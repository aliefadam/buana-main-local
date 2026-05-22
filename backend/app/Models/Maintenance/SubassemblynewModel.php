<?php namespace App\Models\Maintenance;
 
use CodeIgniter\Model;
 
class SubassemblynewModel extends Model
{
    protected $table = 'subassembly_new';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","description", "type_subassy", "part_number","art_num", "sub_assembly_id","sub_assembly","sub_assembly_desc","index_no","aplikasi","qty","uom","brand",];
	
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
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$q = "select 
			c.name as created_by_name, m.name as modified_by_name, concat(s.part_number,' - ', s.description) as part_info, concat(s.sub_assembly, ' - ', s.sub_assembly_desc) as subassy_info, concat(s.part_number, '***', s.sub_assembly) as partsubassy,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by";
		$fields = "*";
		if(isset($json->subassy))
			$fields = "distinct sub_assembly, subassy_info";
		if(isset($json->part_info))
			$fields = "distinct part_info, art_num, part_number";
		/* if(isset($json->distinct)){
			$query = $db->query("select distinct sub_assembly, subassy_info from
			($q)s $where and (part_number = (select part_number from `$this->table` where id = $json->id)) $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
		}
		else{ */
			$query = $db->query("select $fields from
			($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
		//}
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
		/* if(isset($json->distinct)){
			$totalQuery = $db->query("select count(distinct sub_assembly) as total from ($q)s $where and (part_number = (select part_number from `$this->table` where id = $json->id))");
		}
		else */
			$totalQuery = $db->query("select count($fields) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}