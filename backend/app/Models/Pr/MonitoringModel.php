<?php namespace App\Models\Pr;
 
use CodeIgniter\Model;
 
class MonitoringModel extends Model
{
    protected $table = '';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","pr_id","notes","type","created_date","created_by"];
	
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
		/*$q = "SELECT pr.pr_no as Pr_No, poi.po_no as 'PO No', pp.notes, poi.order_qty, poi.allocation as po_allocation, s.qty, spb.approved as spb_approved, s.allocation as stock_allocation,
		ps.description as subledger, ps.id as subledger_id
		FROM pr 
		inner join pr_part pp on pp.pr_id = pr.id
        inner join
        (
			select pr.id as pr_id, pr.pr_no, poi.*, po.po_no, po.flag as po_flag, po.id as po_id
            from purchase_order_item poi
			left join purchase_order po on po.id = poi.purchase_order_id
            left join pr on pr.pr_no = (case when LOWER(po.ref_internal_bmt) REGEXP '// pr' then 
            	trim(substring_index(LOWER(po.ref_internal_bmt), 'pr', -1)) 
            else 
            	null
            end) or pr.id = poi.pr_part_id
        ) poi on poi.item_id = pp.item_id and poi.pr_id = pp.pr_id
        left join pr_subledger ps on ps.id = poi.subledger_id
		left join spb on spb.po_id = poi.po_id and spb.flag != 0
		left join stock s on s.po_id = spb.po_id and s.flag != 0
		where pr.flag != 0";*/
        
        /*$q = "select pd.parent, ph.item_no as project_item, pd.item_no, pd.description, a.* from (
            SELECT pr.pr_no as Pr_No, 
                ps.description as subledger, ps.alokasi_pembelian, poi.po_no as 'PO No', pp.notes, poi.order_qty, poi.allocation as po_allocation, s.qty, spb.approved as spb_approved, s.allocation as stock_allocation
                FROM pr 
                left join pr_part pp on pp.pr_id = pr.id
                left join pr_subledger ps on  ps.pr_part_id = pp.id
                
                left join
                (
                    select pr.id as pr_id, pr.pr_no, poi.*, po.po_no, po.flag as po_flag, poi.purchase_order_id as po_id
                    from purchase_order_item poi
                    left join purchase_order po on po.id = poi.purchase_order_id
                    left join pr on pr.pr_no = (case when LOWER(po.ref_internal_bmt) REGEXP '// pr' then 
                        trim(substring_index(LOWER(po.ref_internal_bmt), 'pr', -1)) 
                    else 
                        null
                    end) or pr.id = poi.pr_part_id
                ) poi on poi.item_id = pp.item_id and poi.pr_id = pp.pr_id and poi.subledger_id = ps.id
                
                left join spb on spb.po_id = poi.po_id and spb.flag != 0
                left join stock s on s.po_id = spb.po_id and s.flag != 0
                where pr.flag != 0
                
            union ALL
            
            SELECT pr.pr_no as Pr_No, 
                ps.description as subledger, ps.alokasi_pembelian, poi.po_no as 'PO No', pp.notes, poi.order_qty, poi.allocation as po_allocation, s.qty, spb.approved as spb_approved, s.allocation as stock_allocation
                FROM pr 
                left join pr_part pp on pp.pr_id = pr.id
                
                left join
                (
                    select pr.id as pr_id, pr.pr_no, poi.*, po.po_no, po.flag as po_flag, poi.purchase_order_id as po_id
                    from purchase_order_item poi
                    left join purchase_order po on po.id = poi.purchase_order_id
                    left join pr on pr.pr_no = (case when LOWER(po.ref_internal_bmt) REGEXP '// pr' then 
                        trim(substring_index(LOWER(po.ref_internal_bmt), 'pr', -1)) 
                    else 
                        null
                    end) or pr.id = poi.pr_part_id
                ) poi on poi.item_id = pp.item_id and poi.pr_id = pp.pr_id and poi.subledger_id is null
                left join pr_subledger ps on  ps.id = poi.subledger_id
                
                left join spb on spb.po_id = poi.po_id and spb.flag != 0
                left join stock s on s.po_id = spb.po_id and s.flag != 0
                where pr.flag != 0
            
        )a 
        left join (select concat(pd.parent,'/',pd.item_no) as kode, description, parent, pd.item_no from project_item_detail pd
                   union ALL
                   select kode, item_no as description, '' as parent, '' as item_no from project_item_header pd2
                   )pd on pd.kode = a.alokasi_pembelian
                   left join project_item_header ph on ph.kode = pd.parent
        
        
                ";*/ 
        
        $q = "select ph.item_no as Project_Item, pd.item_no as Item_No, pd.description as Description, a.* 
		from (select concat(pd.parent, '/', pd.item_no) as kode, description, 
			  parent, pd.item_no from project_item_detail pd 
			union ALL 
			select  kode,  item_no as description,  '' as parent,  '' as item_no 
			from 
			project_item_header pd2
		  ) pd
		 left join project_item_header ph on ph.kode = pd.parent
		left join
		  (
			SELECT 
			  pr.pr_no as Pr_No, i.item_name as Item_Name, ps.description as Subledger, ps.qty as Qty, ps.allocation as Allocation, ps.alokasi_pembelian as Alokasi_Pembelian, poi.po_no as PO_No, poi.order_qty as Order_Qty,  poi.allocation as PO_Allocation,
			  pp.notes as Notes, spb.id as SPB_NO, s.qty as SPB_QTY, spb.approved as Spb_Status, 
			  s.allocation as Stock_Allocation 
			FROM 
			  pr 
			  left join pr_part pp on pp.pr_id = pr.id 
              left join m_item i on i.id = pp.item_id
			  left join pr_subledger ps on ps.pr_part_id = pp.id 
			  left join (
				select pr.id as pr_id, pr.pr_no, poi.*, po.po_no, po.flag as po_flag, poi.purchase_order_id as po_id 
				from 
				  purchase_order_item poi 
				  left join purchase_order po on po.id = poi.purchase_order_id 
				  left join pr on pr.pr_no = (
					case when LOWER(po.ref_internal_bmt) REGEXP '// pr' then trim(
					  substring_index(
						LOWER(po.ref_internal_bmt), 
						'pr', 
						-1
					  )
					) else null end
				  ) 
				  or pr.id = poi.pr_part_id
			  ) poi on poi.item_id = pp.item_id 
			  and poi.pr_id = pp.pr_id 
			  and poi.subledger_id = ps.id 
			  left join spb on spb.po_id = poi.po_id 
			  and spb.flag != 0 
			  left join stock s on s.po_id = spb.po_id 
			  and s.flag != 0 
			where 
			  pr.flag != 0 
			union ALL 
			SELECT 
			 pr.pr_no as Pr_No, i.item_name as Item_Name, ps.description as Subledger, ps.qty as Qty, ps.allocation as Allocation, ps.alokasi_pembelian as Alokasi_Pembelian, poi.po_no as PO_No, poi.order_qty as Order_Qty,  poi.allocation as PO_Allocation,
			  pp.notes as Notes, spb.id as SPB_NO, s.qty as SPB_QTY, spb.approved as Spb_Status, 
			  s.allocation as Stock_Allocation 
			FROM 
			  pr 
			  left join pr_part pp on pp.pr_id = pr.id 
              left join m_item i on i.id = pp.item_id
			  left join (
				select 
				  pr.id as pr_id, pr.pr_no, poi.*, po.po_no, po.flag as po_flag, poi.purchase_order_id as po_id 
				from 
				  purchase_order_item poi 
				  left join purchase_order po on po.id = poi.purchase_order_id 
				  left join pr on pr.pr_no = (
					case when LOWER(po.ref_internal_bmt) REGEXP '// pr' then trim(
					  substring_index(
						LOWER(po.ref_internal_bmt), 
						'pr', 
						-1
					  )
					) else null end
				  ) 
				  or pr.id = poi.pr_part_id
			  ) poi on poi.item_id = pp.item_id 
			  and poi.pr_id = pp.pr_id 
			  and poi.subledger_id is null 
			  left join pr_subledger ps on ps.id = poi.subledger_id 
			  left join spb on spb.po_id = poi.po_id 
			  and spb.flag != 0 
			  left join stock s on s.po_id = spb.po_id 
			  and s.flag != 0 
			where 
			  pr.flag != 0
		  ) a on a.alokasi_pembelian = pd.kode";

        /*$q="select pd.parent, ph.item_no as project_item, pd.item_no, pd.description, a.* from (SELECT pr.pr_no as Pr_No, ps.description as subledger, ps.alokasi_pembelian, poi.po_no as 'PO No', pp.notes, poi.order_qty, poi.allocation as po_allocation, s.qty, spb.approved as spb_approved, s.allocation as stock_allocation, ps.id as subledger_id
		FROM pr 
		left join pr_part pp on pp.pr_id = pr.id
        left join
        (
			select pr.id as pr_id, pr.pr_no, poi.*, po.po_no, po.flag as po_flag, po.id as po_id
            from purchase_order_item poi
			left join purchase_order po on po.id = poi.purchase_order_id
            left join pr on pr.pr_no = (case when LOWER(po.ref_internal_bmt) REGEXP '// pr' then 
            	trim(substring_index(LOWER(po.ref_internal_bmt), 'pr', -1)) 
            else 
            	null
            end) or pr.id = poi.pr_part_id
        ) poi on poi.item_id = pp.item_id and poi.pr_id = pp.pr_id
        left join pr_subledger ps on ps.id = poi.subledger_id
		left join spb on spb.po_id = poi.po_id and spb.flag != 0
		left join stock s on s.po_id = spb.po_id and s.flag != 0
		where pr.flag != 0)a
         left join (select concat(pd.parent,'/',pd.item_no) as kode, description, parent, pd.item_no from project_item_detail pd
                   union ALL
                   select kode, item_no as description, '' as parent, '' as item_no from project_item_header pd2
                   )pd on pd.kode = a.alokasi_pembelian
                   left join project_item_header ph on ph.kode = pd.parent";*/
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