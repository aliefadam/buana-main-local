<?php namespace App\Models\Document;
 
use CodeIgniter\Model;
 
class DocumentModel extends Model
{
    protected $table = 'document';
    protected $returnType     = 'object';
    protected $primaryKey = 'document_id';
    protected $allowedFields = ["document_id","name","alias","identifier","created_date","created_by","modified_date","modified_by","flag","root_id","root_table"];
	protected $document_create = "
		START TRANSACTION;

		CREATE TABLE `{tableName}` (
		`document_id` int(11) NOT NULL,
		`name` varchar(100) NOT NULL,
		`alias` varchar(100) DEFAULT NULL,
		`identifier` varchar(20) NOT NULL,
		`created_date` datetime NOT NULL DEFAULT current_timestamp(),
		`created_by` int(11) NOT NULL,
		`modified_date` datetime DEFAULT NULL,
		`modified_by` int(11) DEFAULT NULL,
		`flag` int(11) NOT NULL DEFAULT 1,
		`root_id` int(11) DEFAULT NULL,
		`root_table` varchar(50) DEFAULT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
		
		ALTER TABLE `{tableName}`
		ADD PRIMARY KEY (`document_id`);
		
		ALTER TABLE `{tableName}`
		MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;
		COMMIT;
	";
	
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
		$q = "select c.name as created_by_name, m.name as modified_by_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by";
        $query = $db->query("select * from
		($q)s
		$where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}