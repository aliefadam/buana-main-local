<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class HelperModel extends Model
{
    protected $table = 'm_general';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'value', 'text', 'category', 'description'];
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    /* function paging($json){
        return $this->read($json->limit, $json->offset);
    } */

    function ddl($table){
        $db = db_connect();
		$table = explode('.', $table);
		if(count($table)==2){
			$schema = $table[0];
			$table = $table[1];
		}
		else {
			$schema = 'buanamultiteknik_internal';
			$table = $table[0];
		}
        $query = $db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE table_schema = '$schema' and table_name = '$table'");
        return $query->getResult();
    }
   
    function read($json){
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $db = db_connect();
        $query = $db->query("select ".join(',', $this->allowedFields)." from `$this->table` limit $offset, $limit");
        return $query->getResult();
    }
}