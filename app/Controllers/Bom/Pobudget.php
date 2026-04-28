<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\PobudgetModel;
 
class Pobudget extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
		$session = session();
		$s = $session->get();
		if(!isset($s["userid"]))
			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
            $model = new PobudgetModel();
            if($this->request->getMethod() == 'put'){
                $json = $this->request->getJSON();

                return $this->update($json->pk);
            }
            else if($this->request->getMethod() == 'delete'){
                $json = $_REQUEST;
                return $this->delete($json[$model->primaryKey]);
            }
            else{
                $total_idr = 0;
                $json = $_REQUEST;
                $json["filter"] = json_decode($json["filter"] ?? '{}', true);
                $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
                $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
                $json = (Object) $json;
                $data = $model->read($json);
                // foreach ($data[1] as $value) {
                //    if (!is_null($value->idr) && is_numeric($value->idr)) {
                //     $total_idr += floatval($value->idr);
                //    }
                // }
                // array_push($data[1], ['total_idr' => $total_idr]);
                return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function create()
    {
        $model = new PobudgetModel();
        $json = $this->request->getJSON();
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
		$session = session();
		$s = $session->get();
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
        $model->insert($data);
		$affected = $model->affectedRows();
		if($affected != 0)
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        
        return $this->respondCreated($response, 200);
    }
 
    public function update($pk = null)
    {
        $model = new PobudgetModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
        }
        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
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
    }
 
    public function delete($pk = null)
    {
        $model = new PobudgetModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
			//$data = [];
			//$data["flag"] = 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date('Y-m-d H:i:s', now());
            //$model->update($pk, ["flag"=>0]);
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

    public function getData(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->getData($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }
     public function getDataProject(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->getDataProject($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }
    public function getDataPaid(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->getDataPaid($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }
    public function getDataNoInvoice(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->getDataNoInvoice($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }
    public function getDataPoNoInvoice(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->getDataPoNoInvoice($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }
    public function getDataMonitoringPayment(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->monitoringPayment($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }
    public function getDataWaitingApproval(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->poWaitingApproval($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }
    public function getDataApprovedOutstanding(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->poApprovedOutstanding($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
    }

    public function getDataMonitoringProject(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->poMonintoringProject($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);

    }
    public function getDataPoItem(){
        
        $model = new PobudgetModel();
        $json = $_REQUEST;
        //return print_r($json);
        $json["filter"] = json_decode($json["filter"] ?? '{}', true);
        $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
        $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
        $json = (Object) $json;
        $data = $model->poItem($json);
        // foreach ($data[1] as $value) {
        //    if (!is_null($value->idr) && is_numeric($value->idr)) {
        //     $total_idr += floatval($value->idr);
        //    }
        // }
        // array_push($data[1], ['total_idr' => $total_idr]);
        return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);

    }
}