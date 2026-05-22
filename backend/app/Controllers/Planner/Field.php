<?php namespace App\Controllers\Planner;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Planner\FieldModel;
 
class Field extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new FieldModel();
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
                return $this->respond($data, 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function create()
    {
        $model = new FieldModel();
        $json = $this->request->getJSON();
        $ret = $model->post($json);
        
        return $this->respondCreated($ret, 200);
    }
 
    public function put($pk = null)
    {
        $model = new FieldModel();
        $json = $this->request->getJSON();
        $ret = $model->put($json);
        
        return $this->respondCreated($ret, 200);
    }
 
    public function delete($pk = null)
    {
        $model = new FieldModel();
        $ret = $model->delete_data($pk);
        
        return $this->respondCreated($ret, 200);
         
    }
 
    public function task_category()
    {
		$json = $_REQUEST;
		$json = (Object) $json;
        $model = new FieldModel();
        $ret = $model->task_category($json->category_id ?? null);
        
        return $this->respondCreated($ret, 200);
         
    }
 
}