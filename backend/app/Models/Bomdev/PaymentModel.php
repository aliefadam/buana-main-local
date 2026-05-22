<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class PaymentModel extends Model
{
    protected $table = 'payment';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
	"payment_no",
	"payment_date",
	"signed_payment_doc_url",
	"notes", 'flag', 'approved', 'approved1', 'approved1_date', 'approved2', 'approved2_date'];
	
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
		$q = "select distinct (select count(*) from payment_item where flag = 1 and payment_id = s.id) as item_count, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", md.id as dept_id, md.dept_name from `$this->table` s
		left join payment_item pi on pi.payment_id = s.id left join invoice iv on iv.id = pi.invoice_id left join purchase_order po on po.id = iv.po_id left join m_department md on md.id = po.dept_id
		";
		if(isset($json->debug)){
			return ["select * 
			from ($q)s $where $order limit $offset, $limit", 0];
		}
        $query = $db->query("select * 
		from ($q)s $where $order limit $offset, $limit");

		if(!$query)
			return [$this->db->error(), false];
		$res = $query->getResult();
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		$totalQuery = $totalQuery->getResult();
		return [$res, $totalQuery[0]->total];
    }

	function complete(){
		/* $q = "SELECT p.*, pi.invoice_id, i.payment_pct, i.invoice_no, i.total_price, poi.item_id, poi.order_qty, po.po_no, po.title, po.id as po_id FROM `payment` p 
		left join payment_item pi on pi.payment_id = p.id
		left join invoice i on i.id = pi.invoice_id
		left join purchase_order po on po.id = i.po_id
		left join purchase_order_item poi on poi.purchase_order_id = po.id
		WHERE p.flag = 1 and p.approved = 4 and po.is_complete = 0"; */

		$q = "SELECT pi.invoice_id, i.payment_pct, i.invoice_no, i.total_price, po.po_no, po.title, po.id as po_id FROM `payment` p 
		left join payment_item pi on pi.payment_id = p.id and pi.flag = 1
		left join invoice i on i.id = pi.invoice_id and i.flag = 1
		left join purchase_order po on po.id = i.po_id and po.flag = 1
		left join purchase_order_item poi on poi.purchase_order_id = po.id and poi.flag = 1
		WHERE p.flag = 1 and p.approved = 4 and po.is_complete = 0 and i.payment_pct = 100 group by pi.invoice_id, i.payment_pct, i.invoice_no, i.total_price, po.po_no, po.title, po.id";

		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

	function transferstock($json, $user_id){
		$q = "SELECT p.*, pi.invoice_id, i.payment_pct, i.invoice_no, i.total_price, poi.item_id, poi.order_qty, po.po_no, po.title, po.id as po_id, bi.required_qty, bi.required_qty_nis, poi.allocation FROM `payment` p 
		left join payment_item pi on pi.payment_id = p.id and pi.flag = 1
		left join invoice i on i.id = pi.invoice_id and i.flag = 1
		left join purchase_order po on po.id = i.po_id and po.flag = 1
		left join purchase_order_item poi on poi.purchase_order_id = po.id and poi.flag = 1
		left join bom_header bh on bh.id = poi.bom_id and bh.flag = 1
		left join bom_item bi on bi.bom_header_id = bh.id and bi.flag = 1
		WHERE p.flag = 1 and p.approved = 4 and po.is_complete = 0 and i.payment_pct = 100 and po_id = ".$json->po_id;

		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		
		$data = $query->getResult();
		$query = [];
		$po = [];
		foreach ($data as $key => $val) {
			if($val->required_qty!=null){
				$query[] = "('".$val->item_id."', 'NI', '".$val->required_qty."', '".$val->po_id."', '$user_id')";
				$po[] = $val->po_id;
			}
			if($val->required_qty_nis!=null){
				$query[] = "('".$val->item_id."', 'NIS', '".$val->required_qty_nis."', '".$val->po_id."', '$user_id')";
				$po[] = $val->po_id;
			}
			if($val->required_qty_nis==null && $val->required_qty==null){
				$query[] = "('".$val->item_id."', '".($val->allocation == '' ? 'NI' : $val->allocation)."', '".$val->order_qty."', '".$val->po_id."', '$user_id')";
				$po[] = $val->po_id;
			}
		}
		
		if(count($query) > 0){
			$this->db->transBegin();
			$param = implode(',', $query);
			$this->db->query("insert into stock(item_id, allocation, qty, po_id, created_by) values ".$param);
			$param = implode(',', $po);
			$this->db->query("update purchase_order set is_complete = 1 where id in (".$param.")");
			$this->db->transComplete();
			if($this->db->transStatus() === false){
				$this->db->transRollback();
				return [false, $this->db->error()];
			}
			else{
				$this->db->transCommit();
				return [true, "Inserted"];
			}
		}
		return [true, "No Data"];
	}
}