<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class DefaultModel extends Model
{
    /* protected $table = 'info';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","info","date", "uid"]; */
	
	protected function initialize()
    {
        $this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

}