<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\PobudgetModel;
 
class Budgeting extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
		$session = session();
		$s = $session->get();
		if(!isset($s["userid"]))
			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
            $model = new PobudgetModel();
            if($this->request->getMethod() == 'put'){
                return '404';
            }
            else if($this->request->getMethod() == 'delete'){
                return '404';
            }
            else{
                //$total_idr = 0;

                $json = $_REQUEST;
                //return print_r($json);
                $json["filter"] = json_decode($json["filter"] ?? '{}', true);
                $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
                $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
                $json = (Object) $json;
                $data = $model->getData($json);
                // foreach ($data[1] as $value) {
                //    if (!is_null($value->idr) && is_numeric($value->idr)) {
                //     $total_idr += floatval($value->idr);
                //    }
                // }
                // array_push($data[1], ['total_idr' => $total_idr]);
                return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 


}