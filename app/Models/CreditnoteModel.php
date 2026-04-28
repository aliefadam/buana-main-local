<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class CreditnoteModel extends Model
{
    protected $table = 'credit_note';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","credit_no","amount","currency","file","supplier_id","po_id","keterangan","created_by","created_date"];
	
	protected function initialize()
    {
        $this->db = db_connect();
        $this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }
    
    public function getKursRate($currency, $amount){
        $html = file_get_contents('https://www.bi.go.id/id/statistik/informasi-kurs/transaksi-bi/Default.aspx');
        if(!$html){
            return ['status' => false, 'message' => 'Failed to fetch data from BI'];
        }

        $pattern = '/'.$currency.'\s*<\/td>\s*<td[^>]*>\s*([\d\.,]+)\s*<\/td>\s*<td[^>]*>\s*([\d\.,]+)\s*<\/td>/i';
        if (preg_match($pattern, $html, $m)) {
            $sell = floatval(str_replace(['.', ','], ['', '.'], $m[2]));
            return ['status' => true, 'sell'=>$sell, 'amount'=>$amount, 'exchange' => $amount * $sell];
        } else {
            return ['status' => false, 'message' => 'Currency not found or HTML structure changed.'];
        }
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
			c.name as created_by_name, m.name as modified_by_name, format(s.amount,2)as amounts,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." , ms.name as suplier_name, po.po_no as po_no
			from `$this->table` s
			left join users c on c.id = s.created_by
            left join m_supplier ms on ms.id = s.supplier_id
            left join purchase_order po on po.id = s.po_id
			left join users m on m.id = s.modified_by";
        $query = $db->query("select * from ($q)s $where $order limit $offset, $limit");
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }
}