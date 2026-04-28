<?php namespace App\Models\Warehouse;
 
use CodeIgniter\Model;
 
class SpbModel extends Model
{
    protected $table = 'spb';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","bom_receive_id","location","ask_approval_notes","approval_notes","approval2_notes","approval3_notes","reject_notes","revisi_notes","reject2_notes","notes","po_id","kabag_id","approved_by","approved_date","rejected_by","rejected_date","created_date","created_by","modified_date","modified_by","flag","approved","kelog_id", "approved2_by","approved2_date","approved3_by","approved3_date","photo","pr_id","pr_no", "arrival_date", 'pr_doc_url','po_doc_url', 'rev'];
	
	protected function initialize()
    {
        $this->db = db_connect();
        $this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

	function setTable($t){
		$table = $t;
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
		$q = "select * from (
				select
				date(s.created_date) as crea_date,
				date(s.modified_date) as mod_date,
				c.name as created_by_name, 
				m.name as modified_by_name,
				v.name as approved3_by_name,
				a.name as approved_by_name,
				r.name as rejected_by_name,
				p.name as kelog_name,
				uk.name as kabag_name,
				case 
					when s.rev>0 then
						concat(LPAD(s.id, 4, 0), '-rev.', LPAD(s.REV, 2, 0))
					else
						LPAD(s.id, 4, 0) 
				end as spb_no,
				po.po_no, 0 as is_bom,
				case when s.po_id != 0 then (select po.signed_pr_url from purchase_order po WHERE s.po_id=po.id) else s.pr_doc_url end as pr_doc_url_final,
				".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
				from `$this->table` s
				left join users c on c.id = s.created_by
				left join purchase_order po on po.id = s.po_id
				left join users m on m.id = s.modified_by
				left join users v on v.id = s.approved3_by
				left join users a on a.id = s.approved_by
				left join users r on r.id = s.rejected_by
				left join users p on p.id = s.kelog_id
				left join users uk on uk.id = s.kabag_id
			)s
			union all
			select * from (
				select
				date(s.created_date) as crea_date,
				date(s.modified_date) as mod_date,
				c.name as created_by_name, 
				m.name as modified_by_name,
				v.name as approved3_by_name,
				a.name as approved_by_name,
				r.name as rejected_by_name,
				p.name as kelog_name,
				uk.name as kabag_name,
				case 
					when s.rev>0 then
						concat(LPAD(s.id, 4, 0), '-rev.', LPAD(s.REV, 2, 0))
					else
						LPAD(s.id, 4, 0) 
				end as spb_no,
				po.po_no, 1 as is_bom,
				case when s.po_id != 0 then (select po.signed_pr_url from purchase_order po WHERE s.po_id=po.id) else s.pr_doc_url end as pr_doc_url_final,
				".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
				from `bom_spb` s
				left join users c on c.id = s.created_by
				left join purchase_order po on po.id = s.po_id
				left join users m on m.id = s.modified_by
				left join users v on v.id = s.approved3_by
				left join users a on a.id = s.approved_by
				left join users r on r.id = s.rejected_by
				left join users p on p.id = s.kelog_id
				left join users uk on uk.id = s.kabag_id
			)s2
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

    function info($id){
		$query = $this->db->query("select s.*, LPAD(s.id, 4, 0) as spb_no, u.name as kelog_name, u.no_hp as kelog_phone, v.name as kabag_name, v.no_hp as kabag_phone, w.name as validator_name, x.name as approver_name, t.email as created_email, t.no_hp as created_phone, v.email as kabag_email  from spb s
        left join users t on t.id = s.created_by
		left join users u on u.id = s.kelog_id
		left join users v on v.id = s.kabag_id
		left join users w on w.id = s.approved_by
		left join users x on x.id = s.approved3_by
		where s.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}