<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class DataModel extends Model
{
	
	protected function initialize()
    {
        $this->db = \Config\Database::connect('machine_gb');//db_connect('machine_gb');
		$this->db->query("SET time_zone = '+07:00'");
    }
   
    function data($q){
        $db = $this->db;
        $query = $db->query("select * from ($q)s");
        
		if(!$query)
			return [false, $this->db->error()]; 
		return [true, $query->getResult()];
    }
}