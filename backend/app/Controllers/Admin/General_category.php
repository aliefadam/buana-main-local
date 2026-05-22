<?php namespace App\Controllers\Admin;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\GeneralCategoryModel;
 
class General_category extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new GeneralCategoryModel();
            if($this->request->getMethod() == 'put'){
                $json = $this->request->getJSON();

                return $this->update($json->pk);
            }
            else if($this->request->getMethod() == 'delete'){
                $json = $_REQUEST;
                return $this->delete($json->{$model->primaryKey});
            }
            else{
                $json = $this->request->getJSON();
                $data = $model->read($json);
                return $this->respond(['status' => true, 'data'=>$data], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    // create a product
    public function create()
    {
        $model = new GeneralCategoryModel();
        $json = $this->request->getJSON();
        $options = [
            'cost' => 11  
        ];
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
            //if($value!='pk')
            $data[$value] = $json->$value;
        }
        //$data = json_decode(file_get_contents("php://input"));
        //$data = $this->request->getPost();
        $model->insert($data);
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 201);
    }
 
    public function update($pk = null)
    {
        $model = new GeneralCategoryModel();
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
        $model = new GeneralCategoryModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
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