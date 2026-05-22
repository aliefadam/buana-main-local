<?php namespace App\Controllers\Planner;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Planner\TaskModel;
 
class Task extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new TaskModel();
            $json = $_REQUEST;
            $json["filter"] = json_decode($json["filter"] ?? '{}', true);
            $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
            $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
            $json = (Object) $json;

            $data = $model->read($json);
            return $this->respond($data, 200);

        } catch (\Throwable $e) {
            return $this->respond([
                'status' => false,
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
		try {
     		$model = new TaskModel();
			$json = $this->request->getJSON();
			$session = session();
			$s = $session->get();
			//var_dump($model->allowedFields);
			if(in_array("created_by", $model->allowedFields) && isset($s["username"]))
				$json->created_by = $s["username"];
			$ret = $model->post($json);
		} catch (\Throwable $th) {
			$ret = (object) [
				'status' => false,
				'data' => $th->getMessage()
			];
		}
        
        return $this->respondCreated($ret, 200);
    }
 
    public function update($id = null)
    {
        $model = new TaskModel(); 
        $json = $this->request->getJSON(); 
        $session = session();
        $s = $session->get();

        if (isset($s["username"])) {
            $json->modified_by = $s["username"];
        }

        $json->modified_date = date("Y-m-d H:i:s");
        $json->pk = $id; // ← Penting! agar bisa diproses oleh model

        $ret = $model->put($json);

        if ($ret->status) {
            return $this->respondUpdated([
                'status' => 'success',
                'message' => 'Task updated successfully',
                'data' => $ret->data
            ]);
        } else {
            return $this->failServerError([
                'status' => 'error',
                'message' => 'Update failed',
                'error' => $ret->data
            ]);
        }
    }


    public function delete($pk = null)
    {
        $model = new TaskModel();
        $ret = $model->delete_data($pk);
            
        return $this->respondCreated($ret, 200);
         
    }
 
    public function related()
    {
		$json = $_REQUEST;
		$json = (Object) $json;
        $model = new TaskModel();
        $ret = $model->related($json->id ?? null);
        
        return $this->respondCreated($ret, 200);
         
    }
 
}