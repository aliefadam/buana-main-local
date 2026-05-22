<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\MonitoringPrPoModel;
 
class Monitoringprpo extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
		$session = session();
		$s = $session->get();
		if(!isset($s["userid"]))
			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
			$uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            $model = new MonitoringPrPoModel();
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
 


}