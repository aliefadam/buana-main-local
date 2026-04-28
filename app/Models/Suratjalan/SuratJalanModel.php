<?php namespace App\Models\Suratjalan;
 
use CodeIgniter\Model;
 
class SuratJalanModel extends Model
{
    protected $table = 'surat_jalan';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id", "sj_no", "sj_date", "place", "from_info", "to_info", "up", "flag", "created_date", "pengirim", "created_by", "modified_by", "modified_date", "approved", "approved_date", "approved_by", "rejected_date", "rejected_by", "idx", "ask_approval_notes", "approved_notes", "rev", "revised_by", "revised_date", "rejected_notes", "revised_notes", "ask_approval_date", "sj_notes", "jam_berangkat", "nopol", "nama", "no_hp", "jenis_kendaraan"];
	
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
		$q = "select date(s.created_date) as crea_date, date(s.modified_date) as mod_date,
			a.name as approved_by_name,
            rv.name as revised_name,
			r.name as rejected_by_name,
			c.name as created_by_name, 
            p.name as pengirim_name,
			m.name as modified_by_name,
            sji.item_in_sj,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
            left join users rv on rv.id = s.revised_by
			left join users m on m.id = s.modified_by
			left join users a on a.id = s.approved_by
            left join users p on p.id = s.pengirim
			left join users r on r.id = s.rejected_by
            left join (SELECT group_concat(concat(';',si.item_id,';')) as item_in_sj, si.sj_id  FROM `surat_jalan_item` si
            left join m_item i on i.id=si.item_id group by si.sj_id)sji on sji.sj_id=s.id";
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
 
	function lastNumber($frominfo){
		$query = $this->db->query("select ( select case when $frominfo=1 then 'SPZ' when $frominfo=2 then 'TRW-SJ' when $frominfo=3 then 'KPJ-SJ' when $frominfo=4 then 'KDR' end as from_no from surat_jalan limit 1) as from_code, LPAD(IFNULL( (SELECT cast(split_str(sj_no, '/', 1) as UNSIGNED) as no FROM `surat_jalan`
where fromroman(SPLIT_STR(sj_no, '/', 3)) = DATE_FORMAT(now(), '%m') and SPLIT_STR(sj_no, '/', 2) like concat('%', from_code,'%') and SPLIT_STR(sj_no, '/', 4) = DATE_FORMAT(now(), '%Y') order by id desc limit 1), 0)+1, 3, 0) as no");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
	
	 function info($id, $user){
		$query = $this->db->query("select sj.sj_no, x.name as approver_by_name,
		case when sj.from_info=1 then 'BMT Spazio' 
		    WHEN sj.from_info =2 then 'BMT Trowulan'
		    WHEN sj.from_info =3 then 'BMT KEPANJEN'
		END AS from_info, sj.to_info, sj.place, sj.sj_notes, u.name as pengirim_name, u.no_hp as pengirim_phone, sj.approved_date,  w.name as created_name, w.no_hp as created_phone from surat_jalan sj
		left join users u on u.id = sj.pengirim
        left join users w on w.id= sj.created_by
        left join users x on x.id =$user
		where sj.id=$id");
        
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}
