<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class DataModel extends Model
{
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }
   
    function data($q){
        $db = db_connect();
        $query = $db->query("select * from ($q)s");
        
		if(!$query)
			return [false, $this->db->error()]; 
		return [true, is_bool($query) ? 'executed!' : $query->getResult()];
    }
}