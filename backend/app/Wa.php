<?php namespace App\Controllers;
header("Access-Control-Allow-Methods: GET, OPTIONS, POST");
header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
if ( "OPTIONS" === $_SERVER['REQUEST_METHOD'] ) {
    die();
}
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\WaModel;
 
class Wa extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new WaModel();
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

	public function send(){
		try{
			
			$authorization = false;
			if(isset(apache_request_headers()["Authorization"]))
				$authorization = apache_request_headers()["Authorization"];
			$host = apache_request_headers()["Host"];
			if($authorization!='19e4002f-f824-4dca-afaf-73fa6526ea33'){
				$authorization = false;
			}
			
			if($host!='internal.buanamultiteknik.com' && $authorization == false){
				$response =['status'   => false];
			}
			else{
				helper(['Wa_helper']);
				$json = $this->request->getJSON() ?? (object) $_REQUEST;
				sendWa($json->number, $json->message);
				$response = [
					'status'   => true
				];
			}
		} catch (\Throwable $th) {
			
			$json = file_get_contents("php://input");
            $json = json_decode($json, true);
			$response = [
				'status'   => false,
				'data'    => $th->getMessage(),
				'a' => $this->request->getJSON(),
				'b' => $_REQUEST,
				"c" => $json
			];
		}
        return $this->respondCreated($response, 200);
	}
 
    public function create()
    {
		try {
			
			$model = new WaModel();
			$json = file_get_contents("php://input");
            $json = json_decode($json, true);
			//$json = $this->request->getJSON();
			
			$data = [
			];
	
		   /*  foreach($model->allowedFields as $value) 
			{
				if(isset($json->{$value}))
					$data[$value] = $json->{$value};
			} */

			//return $json["messsage"];
			
			/*$session = session();
			$s = $session->get();
			if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
				$data["created_by"] = $s["userid"];*/
			//return 'aaa';
			//if($json["ischat"]){
				$data["device"] = $json["device"];
				$data["name"] = $json["name"];
				$data["member"] = $json["member"];
				$data["sender"] = $json["sender"];
				$data["message"] = $json["message"];
				//$data["wa_date"] = $json["time"];
				$data["location"] = $json["location"];
				/* if(isset($json["text"]))
					$data["text"] = $json["text"];
				if(isset($json["pollname"]))
					$data["pollname"] = $json["pollname"];
				if(isset($json["choices"]))
					$data["choices"] = $json["choices"];  */
				//$data["lng"] = $json["lng"];
				//$data["status"] = "received";
				//$data["quotemsgbody"] = $json["quotemsgbody"];
				$model->insert($data);
			//}else{
			//}
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
		} catch (\Throwable $th) {
			$response = [
				'status'   => false,
				'data'    => $th->getMessage()
			];
		}
        
        return $this->respondCreated($response, 200);
    }
 
    public function update($pk = null)
    {
        $model = new WaModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }
        }
        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        $model->update($pk, $data);
		$affected = $model->affectedRows();
		if($affected != 0)
			$response = [
				'status'   => true
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        return $this->respond($response);
    }
 
    public function delete($pk = null)
    {
        $model = new WaModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
			//$json = $_REQUEST;
			//$data = [];
			//$data["flag"] = $json["flag"] ?? 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date("Y-m-d H:i:s");
            //$model->update($pk, $data);
			$affected = $model->affectedRows();
			if($affected != 0)
				$response = [
					'status'   => true
				];
			else
				$response = [
					'status'   => false,
					'data'    => $model->errors()
				];
            return $this->respond($response);
        }else{
            $response = [
                'status'   => false,
				'data' => 'Data not found!'
            ];
            
            return $this->respond($response);
        }
         
    }
 
}