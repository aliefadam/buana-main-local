<?php namespace App\Controllers\Admin;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Admin\NoWaModel;
 
class Nowa extends ResourceController
{
    use ResponseTrait;
    // get all data
    public function index()
    {
        try {
            $model = new NoWaModel();
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


    // create a product
    public function create()
    {
        $model = new NoWaModel();
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

            $y = date("Y");
		    $m = date('m');

        $model->insert($data);

		//$post->db->transComplete();
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 201);
    }
 
    // update data
    public function update($pk = null)
    {
        $model = new NowaModel();
        $json = $this->request->getJSON();
        $session = session();
    	$s = $session->get();
        if($json){
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
            
            if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
    			$data["modified_by"] = $s["userid"];
    		if(in_array("modified_date", $model->allowedFields))
    			$data["modified_date"] = date("Y-m-d H:i:s");
            
        }
        // Insert to Database
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
 
    // delete data
    public function delete($pk = null)
    {
        $model = new NowaModel();
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