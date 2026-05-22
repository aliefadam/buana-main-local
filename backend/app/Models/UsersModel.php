<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class UsersModel extends Model
{
    protected $table = 'users';
    protected $returnType     = 'object';
    protected $primaryKey = 'username';
    protected $allowedFields = ['username','name', 'id', 'auth', 'no_hp', 'is_group', 'email', 'flag', 'is_active', 'modified_by', 'modified_date', 'created_date', 'created_by'];
	
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
    //     if(trim($where)=='')
    //     $where = 'where s.flag = 1';
    // else
    //     $where .= ' and s.flag = 1';
		$q = "select (select GROUP_CONCAT(username) from users u1 left join user_group ug1 on ug1.group_id = u1.id where ug1.user_id = s.id and u1.flag=1 and ug1.flag=1) AS groups, date(s.created_date) as crea_date, date(s.modified_date) as mod_date,  u.name as created_by_name, v.name as modified_by_name, ".join(',', array_map(array($this, 'addPrefix'), ['username', 'name', 'id', 'no_hp', 'is_group', 'email', 'flag', 'is_active', 'modified_by', 'modified_date', 'created_by', 'created_date']))." from `$this->table` s
        left join users u on u.id = s.created_by
        left join users v on v.id = s.modified_by";
        $query = $db->query("select * from ($q) s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [$this->db->error(), false]; 
		
		$totalQuery = $db->query("select count(*) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
		return [$query->getResult(), $totalQuery[0]->total];
    }
   
    function auth($username = '', $password = ''){
        $db = db_connect();
        $options = [
            'cost' => 11  
        ];
        $query = $db->query("select id, username, auth, name, email from `users` where username = '$username' and is_group = 0 and (flag=1 or is_active = 1)");
        $data = $query->getResult();
        if(isset($data[0]))
            $data[0]->auth = password_verify($password, $data[0]->auth);
        return $data;
    }
   
    function groups($username = ''){
        $db = db_connect();
        $query = $db->query("
		select (select username from users where id = s.group_id) as group_name from (
			select group_id from user_group where user_id in (select group_id from user_group where user_id = (select id from users where username = '$username' and flag=1)) or user_id in (select username from users where username = '$username' and flag=1)
			union ALL 
			select group_id from user_group where user_id = (select id from users where username = '$username')  and flag=1
		)s
		");
		
		//select (select username from users where id = s.group_id) as group_name from user_group s where user_id = (select id from users where username = '$username')");
        $data = $query->getResult();
        return $data;
    }

    function info($id){
        $db = db_connect();
        $query = $this->db->query("select name from users where id=$id");
        
        if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
    }
}