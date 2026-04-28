<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\PoitemModel;
 
class Poitem extends ResourceController
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
            $model = new PoitemModel();
			$json = $_REQUEST;
			$json["filter"] = json_decode($json["filter"], true);
			$json["filterType"] = json_decode($json["filterType"], true);
			$json["filterCondition"] = json_decode($json["filterCondition"], true);
			$json = (Object) $json;
			$data = $model->read($json);
				
			return $this->respond(['status' => true, 'data'=>$data[1], 'total' => $data[2]], 200);
            
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
}