<?php namespace App\Models\Transaction;
 
use CodeIgniter\Model;
 
class NpbModel extends Model
{
    protected $table = 'npb';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","ask_approval_notes","approval_notes","approval2_notes","approval3_notes","reject_notes","revisi_notes","reject2_notes","idx","npb_no","notes","peminta_id", "kabag_id", "project_id", "flag","tipe", "rejected_by","rejected_date","created_date","created_by","modified_date","modified_by", "approved","approved_by","approved_date","approved2_by","approved2_date","approved3_by","approved3_date", 'rev', 'npb__no', 'notify_id'] ;
	
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
		$q = "select (select count(*) from npb_item where npb_id = s.id) as item_count, u.name as peminta_name, uk.name as kabag_name, pp.project_no as project_no, pp.project_name, concat(LPAD(s.idx, 4, 0),'/NPB/',date_format(s.created_date, '%m/%Y'),
			case when rev >= 1 then
				concat('-REV', LPAD(s.REV, 2, 0))
			else '' end
		) as npbno, 
		    v.name as approved3_by_name,
			a.name as approved_by_name,
			r.name as rejected_by_name,
			c.name as created_by_name, 
			m.name as modified_by_name,
            npi.item_in_npb, subassembly_in_npb,   nowa.description as notify_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
			left join users u on u.id = s.peminta_id
			left join users uk on uk.id = s.kabag_id
			left join users v on v.id = s.approved3_by
			left join users a on a.id = s.approved_by
			left join users r on r.id = s.rejected_by
			left join m_project pp on pp.id = s.project_id
			left join m_nowa nowa on nowa.id=s.notify_id
            left join (SELECT group_concat(concat(';',ni.item_id,';')) as item_in_npb, group_concat(concat(';',ni.subassembly_id,';')) as subassembly_in_npb, ni.npb_id  FROM `npb_item` ni
            left join m_item i on i.id=ni.item_id left join m_subassembly msub on msub.id = ni.subassembly_id  group by ni.npb_id)npi on npi.npb_id=s.id";
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

	function complete($json){
		$session = session();
		$s = $session->get();
		$this->db->transBegin();
		$query = $this->db->query("update npb set flag = 4, completed_by = ".$s["userid"].", completed_date = now() where id = ".$json->npb_id);
		$this->db->transComplete();
		if($this->db->transStatus() === false){
			$this->db->transRollback();
			return [false, "update npb set flag = 4 and completed_by = ".$s["userid"]." where id = ".$json->npb_id];
		}
		else{
			$this->db->transCommit();
			return [true, "completed"];
		}
	}

    function info($id){
		$query = $this->db->query("select s.*, s.id as npb_id, concat(LPAD(s.idx, 4, 0),'/NPB/',date_format(s.created_date, '%m/%Y'), 
        case when rev > 1 then
            concat('-REV', LPAD(s.REV, 2, 0))
        else '' end
    ) as npbno, u.name as peminta_name, u.no_hp as peminta_phone, v.name as kabag_name, w.name as validator_name, x.name as approver_name, t.email as created_email, t.no_hp as created_phone, v.email as kabag_email, v.no_hp as kabag_phone, nowa.wa_id from npb s
        left join users t on t.id = s.created_by
		left join users u on u.id = s.peminta_id
		left join users v on v.id = s.kabag_id
		left join users w on w.id = s.approved_by
		left join users x on x.id = s.approved3_by
		left join m_nowa nowa on nowa.id=s.notify_id

		where s.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}
