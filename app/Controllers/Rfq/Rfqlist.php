<?php namespace App\Controllers\Rfq;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ListModel;
 
class Rfqlist extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new ListModel();
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
        } catch (\Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function create()
    {
        $model = new ListModel();
        $json = $_REQUEST;
        
        $data = [
        ];

		if(isset($json["pk"])){
			return $this->update($json["pk"]);
		}
		else{
			foreach($model->allowedFields as $value) 
			{
				if(isset($json[$value]) && $value != 'datasheet2')
					$data[$value] = $json[$value];
			}

			$validation = \Config\Services::validation();

			/* $input = $validation->setRules([
				'file' => 'uploaded[datasheet]|max_size[datasheet,8192],'
			]); */

			$doc = $this->request->getFile('datasheet2');
			if(!empty($doc)){
				try {
					if (! $doc->hasMoved()) {
						$name = $doc->getRandomName();
						$filepath = './uploads/' . $name;
						$data['datasheet'] = $name;
						$doc->move('./uploads', $name);
					}
					else{
						$response = [
							'status'   => false
						];
					
						return $this->respondCreated($response, 200);
					}
				} catch (\Exception $err) {
					$response = [
						'status'   => false,
						'data'	=> $err->getMessage(),
						'a' => $name
					];
				
					return $this->respondCreated($response, 200);
				}
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
    }
 
    public function update($pk = null)
    {
        $model = new ListModel();
		$json = $_REQUEST;
		$data = [
		];
        if($json){

            foreach($json as $key => $value) 
            {
                if($key!='pk' && $key != 'datasheet2')
                    $data[$key] = $value;
            }
        }
		$doc = $this->request->getFile('datasheet2');
		if(!empty($doc)){
			try {
				if (! $doc->hasMoved()) {
					$name = $doc->getRandomName();
					$filepath = './uploads/' . $name;
					$data['datasheet'] = $name;
					$doc->move('./uploads/', $name);
				}
				else{
					$response = [
						'status'   => false
					];
				
					return $this->respondCreated($response, 200);
				}
			} catch (\Exception $err) {
				$response = [
					'status'   => false,
					'data'	=> $err->getMessage()
				];
			
				return $this->respondCreated($response, 200);
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
        $model = new ListModel();
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