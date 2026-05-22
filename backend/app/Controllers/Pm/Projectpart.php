<?php namespace App\Controllers\Pm;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Pm\ProjectpartModel;
 
class Projectpart extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new ProjectpartModel();
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
 
    public function create()
    {
        $model = new ProjectpartModel();
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
        $model = new ProjectpartModel();
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
		$noModif = true;
		if(isset($data["design_date"])){
			$noModif = false;
			if(in_array("design_modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["design_modified_by"] = $s["userid"];
			if(in_array("design_modified_date", $model->allowedFields))
				$data["design_modified_date"] = date("Y-m-d H:i:s");
		}
		if(isset($data["rfq_date"])){
			$noModif = false;
			if(in_array("rfq_modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["rfq_modified_by"] = $s["userid"];
			if(in_array("rfq_modified_date", $model->allowedFields))
				$data["rfq_modified_date"] = date("Y-m-d H:i:s");
		}
		if(isset($data["po_date"])){
			$noModif = false;
			if(in_array("po_modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["po_modified_by"] = $s["userid"];
			if(in_array("po_modified_date", $model->allowedFields))
				$data["po_modified_date"] = date("Y-m-d H:i:s");
		}
		if(isset($data["pr_no"])){
		  $noModif = false;
			if(in_array("pr_modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["pr_modified_by"] = $s["userid"];
			if(in_array("pr_modified_date", $model->allowedFields))
				$data["pr_modified_date"] = date("Y-m-d H:i:s");
		}
		if(isset($data["delivery_date"])){
			$noModif = false;
			if(in_array("delivery_modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["delivery_modified_by"] = $s["userid"];
			if(in_array("delivery_modified_date", $model->allowedFields))
				$data["delivery_modified_date"] = date("Y-m-d H:i:s");
		}
		if(isset($data["complete_installation_date"])){
			$noModif = false;
			if(in_array("complete_modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["complete_modified_by"] = $s["userid"];
			if(in_array("complete_modified_date", $model->allowedFields))
				$data["complete_modified_date"] = date("Y-m-d H:i:s");
		}
		if($noModif){
			if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["modified_by"] = $s["userid"];
			if(in_array("modified_date", $model->allowedFields))
				$data["modified_date"] = date("Y-m-d H:i:s");
		}
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
        $model = new ProjectpartModel();
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
 
}