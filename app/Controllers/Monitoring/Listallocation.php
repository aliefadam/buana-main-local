<?php namespace App\Controllers\Monitoring;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Monitoring\ListallocationModel;
 
class Listrfqprimary extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
			$uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            $model = new Listrfqprimary();
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
                $data = $model->allocationList($json);
				$ex = explode('.', end($segment));
				if(end($ex)=='xlsx'){
					$exporter = new \Xerobase\ExcelReporter\Export();
					$exporter->export($data[1]);
				}
				else
                	return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
}