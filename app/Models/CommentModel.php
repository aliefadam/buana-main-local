<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class CommentModel extends Model
{
    protected $table = 'purchase_order_comment';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
	"notes",
	"purchase_order_id", "created_by", "created_date"];
	
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
		$q = "select u.name as created_by_name,
		".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
		from `$this->table` s 
		left join users u on u.id = s.created_by";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count(*) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }
}