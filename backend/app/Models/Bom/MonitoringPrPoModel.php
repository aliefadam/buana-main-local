<?php namespace App\Models\Bom;
 
use CodeIgniter\Model;
 
class MonitoringPrPoModel extends Model
{
    // protected $table = 'purchase_order';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
//     protected $allowedFields = ["id",
// 		"po_no",
// 		"dept_id",
// 		"po_date",
// 		"eta_date",
// 		"received_date",
// 		"order_type",
// 		"supplier_id",
// 		"promised_delivery_date",
// 		"currency",
// 		"exchange_rate",
// 		"rate_date",
// 		"final_quote_url",
// 		"signed_po_url", "signed_pr_url", "no", "year", "charge1", "charge2", "charge1_desc", "charge2_desc", "note", "title", 'flag', 'approved', 'ref_offer_no', 'ref_internal_bmt', 'pr_id', 'rfq_id', 'askapproval_note', 'ask_approval_date', 'approval_date', 'approval_by', 'approval_2_date', 'approval_2_by', 'rejected_by', 'rejected_date', 'modified_date', 'modified_by', "approval_note", "created_by", "created_date", "new_po_no",
// 		"reject_note1",
// 		"reject_note2",
// 		"payment_term",
// 		"despatch",
// 		"shipping_address",
// 		"miscellaneous",
// 		"discount_desc",
// 		"other_charge",
// 		"discount",
// 		"complete",
//         "ask_canceled_date",
//         "ask_canceled_note",
//         "canceled_by",
//         "canceled_note",
//         "canceled_date",
//         "canceled_2_by",
//         "canceled_2_date"
// 	];
	
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
		/*$q = "select r.rfq_no, r.mr_task_no, r.modified_date as rfq_modified_date, r.rfq_group, r.allocation,
			r.created_date as rfq_created_date, s.id, s.id as po_id, r.id as rfq_id, concat(rg.id, '-', rg._ragicId) as ragic_id,
			(select coalesce(modified_date, created_date) as date from rfq_comment where rfq_id = r.id order by created_date desc limit 1) as rfq_hist_date, s.currency,
			r.status as rfq_status, s.po_no, s.title, s.dept_id, s.approved, s.created_date as po_created_date, s.signed_pr_url, s.final_quote_url, s.approved as po_approved, s.new_po_no, s.is_complete, s.complete, pr.pr_no, s.flag, c.grand_total_price,
			rg.order_id, rg.status as ragic_status,  u.name as po_created_by, coalesce((select sum(i.payment_pct) from invoice i where i.po_id = s.id and i.flag > 0), 0) as total_payment_pct, c.grand_total_price+s.charge1+s.charge2-s.discount as grand_total, COALESCE((SELECT (sum( pi.complete_qty/pi.order_qty))/(count(pi.item_id))*100 from purchase_order_item pi WHERE pi.purchase_order_id=s.id and pi.flag=1),0) as total_received_pct
			 from `$this->table` s 
			left join pr pr on pr.id=s.pr_id
			left join rfq_header r on r.id = s.rfq_id
			left join ragic rg on rg.order_id = r.reference_no
			left join users u on u.id = s.created_by
			left join (
                select sum(order_qty * price_per_item) as grand_total_price, purchase_order_id
                from purchase_order_item where flag = 1
                group by purchase_order_id
            ) c on c.purchase_order_id = s.id";*/
		$q = "
		/*
    PrPo Query
    */
    SELECT 
      GROUP_CONCAT(
        DISTINCT po.po_no 
        ORDER BY 
          po.po_no
      ) AS ipo_no, 
      -- Menampilkan po_no, bukan purchase_order_id
      GROUP_CONCAT(
        DISTINCT poi.purchase_order_id 
        ORDER BY 
          poi.purchase_order_id
      ) AS ipo_id, 
      pr.*, 
      # (pr.orderQty / NULLIF(pr.qty, 0) * 100) AS total_ordered_percentage,
      # (pr.ReceivedQty / NULLIF(pr.orderQty, 0) * 100) AS total_received_percentage
      CONCAT(
        ROUND(
          LEAST(
            (
              SUM(pr.orderQty) / NULLIF(
                SUM(pr.qty), 
                0
              )
            ) * 100, 
            100
          ), 
          2
        ), 
        '%'
      ) AS total_ordered_percentage, 
      -- Total Received Percentage berdasarkan total complete_qty dan total order_qty
      COALESCE(
        CONCAT(
          ROUND(
            (
              COALESCE(
                SUM(pr.ReceivedQty), 
                0
              ) / NULLIF(
                COALESCE(
                  SUM(pr.orderQty), 
                  0
                ), 
                0
              )
            ) * 100, 
            2
          ), 
          '%'
        ), 
        0
      ) AS total_received_percentage,
      CASE 
          WHEN 
              ROUND(
                  LEAST(
                      (SUM(pr.orderQty) / NULLIF(SUM(pr.qty), 0)) * 100, 
                      100
                  ), 
                  2
              ) = 100 
          AND 
              ROUND(
                  (
                      COALESCE(SUM(pr.ReceivedQty), 0) / NULLIF(COALESCE(SUM(pr.orderQty), 0), 0)
                  ) * 100, 
                  2
              ) = 100 
          THEN 'Complete' 
          ELSE 'Inprogress' 
      END AS in_status 
    FROM 
      (
        SELECT 
          pri.*, 
          case when pri.status = '0' then 'New' when pri.status = '-1' then 'Rejected' when pri.status = '-2' then 'Asking Cancel 1' when pri.status = '-3' then 'Asking Cancel 2' when pri.status = '-4' then 'Canceled' when pri.status = '3' then 'Final Approve' end as pr_status, 
          COALESCE(
            (
              SELECT 
                SUM(ps.qty) 
              FROM 
                pr_subledger ps 
              WHERE 
                ps.pr_part_id = pri.pp_id
                and ps.flag = 1
            ), 
            0
          ) AS qty, 
          COALESCE(
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
            ), 
            0
          ) as orderQty, 
          COALESCE (
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
            ), 
            0
          ) as ReceivedQty 
        FROM 
          (
            SELECT 
              po.signed_pr_url, 
              pr.flag, 
              pp.id AS pp_id, 
              pr.id AS pr_id, 
              i.id AS i_id, 
              po.id as po_id, 
              pr.pr_no, 
              poi.order_no, 
              i.item_name, 
              i.item_no, 
              pr.status, 
              pr.pr_subject, 
              u.name as name,
            	r.status as ragic_status,
              pr.created_date, 
              rfq.rfq_no, 
              pp.created_date as pp_created 
            FROM 
              pr_part pp 
              LEFT JOIN pr ON pr.id = pp.pr_id 
              left join users u on u.id = pr.created_by 
              left join rfq_header rfq on rfq.id = pr.rfq_id 
              left join purchase_order po on po.pr_id = pr.id 
            	left JOIN ragic r on r.order_id = rfq.reference_no 
                #ON (r.item_name REGEXP CONCAT('\\b', REPLACE(rfq.rfq_no, rfq.rfq_no, substring(rfq.rfq_no from 5)), '\\b'))  -- Mencocokkan seluruh rfq_no
                #OR (r.order_id = rfq.reference_no)
              LEFT JOIN m_item i ON i.id = pp.item_id 
              left join purchase_order_item poi on poi.purchase_order_id = po.id 
            WHERE 
              pp.flag = 1 
              and pr.status = 3 #and pr.pr_date > '2024-01-01'
              #and po.approved >= 0
              ) AS pri
      ) as pr 
      left join purchase_order_item poi on poi.purchase_order_id = pr.po_id 
      and poi.item_id = pr.i_id 
      LEFT JOIN purchase_order po ON po.id = poi.purchase_order_id -- Tambahkan join dengan purchase_order untuk mendapatkan po_no
    group by 
      pr_no 
    order by 
      pr.pp_created asc


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