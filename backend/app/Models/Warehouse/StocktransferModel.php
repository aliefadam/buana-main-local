<?php namespace App\Models\Warehouse;
 
use CodeIgniter\Model;
 
class StocktransferModel extends Model
{
    protected $table = 'stocktransfer';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","ask_approval_notes","approval_notes","approval2_notes","approval3_notes","notes","po_id","kabag_id","approved_by","approved_date","rejected_by","rejected_date","created_date","created_by","modified_date","modified_by","flag","approved","peminta_id", "approved2_by","approved2_date","approved3_by","approved3_date","photo","transfer_from", 'rev'];
	
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
			c.name as created_by_name, 
			m.name as modified_by_name,
			v.name as approved3_by_name,
			a.name as approved_by_name,
			r.name as rejected_by_name,
			p.name as peminta_name,
			uk.name as kabag_name,
			case 
				when s.rev>0 then
					concat(LPAD(s.id, 4, 0), '-rev.', LPAD(s.REV, 2, 0))
				else
					LPAD(s.id, 4, 0) 
			end as stocktransfer_no,
			po.po_no,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join purchase_order po on po.id = s.po_id
			left join users m on m.id = s.modified_by
			left join users v on v.id = s.approved3_by
			left join users a on a.id = s.approved_by
			left join users r on r.id = s.rejected_by
			left join users p on p.id = s.peminta_id
			left join users uk on uk.id = s.kabag_id
			
			";
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