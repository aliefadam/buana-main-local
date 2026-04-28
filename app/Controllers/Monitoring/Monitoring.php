<?php namespace App\Controllers\Pr; 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Pr\MonitoringModel;

class Monitoring extends ResourceController
{
    use ResponseTrait;
	
    public function monitoring(){
        $model = new MonitoringModel();
		$res = $model->db->query("
			select cast(now() as date) as log_date,
			(SELECT SUM(s.total_rfq) FROM  (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='New' or s.status='RFQ Submitted') UNION ALL  SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='New' OR r.status='RFQ'))s) s) as rfq_new,
			(SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Clarification') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Clarification RFQ' OR r.status='Clarification'))s) s) as rfq_clarification,
			(SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Quotation Received') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no
            WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Offer Sent to BMT' OR r.status='Quotation Submitted'))s) s) as rfq_quotation,
			(SELECT count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1 left join users u on u.id=s.created_by left join users x on x.id=s.assigned_to where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null)) as rfq_ready_for_pr,
			(SELECT count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1 left join users u on u.id=s.created_by left join users x on x.id=s.assigned_to where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null)) as pr_ready_for_pr,
			(select count(pr.pr_no)  from pr where (pr.status between 0 AND 2) and pr.flag=1) as pr_in_process,
			(SELECT  count(s.pr_no) FROM pr s  left join purchase_order po on po.pr_id=s.id and po.flag=1  where s.flag=1 and s.status=3 and s.ask_canceled_date is null and po.id is  null) as pr_ready_for_po,
			(SELECT  count(s.pr_no) FROM    pr s  left join purchase_order po on po.pr_id=s.id and po.flag=1  where s.flag=1 and s.status=3 and s.ask_canceled_date is null and po.id is  null) as po_ready_for_po, (select count(s.po_no)  from `purchase_order` s where s.flag=1 and s.approved=0) as po_in_process, 
			(select count(s.po_no)  from `purchase_order` s where s.flag=1 and (s.approved=1 or s.approved=2)) as po_ready_for_approval
			union all
			select * from (select * from procurement_log order by log_date desc limit 1)s;");
		$result = $res->getResult();
		return $this->respond(['status' => true, 'data' => $result], 200);
	}

	public function monitoring_log(){
        $model = new MonitoringModel();
		$res = $model->db->query("
		insert into procurement_log (log_date, rfq_new, rfq_clarification, rfq_quotation, rfq_ready_for_pr, pr_ready_for_pr, pr_in_process, pr_ready_for_po, po_ready_for_po, po_in_process, po_ready_for_approval) 
			select cast(now() as date) as log_date, 
			(SELECT SUM(s.total_rfq) FROM  (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='New' or s.status='RFQ Submitted') UNION ALL  SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='New' OR r.status='RFQ'))s) s) as rfq_new,
			(SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Clarification') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Clarification RFQ' OR r.status='Clarification'))s) s) as rfq_clarification,
			(SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Quotation Received') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no
            WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Offer Sent to BMT' OR r.status='Quotation Submitted'))s) s) as rfq_quotation,
			(SELECT  count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 where s.flag=1 and s.status='Final Quotation' and s.pr_no is not null and pr.id is  null) as rfq_ready_for_pr,
			(SELECT count(pr_no) FROM pr where status=0 and flag=1) as pr_ready_for_pr,
			(select count(pr.pr_no)  from pr where pr.status=1 or pr.status=2) as pr_in_process,
			(SELECT  count(s.pr_no) FROM pr s  left join purchase_order po on po.pr_id=s.id and po.flag=1  where s.flag=1 and s.status=3 and s.ask_canceled_date is null and po.id is  null) as pr_ready_for_po,
			(SELECT  count(s.pr_no) FROM    pr s  left join purchase_order po on po.pr_id=s.id and po.flag=1  where s.flag=1 and s.status=3 and s.ask_canceled_date is null and po.id is  null) as po_ready_for_po, (select count(s.po_no)  from `purchase_order` s where s.flag=1 and s.approved=0) as po_in_process, 
			(select count(s.po_no)  from `purchase_order` s where s.flag=1 and (s.approved=1 or s.approved=2)) as po_ready_for_approval;");
		$result = $res->getResult();
		return $this->respond(['status' => true, 'data' => $result], 200);
	}
 
}