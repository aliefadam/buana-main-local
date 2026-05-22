<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ProjectModel;
 
class Project extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new ProjectModel();
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
                $json["filter"] = json_decode($json["filter"], true);
                $json["filterType"] = json_decode($json["filterType"], true);
                $json["filterCondition"] = json_decode($json["filterCondition"], true);
                $json = (Object) $json;
                $data = $model->read($json);
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    // create a product
    public function create()
    {
        try {
			$model = new ProjectModel();
			$json = $this->request->getJSON();
			$options = [
				'cost' => 11  
			];
			
			$data = array();

			foreach($model->allowedFields as $value) 
			{
				if(isset($json->{$value}))
					$data[$value] = $json->{$value};
			}

			$y = date("Y");

			$noQuery = $model->lastNumber($y);
			if(!$noQuery[0])
				return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
			$no = $noQuery[1][0]->no;
			$data["project_no"] = "PRJ/BMT/".$no."/".$y;
			$data["prj_year"] = $y;
			$data["no"] = $noQuery[1][0]->number;
			
			$model->insert($data);
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
			
			return $this->respondCreated($response, 201);
			//code...
		} catch (Exception $e) {
			return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
		}
    }
 
    // update product
    public function update($pk = null)
    {
        $model = new ProjectModel();
        $json = $this->request->getJSON();
        if($json){
            $options = [
                'cost' => 11  
            ];

            
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
        }
        // Insert to Database
        $model->update($pk, $data);
        $response = [
            'status'   => true
        ];
        return $this->respond($response);
    }
 
    // delete product
    public function delete($pk = null)
    {
        $model = new ProjectModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
            $model->update($pk, ["flag"=>0]);
            $response = [
                'status'   => true
            ];
             
            return $this->respond($response);
        }else{
            $response = [
                'status'   => false
            ];
            
            return $this->respond($response);
        }
         
    }
 
}