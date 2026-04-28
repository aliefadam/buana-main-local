<?php namespace App\Controllers\Rfq;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\RfqModel;
use App\Models\RfqcommentModel;
 
class Rfq extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
			$uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            $model = new RfqModel();
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
				$ex = explode('.', end($segment));
				if(end($ex)=='xlsx'){
					$exporter = new \Xerobase\ExcelReporter\Export();
					$exporter->export($data[1]);
				}
				else
                	return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function create()
    {
        $model = new RfqModel();
        $json = $this->request->getJSON();
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }

		//return $this->respondCreated($model->allowedFields, 200);
        $session = session();
		$s = $session->get();
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
        $model->insert($data);
		$affected = $model->affectedRows();
		$id = $model->getInsertID();
        $model->where('id', $id)
        ->set(['rfq_no' => "RFQ-BMT-".str_pad($id, 4, "0", STR_PAD_LEFT)])
        ->update();
		if($affected != 0){
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
		}else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        
        return $this->respondCreated($response, 200);
    }
 
    public function update($pk = null)
    {
        $model = new RfqModel();
        $json = $this->request->getJSON();
		$data = [
		];
        if($json){

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
        }
        // Insert to Database
        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        $model->update($pk, $data);
		$affected = $model->affectedRows();
		if($affected != 0){
			$modelcomment = new RfqcommentModel();
			$hist = [];
			//$data["$table"] = $model->table;
			$hist["hist"] = json_encode($data, true);
			$hist["rfq_id"] = $pk;
			if(isset($s["userid"]))
				$hist["created_by"] = $s["userid"];
			$modelcomment->insert($hist);
			$response = [
				'status'   => true
			];
		}else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        return $this->respond($response);
    }
 
    public function delete($pk = null)
    {
        $model = new RfqModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
			$data = [];
			$data["flag"] = 0;
			$session = session();
			$s = $session->get();
			if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["modified_by"] = $s["userid"];
			if(in_array("modified_date", $model->allowedFields))
				$data["modified_date"] = date('Y-m-d H:i:s');
            $model->update($pk, $data);
			$affected = $model->affectedRows();
			if($affected != 0)
				$response = [
					'status'   => true
				];
			else
				$response = [
					'status'   => false,
					'data'    => $model->errors()
				];
            return $this->respond($response);
        }else{
            $response = [
                'status'   => false,
				'data' => 'Data not found!'
            ];
            
            return $this->respond($response);
        }
         
    }

    public function total_rfq(){
        $model = new RfqModel();
		$res = $model->db->query("
			select 
			(select sum(total_rfq) from (
           SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='New' or s.status='RFQ Submitted') 
        UNION ALL  
        SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='New' OR r.status='RFQ'))s)s) as new_rfq,
        (SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Clarification') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Clarification RFQ' OR r.status='Clarification'))s) s) as rfq_clarification, 
        (SELECT SUM(s.total_rfq) FROM (SELECT  COUNT(s.rfq_no) AS total_rfq FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no WHERE s.flag=1 AND s.rfq_group='Local' and (s.status='Quotation Received') UNION ALL SELECT COUNT(s.rfq_no) as total_rfq FROM (select s.rfq_no, (select distinct r.order_id FROM ragic_data r WHERE r.ragic_id = s.ragic_id LIMIT 1) AS ragic_no, r.status AS ragic_status FROM rfq_header s LEFT JOIN ragic r ON r.order_id = s.reference_no  WHERE s.rfq_group LIKE 'overseas%' AND (r.status='Offer Sent to BMT' OR r.status='Quotation Submitted'))s) s) as rfq_quotation,
        (SELECT count(s.rfq_no) FROM rfq_header s left join pr on pr.rfq_id=s.id and pr.flag=1 left join purchase_order po on concat('%', po.ref_internal_bmt, '%') like concat('%', s.rfq_no, '%') and po.flag=1 where s.flag=1 and s.status='Final Quotation' and (s.pr_no is null and pr.id is null and po.ref_internal_bmt is null)) as rfq_ready_for_pr;
			");
		$result = $res->getResult();
		return $this->respond(['status' => true, 'data' => $result], 200);
	}
 
}