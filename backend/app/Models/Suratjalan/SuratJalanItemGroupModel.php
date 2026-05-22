<?php namespace App\Models\Suratjalan;
 
use CodeIgniter\Model;
 
class SuratJalanItemGroupModel extends Model
{
    protected $table = 'surat_jalan_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","sj_id","item_id","qty","notes", "npb_item_id", "flag", 'reject_notes', 'reject', 'short_desc', 'attachment'];
	
	protected function initialize()
    {
        $this->db = db_connect();
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
		$q = "select  i.item_no, i.item_name,  i.original_manufacture, i.manufacture_pn, i.specification, i.unit, sj.approved,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s 
			left join surat_jalan sj on sj.id = s.sj_id
			left join m_item i on i.id = s.item_id";
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

    function read_npb_part($id){
        helper(['Query_helper']);
        $db = $this->db;
        $query = $db->query("
		SELECT p.item_id, p.qty, p.allocation, p.notes FROM `npb_item` p
		WHERE p.id = $id
        ");
		if(!$query)
			return [false, $this->db->error()];
		return $query->getResult();
    }
}