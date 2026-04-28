<?php namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\DataModel;
use CodeIgniter\API\ResponseTrait;
 
class Data extends Controller
{
    use ResponseTrait;
    public function index(){
        if($this->request->getMethod() == 'delete'){
            $session = session();
            $session->destroy();
            return $this->respond(['status'   => true]);
        }
        return '';
    }

    public function get(){
        try {
            $model = new DataModel();
			if($this->request->getMethod() == 'post'){
                $json = $this->request->getJSON();
				$data = $model->data($json->q);
            }
			else{
				$json = $_REQUEST;
				//$data = json_decode(file_get_contents("php://input"));
				$data = $model->data($json['q']);
			}
            if(!$data[0])
            	return $this->respond(['status'   => false, 'data'    => $data[1]]);
			return $this->respond(['status'   => true, 'data'    => $data[1]]);
        } catch (Exception $e) {
            return $this->respond(['status'   => false, 'data'    => $e]);
        }
    }
 
}