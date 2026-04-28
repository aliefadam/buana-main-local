<?php namespace App\Controllers;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UsersModel;
 
class Users extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try{
            $session = session();
            $s = $session->get();
            $authorization = false;
            if(!isset($s["userid"]))
                $authorization = false;
            else
                $authorization = true;
    
            if(isset(apache_request_headers()["Authorization"]))
                $authorization = apache_request_headers()["Authorization"];
            $host = apache_request_headers()["Host"];
            if($authorization!='19e4002f-f824-4dca-afaf-73fa6526ea33'){
                $authorization = false;
            }
            if(!$authorization)
                return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);

        }
        catch (\Throwable $th) {
            $response = [
               'status'   => false,
               'data'=>$th->getMessage()
           ];
           return $this->respond($response);
       }

    
        try {
             $model = new UsersModel();
			$uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            if($this->request->getMethod() == 'put'){
                $json = $this->request->getJSON();

                return $this->update($json->pk);
            }
            else if($this->request->getMethod() == 'delete'){
                $json = $_REQUEST;
                return $this->delete($json['username']);
                //$model = new UsersModel();
                //$data = $model->find($json->username);
                /* if($data){
                    $model->delete($json->username);
                    $response = [
                        'status'   => true
                    ];
                    
                    return $this->respond($response);
                }else{
                    $response = [
                        'status'   => false
                    ];
                    
                    return $this->respond($response);
                } */
            }
            else{
                $json = $_REQUEST;
                $json["filter"] = json_decode(json_encode($json["filter"] ?? '{}', true), true);
                $json["filterType"] = json_decode(json_encode($json["filterType"] ?? '{}', true), true);
                $json["filterCondition"] = json_decode(json_encode($json["filterCondition"] ?? '{}', true), true);
                $json = (Object) $json;
                $data = $model->read($json);
				$ex = explode('.', end($segment));
				if(end($ex)=='xlsx'){
					$exporter = new \Xerobase\ExcelReporter\Export();
					$exporter->export($data[0]);
					//return $this->respond($data[0]);
				}
				else
                	return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1], "segment"=>$segment], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    // get single product
    public function show($id = null)
    {
        $model = new UsersModel();
        $data = $model->getWhere(['username' => $id])->getResult();
        if($data){
            return $this->respond($data);
        }else{
            return $this->failNotFound('No Data Found with id '.$id);
        }
    }

    public function auth(){
        $session = session();
        $json = $this->request->getJSON();
        $model = new UserModel();
        $data = $model->auth($json->username, $json->password);
        return $this->respond($data, 200);
    }
 
    // create a product
    public function create()
    {
        $model = new UsersModel();
        $json = $this->request->getJSON();
        $options = [
            'cost' => 11  
        ];
        
        $hashed_password = '';
		if(isset($json->auth))
			$hashed_password = password_hash($json->auth, PASSWORD_BCRYPT, $options);
        $data = [
            'username' => $json->username,
            'name' => $json->name,
            'is_group' => $json->is_group,
            'auth' => $hashed_password,
            'flag' => 1
        ];
        //$data = json_decode(file_get_contents("php://input"));
        //$data = $this->request->getPost();
        $model->insert($data);
        $affected = $model->affectedRows();
		if($affected != 0)
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        
        return $this->respondCreated($response, 200);
    }
 
    // update product
    public function update($id = null)
    {
        $model = new UsersModel();
        $json = $this->request->getJSON();
        $session = session();
    	$s = $session->get();
        if($json){
            $options = [
                'cost' => 11  
            ];

            
            $data = [
                /* 'username' => $json->username,
                'name' => $json->name, */
            ];

            foreach($json as $key => $value) 
            {
                if($key=='auth'){
                    if(trim($value)!=''){
                        $hashed_password=password_hash($value, PASSWORD_BCRYPT, $options);
                        $data[$key] = $hashed_password;
                        //array_push($data, 'password' => $hashed_password)
                    }
                }
                else if($key!='pk')
                    $data[$key] = $value;
            }
            if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
            $data["modified_by"] = $s["userid"];
        if(in_array("modified_date", $model->allowedFields))
            $data["modified_date"] = date("Y-m-d H:i:s");
        }
        // Insert to Database
        $model->update($id, $data);
        $affected = $model->affectedRows();
		if($affected != 0)
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
        return $this->respond($response);
    }
 
    // delete product
    public function delete($pk = null)
    {
        $model = new NowaModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
            $model->update($pk, ["flag"=>0]);
            $response = [
                'status'   => true
            ];
             
            return $this->respond($response);
        }else{
            $response = [
                'status'   => false
            ];
            
            return $this->respond($response);
        }
         
    }
 
}