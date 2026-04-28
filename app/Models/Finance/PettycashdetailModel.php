<?php namespace App\Models\Finance;
 
use CodeIgniter\Model;
 
class PettycashdetailModel extends Model
{
    protected $table = 'petty_cash_detail';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","petty_cash_id","rincian","bukti","pos_akun_kode","debet","kredit",
	"created_by","created_date","modified_by","modified_date","flag","date_voucher","no_voucher","rekon_bank","ppn","pph23","other",
	"beneficiary_name","bank_account","npwp","po_reference","invoice_date","due_date","invoice_ref","nsfp","status","notes"];
	
	protected function initialize()
    {
        $this->db = db_connect('finance');
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
			c.name as created_by_name, m.name as modified_by_name, pa.nama, (s.debet-(s.kredit+s.ppn+s.pph23+s.other)) as net_amount,
			(SELECT kredit FROM petty_cash_detail WHERE rincian = 'saldo awal' and petty_cash_id = s.petty_cash_id LIMIT 1) + sum(s.debet - (s.kredit + s.ppn + s.pph23 + s.other)) OVER (ORDER BY id ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS saldo_berjalan,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join pos_akun pa on pa.kode = s.pos_akun_kode 
			left join buanamultiteknik_internal.users c on c.id = s.created_by
			left join buanamultiteknik_internal.users m on m.id = s.modified_by";
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