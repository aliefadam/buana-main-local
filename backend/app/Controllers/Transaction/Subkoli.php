<?php namespace App\Controllers\Transaction;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\SubkoliModel;
 
class Subkoli extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new SubkoliModel();
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
        $model = new SubkoliModel();
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
			$data["flag"]=1;
		
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
        $model = new SubkoliModel();
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
        $model = new SubkoliModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
			//$json = $_REQUEST;
			//$data = [];
			//$data["flag"] = $json["flag"] ?? 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date("Y-m-d H:i:s");
            //$model->update($pk, $data);
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

	public function auto(){
		
        try {
			$model = new SubkoliModel();
			$json = $this->request->getJSON();
			$session = session();
			$s = $session->get();
			$user = 0;
			if(isset($s["userid"]))
				$user = $s["userid"];
			$tmp = Array();
			for ($i=0; $i < $json->qty; $i++) { 
				$tmp[] = "('".$user."')";
			}
			$q = $model->query("
				insert into subkoli (created_by) 
				values".implode(',', $tmp).";
			");
			$q = $model->query("
				select * from subkoli order by id desc limit ".$json->qty."
			");
			$data = $q->getResult();
			$response = [
				'status'   => true,
				'data'   => $data
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
}