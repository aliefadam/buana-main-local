<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\OtpModel;
 
class Otp extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new OtpModel();
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
 
    public function create()
    {
		try{
			$model = new OtpModel();
			$json = $this->request->getJSON();
			
			$data = [
			];

			foreach($model->allowedFields as $value) 
			{
				if(isset($json->{$value}))
					$data[$value] = $json->{$value};
			}
			$otp = random_int(100000, 999999);
			$data["kode"] = $otp;
			$session = session();
			$s = $session->get();
			if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
				$data["created_by"] = $s["userid"];
			$data["url"] .= "::".$s["userid"];
			$model->insert($data);
			$affected = $model->affectedRows();
			if($affected != 0){
				$response = [
					'status'   => true,
					'data'    => 'Data Saved'
				];

				helper(['Wa_helper']);
				$msgWa = preg_replace([
					'/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', //remove all style tags
					'/<(?:br|p|tr)[^>]*>/i', //replace br p with ' '
					'/<[^>]*>/',  //replace any tag with ''
					'/\s+/u', //remove run on space - replace using the unicode flag
					'/^\s+|\s+$/u', //trim - replace using the unicode flag
					'/\~nl\~/i'
				],[
					'', "~nl~", '', ' ', '', "\r\n"
				], "Kode OTP anda untuk ".$json->name." adalah: ".$otp);
				$result = $model->db->query("select no_hp from users where id = ".$s["userid"]);
				if(isset($result->getResult()[0])){
					$no = $result->getResult()[0]->no_hp;
					if($no != null)
						sendWa($no, $msgWa);
				}
			}
			else
				$response = [
					'status'   => false,
					'data'    => $model->errors()
				];
			
			return $this->respondCreated($response, 200);
		} catch (\Throwable $e) {
			$response = [
                'status'   => false,
				"message" => $e->getMessage()
            ];
            
            return $this->respond($response);
		}
    }
 
    public function update($pk = null)
    {
        $model = new OtpModel();
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
		try{
			$model = new OtpModel();
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
		} catch (\Throwable $e) {
			$response = [
                'status'   => false,
				"message" => $e->getMessage(),
            ];
            
            return $this->respond($response);
		}
         
    }

	public function check(){
		
		try{
			$model = new OtpModel();
			$json = $_REQUEST;
			$json = (Object) $json;
			$session = session();
			$s = $session->get();
			
			$result = $model->db->query("select count(*) as c from otp where created_date >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) and kode = ".$json->otp." and url = '".$json->url . "::" . $s["userid"]."'");
			if($result->getResult()[0]->c == 0){
				$response = [
					'status'   => false,
					'message' => "OTP not found!"
				];
			}
			else{
				$res = $result->getResult()[0];
				$response = [
					'status'   => true,
					'data' => $res
				];
			}

			return $this->respond($response);
		} catch (\Throwable $e) {
			$response = [
                'status'   => false,
				"message" => $e->getMessage(),
            ];
            
            return $this->respond($response);
		}
	}
 
}