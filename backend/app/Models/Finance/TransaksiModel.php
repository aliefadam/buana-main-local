<?php namespace App\Models\Finance;
 
use CodeIgniter\Model;
 
class TransaksiModel extends Model
{
    protected $table = 'transaksi';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","tanggal","judul","keterangan","attachment","akun_id","tipe","jumlah","saldo","created_by","modified_by"];
	
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
		$q = "select FORMAT(
            (SELECT SUM(rt.nominal) 
            FROM rincian_transaksi rt 
            WHERE rt.transaksi_id = s.id), 
            'N2', 'id-ID') AS jumlah_nominal,
			c.name as created_by_name, m.name as modified_by_name,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
			from `$this->table` s
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by";

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
    
    function dataLaporan(){
        $db = $this->db;
        $query = $db->query('
            SELECT 
            s.id, 
            s.tanggal, 
            s.judul, 
            s.keterangan, 
            s.attachment, 
            s.tipe, 
            concat(
                a.kode, " - ", a.nama, " - ", a.tipeAkun
            ) as coa, 
            rt.transaksi_id, 
            rt.rincian, 
            FORMAT(rt.nominal, 2) AS jumlah_nominal, 
            rt.is_paid 
            FROM 
            transaksi s 
            left join rincian_transaksi rt on rt.transaksi_id = s.id 
            left join akun a on a.id = rt.akun_id

        ');
        if (!$query) 
            return [false, $this->db->error()];
        return ($query->getResult());
        
    }
}