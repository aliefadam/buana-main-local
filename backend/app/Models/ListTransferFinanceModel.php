<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class ListTransferFinanceModel extends Model
{
    protected $table = 'payment';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id",
	"payment_no",
	"payment_date",
	"signed_payment_doc_url",
	"notes", 'flag', 'approved', 'approved1', 'approved1_date', 'approved2', 'approved2_date', 'done', 'rev', 'created_by', 'created_date', 'modified_by', 'modified_date', 'eff_date', 'charges_type', 'is_request_payment'];
	
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
		/* $q = "select distinct (select count(*) from payment_item where flag = 1 and payment_id = s.id) as item_count, ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", md.id as dept_id, md.dept_name from `$this->table` s
		left join payment_item pi on pi.payment_id = s.id and pi.flag = 1 left join invoice iv on iv.id = pi.invoice_id and iv.flag = 1 left join purchase_order po on po.id = iv.po_id and po.flag = 1 left join m_department md on md.id = po.dept_id
		"; */
		$q = "select distinct (select count(*) from payment_item where flag = 1 and payment_id = s.id) as item_count, 

IFNULL((
    select i.is_paid
    from payment p
    left join payment_item pi on pi.payment_id = p.id
    left join invoice i on i.id = pi.invoice_id
    where pi.flag = 1 and p.id = s.id
    and i.is_paid = 0
    limit 1
), 1) as status_pembayaran,
		pp.po_in_payment, date(s.approved1_date) as validated_date, date(s.approved2_date) as approved_date, date(s.created_date) as crea_date, date(s.modified_date) as mod_date, u.name as created_by_name, v.name as modified_by_name,  CONCAT_WS(';', 'internal@buanamultiteknik.com', 'titik@buanamultiteknik.com', 'finance@buanamultiteknik.com') as beneficiary_email,
		".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields))." 
		from `$this->table` s
		left join payment_item pi on pi.payment_id = s.id and pi.flag = 1
		left join (
		    SELECT group_concat(po.po_no) as po_in_payment, pi.payment_id FROM `payment_item` pi
            left join invoice i on i.id = pi.invoice_id
            left join purchase_order po on po.id = i.po_id
            group by pi.payment_id
		)pp on pp.payment_id = s.id
        left join users u on u.id = s.created_by 
        left join users v on v.id = s.modified_by
		";
		if(isset($json->debug)){
			return ["select *,
			
			 
        (select sum((a.invoice_total_price* case when po.currency = 'idr' then 1 else a.exchange_rate end)) as total_tp from (SELECT case when i.payment_pct_fiat != 0 then (i.payment_pct_fiat + i.invoice_charge) - invoice_reduction 
            when as_reference = 0 then ((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - invoice_reduction 
            else
            	i.payment_amount + i.invoice_charge - i.invoice_reduction
            end as invoice_total_price, coalesce(po.exchange_rate, i.exchange_rate) as exchange_rate,pi.payment_id
            
            FROM 
            payment_item pi left join invoice i on i.id = pi.invoice_id
            left join purchase_order po on po.id = i.po_id 
            where pi.flag = 1 and i.flag = 1)a where a.payment_id = s.id
        ) as totalrp
			
			from ($q)s $where $order limit $offset, $limit", 0];
		}
        $query = $db->query("select * ,

	
			 
        (select format(sum((a.invoice_total_price*(case when a.currency = 'idr' OR isnull(a.currency) then 1 else a.exchange_rate end))), 2) as total_tp from (SELECT case when i.payment_pct_fiat != 0 then (i.payment_pct_fiat + i.invoice_charge) - invoice_reduction 
            when as_reference = 0 then ((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - invoice_reduction 
            else
            	i.payment_amount + i.invoice_charge - i.invoice_reduction
            end as invoice_total_price, coalesce(po.exchange_rate, i.exchange_rate) as exchange_rate,pi.payment_id, coalesce(po.currency, ms.currency) as currency
            
            FROM 
            payment_item pi left join invoice i on i.id = pi.invoice_id
            left join purchase_order po on po.id = i.po_id 
			left join m_supplier ms on ms.id = i.supplier_id
            where pi.flag = 1 and i.flag = 1)a where a.payment_id = s.id
        ) as totalrp
		from ($q)s $where $order limit $offset, $limit");

		if(!$query)
			return [$this->db->error(), false];
		$res = $query->getResult();
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		$totalQuery = $totalQuery->getResult();
		return [$res, $totalQuery[0]->total];
    }

	function getOne($id){
		$q = "select * from payment where id = '$id'";
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

	function complete($json){
		/*$q = "SELECT pi.invoice_id, i.payment_pct, i.invoice_no, i.total_price, po.po_no, po.title, po.id as po_id FROM `payment` p 
		left join payment_item pi on pi.payment_id = p.id and pi.flag = 1
		left join invoice i on i.id = pi.invoice_id and i.flag = 1
		left join purchase_order po on po.id = i.po_id and po.flag = 1
		left join purchase_order_item poi on poi.purchase_order_id = po.id and poi.flag = 1
		WHERE p.flag = 1 and p.approved = 4 group by pi.invoice_id, i.payment_pct, i.invoice_no, i.total_price, po.po_no, po.title, po.id";*/

		helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);

		$q = "SELECT i.payment_pct, i.invoice_no, i.total_price, po.po_no, po.title, po.id as po_id, po.flag
		from invoice i
		left join purchase_order po on po.id = i.po_id and po.flag = 1
		left join purchase_order_item poi on poi.purchase_order_id = po.id and poi.flag = 1
		WHERE i.flag = 1 and po.approved = 3 and po.flag = 1 group by po.po_no, po.title, po.id, i.id, i.payment_pct, i.invoice_no, i.total_price";
		// and po.is_complete = 0 and i.payment_pct = 100 
		$query = $this->db->query("select * from ($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
	}

	function transferstock($json, $user_id){
		/*$q = "SELECT p.*, pi.invoice_id, i.payment_pct, i.invoice_no, i.total_price, poi.item_id, poi.order_qty, po.po_no, po.title, po.id as po_id, bi.required_qty, bi.required_qty_nis, poi.allocation FROM `payment` p 
		left join payment_item pi on pi.payment_id = p.id and pi.flag = 1
		left join invoice i on i.id = pi.invoice_id and i.flag = 1
		left join purchase_order po on po.id = i.po_id and po.flag = 1
		left join purchase_order_item poi on poi.purchase_order_id = po.id and poi.flag = 1
		left join bom_header bh on bh.id = poi.bom_id and bh.flag = 1
		left join bom_item bi on bi.bom_header_id = bh.id and bi.flag = 1
		WHERE p.flag = 1 and po.is_complete = 0 and po_id = ".$json->po_id." and poi.id = ".$json->id;*/
		
		$q = "SELECT poi.*, po.id as po_id from purchase_order_item poi left join purchase_order po on po.id = poi.purchase_order_id and po.flag = 1 left join bom_header bh on bh.id = poi.bom_id and bh.flag = 1 left join bom_item bi on bi.bom_header_id = bh.id and bi.flag = 1 WHERE poi.flag = 1 and po.is_complete = 0 and po.id = ".$json->po_id." and poi.id = ".$json->id;
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		
		$data = $query->getResult();
		$query = [];
		$id = [];
		foreach ($data as $key => $val) {
			/* if($val->required_qty!=null){
				$query[] = "('".$val->item_id."', 'NI', '".$json->qty."', '".$val->po_id."', '$user_id', '".$json->id."', '".$json->spb_id."', '".$json->notes."')";
				//$id[] = $json->id;
				//ni
			}
			else if($val->required_qty_nis!=null){
				$query[] = "('".$val->item_id."', 'NIS', '".$json->qty."', '".$val->po_id."', '$user_id', '".$json->id."', '".$json->spb_id."', '".$json->notes."')";
				//$po[] = $json->id;
				//nis
			}
			else if($val->required_qty_nis==null && $val->required_qty==null){
				$query[] = "('".$val->item_id."', '".$val->allocation."', '".$json->qty."', '".$val->po_id."', '$user_id', '".$json->id."', '".$json->spb_id."', '".$json->notes."')";
				//$po[] = $json->id;
				//$val->allocation
			}*/		
			$query[] = "('".$val->item_id."', '".$json->allocation."', '".$json->qty."', '".$val->po_id."', '$user_id', '".$json->id."', '".$json->spb_id."', '".$json->notes."', '".$json->photo."')";

			/* 
			if($val->required_qty_nis==null && $val->required_qty==null){
				$query[] = "('".$val->item_id."', '".($val->allocation == '' ? 'NI' : $val->allocation)."', '".$val->order_qty."', '".$val->po_id."', '$user_id')";
				$po[] = $val->po_id;
			} */

		}
		
		if(count($query) > 0){
			$this->db->transBegin();
			$param = implode(',', $query);
			$this->db->query("insert into stock(item_id, allocation, qty, po_id, created_by, purchase_order_item_id, spb_id, notes, photo) values ".$param);
			//$param = implode(',', $po);
			$this->db->query("update purchase_order_item set complete_qty = (complete_qty + ".$json->qty.") where id = ".$json->id);
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