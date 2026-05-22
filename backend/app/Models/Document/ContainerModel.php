<?php namespace App\Models\Document;
 
use CodeIgniter\Model;
 
class ContainerModel extends Model
{
    protected $table = 'document_container';
    protected $returnType     = 'object';
    protected $primaryKey = 'children_document_id';
    protected $allowedFields = ["parent_table","children_table","parent_document_id","children_document_id","created_date","created_by","modified_date","modified_by"];
	
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
			c.name as created_by_name, m.name as modified_by_name, 
			p.name as parent_name, p.alias as parent_alias, p.identifier as parent_identifier,
			cd.name as children_name, cd.alias as childrenalias, cd.identifier as children_identifier,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join document p on p.document_id = s.parent_document_id
			left join document cd on cd.document_id = s.children_document_id
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by";
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