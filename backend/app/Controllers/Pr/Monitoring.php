<?php namespace App\Controllers\Pr;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Pr\MonitoringModel;
 
class Monitoring extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new MonitoringModel();
            if($this->request->getMethod() == 'put'){
                $json = $this->request->getJSON();

                return $this->update($json->pk);
            }
            else if($this->request->getMethod() == 'delete'){
                $json = $_REQUEST;
                return $this->delete($json[$model->primaryKey]);
            }
            else{
                $json = $_REQUEST;
                $json["filter"] = json_decode($json["filter"] ?? '{}', true);
                $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
                $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
                $json = (Object) $json;
                $data = $model->read($json);
                return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

	public function upload(){
        $model = new MonitoringModel();
		try {
			$json = $this->request->getJSON();
			$header = json_decode($json->header ?? '{}', true);
			$item = json_decode($json->item ?? '{}', true);
			$del = $json->del;
			$model->db->transBegin();
			$res = $model->db->query("delete * from project_item_header where kode in ($del)");
			//if(!$res) throw $model->db->error();
			$res = $model->db->query("delete * from project_item_detail where parent in ($del)");
			//if(!$res) throw $model->db->error();
			$res = $model->db->table('project_item_header')->insertBatch($header);
			//if(!$res) throw $model->db->error();
			$res = $model->db->table('project_item_detail')->insertBatch($item);
			//if(!$res) throw $model->db->error();
			$model->db->transCommit();
			return $this->respond(['status' => true], 200);
		} catch (\Throwable $th) {
			$model->db->transRollback();
			return $this->respond(['status' => false, 'data'=>$th->getMessage()], 200);
		}
	}

	public function monitoring(){
        $model = new MonitoringModel();
		//insert into procurement_log (log_date, rfq_new, rfq_clarification, rfq_quotation, rfq_ready_for_pr, pr_ready_for_pr, pr_in_process, pr_ready_for_po, po_ready_for_po, po_in_process, po_ready_for_approval) 
		$res = $model->db->query("
			select cast(now() as date) as log_date,
			(SELECT SUM(s.total_rfq) FROM  (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='New' or s.status='RFQ Submitted') UNION ALL  SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='New' OR r.status='RFQ'))s) s) as rfq_new,
			(SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Clarification') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Clarification RFQ' OR r.status='Clarification'))s) s) as rfq_clarification,
			(SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Quotation Received') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no
            WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Offer Sent to BMT' OR r.status='Quotation Submitted'))s) s) as rfq_quotation,
			(SELECT count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1 where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null)) as rfq_ready_for_pr,
			(SELECT count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1 where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null)) as pr_ready_for_pr,
			(select count(pr.pr_no)  from pr where (pr.status between 0 AND 2) and pr.flag=1) as pr_in_process,
			(select count(distinct(s.id))from (select s.id, (select count(pi.id) from purchase_order_item pi left join purchase_order po on po.id=pi.purchase_order_id where  pi.pr_part_id = pp.id  and ps.id=pi.subledger_id and po.approved>=0 and pi.flag=1) as in_po FROM pr s left join pr_part pp on pp.pr_id=s.id left join pr_subledger ps on ps.pr_part_id=pp.id where s.flag=1 and s.status=3 and s.ask_canceled_date is null and pp.flag=1 and ps.flag=1)s where s.in_po=0) as pr_ready_for_po,
			(SELECT  count(s.pr_no) FROM    pr s  left join purchase_order po on po.pr_id=s.id and po.flag=1  where s.flag=1 and s.status=3 and s.ask_canceled_date is null  and po.id is  null) as po_ready_for_po, (select count(s.po_no)  from `purchase_order` s where s.flag=1 and s.approved=0) as po_in_process, 
			(select count(s.po_no)  from `purchase_order` s where s.flag=1 and (s.approved=1 or s.approved=2)) as po_ready_for_approval,  '' as created_date
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
			(SELECT count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1  where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null)) as rfq_ready_for_pr,
			(SELECT count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1 where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null)) as pr_ready_for_pr,
			(select count(pr.pr_no)  from pr where (pr.status between 0 AND 2) and pr.flag=1) as pr_in_process,
			(SELECT  count(s.pr_no) FROM pr s  left join purchase_order po on po.id=s.id and po.flag=1  where s.flag=1 and s.status=3  and po.id is  null) as pr_ready_for_po,
			(SELECT  count(s.pr_no) FROM    pr s  left join purchase_order po on po.id=s.id and po.flag=1  where s.flag=1 and s.status=3  and po.id is  null) as po_ready_for_po, (select count(s.po_no)  from `purchase_order` s where s.flag=1 and s.approved=0) as po_in_process, 
			(select count(s.po_no)  from `purchase_order` s where s.flag=1 and (s.approved=1 or s.approved=2)) as po_ready_for_approval;");
		//$result = $res->getResult();
		return $this->respond(['status' => true], 200);
	}
 
}