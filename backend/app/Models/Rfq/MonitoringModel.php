<?php namespace App\Models\Rfq;
 
use CodeIgniter\Model;
 
class MonitoringModel extends Model
{
    protected $table = 'rfq_header';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","rfq_no","title","status","priority","created_by","created_date","modified_by","modified_date","flag","rfq_group","assigned_to","reference_no","mr_task_no","item_no","item_description","pr_no","ragic_id","allocation","project_id","id","rfq_no","title","status","priority","created_by","created_date","modified_by","modified_date","flag","rfq_group","assigned_to","reference_no","mr_task_no","item_no","item_description","pr_no","ragic_id","allocation","project_id"];
	
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
		$q = "
        /* Monitoring RFQ-PR-PO */
        SELECT 
        rfq.id,
        rfq.rfq_no,
        rfq.title,
        rfq.priority,
        rfq.rfq_group,
        u.name as assigned_to,
        rfq.status as rfq_status,
        rfq.reference_no,
        r.status ragic_status,
        rfq.created_date AS rfq_created_date,
        #    i.item_no,
        #    i.item_name,
            COUNT(DISTINCT pr.id) AS total_pr,  
            COUNT(DISTINCT po.id) AS total_po,
            GROUP_CONCAT(DISTINCT pr.pr_no ORDER BY pr.pr_no) AS pr_list,
            GROUP_CONCAT(
            DISTINCT CONCAT(
                pr.pr_no, ' (', 
                CASE 
                    WHEN pr.status = '0' THEN 'New' 
                    WHEN pr.status = '-1' THEN 'Rejected' 
                    WHEN pr.status = '-2' THEN 'Asking Cancel 1' 
                    WHEN pr.status = '-3' THEN 'Asking Cancel 2' 
                    WHEN pr.status = '-4' THEN 'Canceled' 
                    WHEN pr.status = '3' THEN 'Final Approve'
                    ELSE 'Other Status' 
                END, 
                ')'
            ) 
            ORDER BY po.po_no
        ) AS pr_list_with_status,
            GROUP_CONCAT(DISTINCT po.po_no ORDER BY po.po_no) AS po_list,
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
        ) AS po_list_with_status,
            CASE 
                WHEN COUNT(DISTINCT pr.id) > 0 AND COUNT(DISTINCT po.id) = 0 THEN 'PR Sudah Ada, PO Belum Ada'
                WHEN COUNT(DISTINCT pr.id) > 0 AND COUNT(DISTINCT po.id) > 0 THEN 'PR dan PO Sudah Ada'
                ELSE 'Belum Ada PR dan PO'
            END AS status
        FROM 
            `$this->table` rfq
        LEFT JOIN 
            pr ON rfq.id = pr.rfq_id  
        LEFT JOIN 
            pr_part pp ON pr.id = pp.pr_id  
        LEFT JOIN ragic r 
                    #ON (r.item_name REGEXP CONCAT('\\b', REPLACE(rfq.rfq_no, rfq.rfq_no, substring(rfq.rfq_no from 5)), '\\b'))  
                    ON (r.order_id = rfq.reference_no)
        # LEFT JOIN 
        #     m_item i ON pp.item_id = i.id  
        LEFT JOIN 
            purchase_order po ON pr.id = po.pr_id  
        LEFT JOIN 
            users u on u.id = rfq.assigned_to
        WHERE rfq.flag > 0 
        GROUP BY 
            rfq.rfq_no #, i.item_no
        ORDER BY 
            rfq.created_date ASC#, i.item_no        
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