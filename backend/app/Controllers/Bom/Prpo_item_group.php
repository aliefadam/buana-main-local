<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PrPoItemGroupModel;
 
class Prpo_item_group extends ResourceController
{
    use ResponseTrait;

    protected function initialize()
    {
        $this->db = db_connect();
    }

    // get all product
    public function index()
    {
        try {
            $model = new PrPoItemGroupModel();

                $json = $_REQUEST;
                $json["filter"] = json_decode($json["filter"] ?? '{}', true);
                $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
                $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
                $json = (Object) $json;
                $data = $model->read($json);
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
                
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

}