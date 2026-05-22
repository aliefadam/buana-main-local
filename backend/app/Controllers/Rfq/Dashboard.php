<?php namespace App\Controllers\Rfq;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\RfqModel;
use App\Models\RfqcommentModel;
use App\Models\UsersModel;
 
class Dashboard extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $umodel = new UsersModel();
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
                $json["filter"] = json_decode($json["filter"]??"{}", true);
                $json["filterType"] = json_decode($json["filterType"]??"{}", true);
                $json["filterCondition"] = json_decode($json["filterCondition"]??"{}", true);
                $json = (Object) $json;
				
				$session = session();
				$s = $session->get();
				$groups = $umodel->groups($s["username"]);
				$g = [];
				foreach ($groups as $key => $value) {
					array_push($g, $value->group_name);
				}
				// if(isset($g)){
				// 	if(in_array("rfq_overseas_germany", $g))
				// 		$json->filter["rfq_group"] = "Overseas Germany";
				// 	if(in_array("rfq_overseas_beijing", $g))
				// 		$json->filter["rfq_group"] = "Overseas Beijing";
				// }
                $data = $model->read($json);
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
        $model = new RfqModel();
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
				$data["modified_date"] = date('Y-m-d H:i:s', now());
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
 
}