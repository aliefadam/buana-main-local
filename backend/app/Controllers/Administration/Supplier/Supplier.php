<?php namespace App\Controllers\Administration\Supplier\Supplier;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\SupplierModel;
 
class Supplier extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        $session = session();
		$s = $session->get();
		if(!isset($s["userid"]))
			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
            $model = new SupplierModel();
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
        $model = new SupplierModel();
        $json = $this->request->getJSON();
        $options = [
            'cost' => 11  
        ];
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
        //$data = json_decode(file_get_contents("php://input"));
        //$data = $this->request->getPost();
		//$model->db->transStart();
		//$model->db->query('select ')
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
 
    // update product
    public function update($pk = null)
    {
        $model = new SupplierModel();
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

            $session = session();
		    $s = $session->get();
		    if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		    if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
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
        $model = new SupplierModel();
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