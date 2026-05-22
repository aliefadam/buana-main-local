<?php namespace App\Models;
 
use CodeIgniter\Model;
 
class PrPoItemGroupModel extends Model
{
//     protected $table = 'purchase_order_item';
//     protected $returnType     = 'object';
//     protected $primaryKey = 'id';
//     protected $allowedFields = ["id",
//     "purchase_order_id",
//     "bom_id",
//     "price_per_item", "order_qty", "item_id", "active", 'flag', 'pr_part_id', "allocation", 'complete_qty', 'created_date', 'created_by', 'modified_date', 'modified_by','order_no','subledger_id'
// ];
	
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
		$q = "
				/* 
        Query Monitoring Item for Monitor PR-PO
        */
        
        SELECT 
        GROUP_CONCAT(
          DISTINCT CONCAT(
              po.po_no, ' (', 
              CASE 
            			WHEN po.approved = 0 THEN 'New'
                  WHEN po.approved = 1 THEN 'Asking Approval 1' 
                  WHEN po.approved = 2 THEN 'Asking Approval 2'
            			WHEN po.approved = 3 THEN 'Final Approved'
            			WHEN po.approved = 4 THEN 'Clarification'
            			WHEN po.approved = 5 THEN 'Asking for Draft PO'
            			WHEN po.approved = 6 THEN 'Approval 2 Approved Draft'
            			WHEN po.approved = -1 THEN 'Rejected'
            			WHEN po.approved = -2 THEN 'Asking for canceled 1'
            			WHEN po.approved = -3 THEN 'Asking for canceled 2'
            			WHEN po.approved = -4 THEN 'Canceled'
                  ELSE 'Other Status' 
              END, 
              ')'
          ) 
          ORDER BY po.po_no
      ) AS ipo_no,
        	GROUP_CONCAT(DISTINCT poi.purchase_order_id ORDER BY poi.purchase_order_id) AS ipo_id,
          pri.pr_id as purchase_r_id, 
          pri.pr_id,
          pri.po_id, 
          pri.pr_no, 
          pri.pr_subject, 
          case when pri.status = '0' then 'New' when pri.status = '-1' then 'Rejected' when pri.status = '-2' then 'Asking Cancel 1' when pri.status = '-3' then 'Asking Cancel 2' when pri.status = '-4' then 'Canceled' when pri.status = '3' then 'Final Approve' end as Pr_Status, 
          pri.name as created_by, 
          pri.created_date, 
          pri.rfq_no, 
          pri.rfq_group, 
          pri.i_id AS item_id, 
          pri.item_no, 
          pri.item_name, 
          pri.flag, 
          pri.pp_created,
          (
            SELECT 
              SUM(ps.qty) 
            FROM 
              pr_subledger ps 
            WHERE 
              ps.pr_part_id = pri.pp_id
              and ps.flag = 1
          ) AS qty, 
          (
            SELECT 
              sum(poi.order_qty) 
            FROM 
              purchase_order_item poi 
              left join purchase_order po on po.id = poi.purchase_order_id 
            WHERE 
              po.pr_id = pri.pr_id 
              and po.approved >= 0
              and poi.item_id = pri.i_id 
              and poi.flag = 1 
              and po.flag = 1
            group by 
              poi.item_id
          ) as orderQty, 
          (
            SELECT 
              (
                sum(poi.complete_qty)
              ) 
            FROM 
              purchase_order_item poi 
              left join purchase_order po on po.id = poi.purchase_order_id 
            WHERE 
              po.pr_id = pri.pr_id 
              and poi.item_id = pri.i_id 
              and poi.flag = 1 
            group by 
              poi.item_id
          ) as ReceivedQty, 
          (
            SELECT 
              (poi.price_per_item) 
            FROM 
              purchase_order_item poi 
              left join purchase_order po on po.id = poi.purchase_order_id 
            WHERE 
              po.pr_id = pri.pr_id 
              and poi.item_id = pri.i_id 
              and poi.flag = 1 
            group by 
              poi.item_id
          ) as Itemprice, 
          (
            SELECT 
              (
                sum(
                  poi.price_per_item * poi.order_qty
                )
              ) 
            FROM 
              purchase_order_item poi 
              left join purchase_order po on po.id = poi.purchase_order_id 
            WHERE 
              po.pr_id = pri.pr_id 
              and poi.item_id = pri.i_id 
              and poi.flag = 1 
            group by 
              poi.item_id
          ) as Totalprice,
          GROUP_CONCAT(
            DISTINCT po.po_no, ' (', 
            COALESCE((
                SELECT SUM(i.payment_pct) 
                FROM invoice i 
                WHERE i.po_id = po.id 
                AND i.flag > 0 
                AND i.is_paid = 1
            ), 0), '%)'
            ORDER BY po.po_no
            SEPARATOR ', '
          ) AS paymentpo_status
        FROM 
          (
            SELECT 
              pp.id AS pp_id, 
              pr.id AS pr_id, 
              pr.pr_subject, 
              pr.status, 
              pr.created_date, 
              u.name, 
              rfq.rfq_no, 
              rfq.rfq_group, 
              i.id AS i_id, 
              po.id as po_id, 
              pr.pr_no, 
              i.item_name, 
              i.item_no, 
              pp.flag,
              pp.created_date as pp_created
            FROM 
              pr_part pp 
              LEFT JOIN pr ON pr.id = pp.pr_id 
              left join users u on u.id = pr.created_by 
              left join rfq_header rfq on rfq.id = pr.rfq_id 
              left join purchase_order po on po.pr_id = pr.id 
              LEFT JOIN m_item i ON i.id = pp.item_id 
            WHERE 
              pp.flag = 1 
              and pr.status = 3
          ) AS pri 
				left join purchase_order_item poi on poi.purchase_order_id = pri.po_id and poi.item_id = pri.i_id
        LEFT JOIN purchase_order po ON po.id = poi.purchase_order_id  -- Tambahkan join dengan purchase_order untuk mendapatkan po_no
        #where pri.pr_no = 202501024
        #where po.approved >= 0
        GROUP BY 
          pri.pr_no, 
          pri.i_id,
          pri.item_no
        order by 
          pri.pp_created asc
		";
        $query = $db->query("select * from ($q) s $where $order limit $offset, $limit");
        
		if(!$query)
			return [$this->db->error(), false];
	
		$totalQuery = $db->query("select count(*) as total from ($q) s $where");
		$totalQuery = $totalQuery->getResult();
        $res = $query->getResult();
        // if(count($res) > 0){
        //     $id = $res[0]->purchase_order_id;
        //     $query = $db->query("
        //         select a.charge1, a.charge2, (select sum(total) as total from (select order_qty*price_per_item as total from purchase_order_item where purchase_order_id = $id and flag=1)k) as total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id and s.flag=1 limit 1
        //     ");
        //     $query = $query->getResult();
        //     $res[0]->grand_total = $query[0]->total+$query[0]->charge1+$query[0]->charge2;
        // }
		return [$res, $totalQuery[0]->total];
    }

    /* readAll($pid){
        helper(['Query_helper']);
        $db = db_connect();
        $q = "`$this->table` s left join bom_header h on h.id = s.bom_id left join m_item i on i.id = s.item_id left join purchase_order p on p.id = s.purchase_order_id where purchase_order_id = $pid";
        $query = $db->query("select 
        (select sum(order_qty * price_per_item) from purchase_order_item where purchase_order_id = s.purchase_order_id) as total_item_price , 
        s.* from
        (select ".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", h.bom_no, i.item_name, i.item_no, i.original_manufacture, i.manufacture_pn, i.specification, p.currency, (s.order_qty*s.price_per_item) as total_price_per_item from $q) s");
        
        if(!$query)
            return [$this->db->error(), false];
    
        $totalQuery = $db->query("select count(*) as total from $q");
        $totalQuery = $totalQuery->getResult();
        $res = $query->getResult();
        if(count($res) > 0){
            $id = $res[0]->purchase_order_id;
            $query = $db->query("
                select sum(order_qty * price_per_item)+a.charge1+a.charge2 as grand_total from purchase_order_item s left join purchase_order a on a.id = s.purchase_order_id where s.purchase_order_id = $id
            ");
            $query = $query->getResult();
            $res[0]->grand_total = $query[0]->grand_total;
        }
        return [$res, $totalQuery[0]->total];
    } */

    function read_bom($id, $pid){
        helper(['Query_helper']);
        $db = db_connect();
		$q = " bom_header h inner join bom_item bi on bi.bom_header_id = h.id left join m_item i on i.id = bi.item_id where h.id = $id and i.is_active = 1";
        $query = $db->query("
            select s.id, s.item_id, s.order_qty from `$this->table` s where purchase_order_id = $pid and active = 1 and flag = 1
            union all
            select null as id, i.id as item_id, bi.order_qty from $q
        ");
		if(!$query)
			return [false, $this->db->error()];
		return $query->getResult();
    }

    function read_pr_part($id){
        helper(['Query_helper']);
        $db = $this->db;
        $query = $db->query("
		SELECT s.id, s.allocation, s.qty as order_qty, p.item_id FROM `pr_subledger` s
		left join pr_part p on p.id=s.pr_part_id
		WHERE s.id= $id
        ");
		/* $query = $db->query("
            select  *, (select sum(qty) from pr_subledger where pr_part_id = $id) as order_qty, (select allocation from pr_subledger where pr_part_id = $id) as allocation from pr_part where id = $id
        "); */
		if(!$query)
			return [false, $this->db->error()];
		return $query->getResult();
    }
}