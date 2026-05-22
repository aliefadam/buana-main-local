<?php namespace App\Controllers\Transaction;
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\BomreportitemModel;
 
class Bomreportitem extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new BomreportitemModel();
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