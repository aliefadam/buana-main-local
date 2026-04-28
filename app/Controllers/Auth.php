<?php namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\UsersModel;
use CodeIgniter\API\ResponseTrait;
 
class Auth extends Controller
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

    public function login(){
        try {
			$authorization = false;
			if(isset(apache_request_headers()["Authorization"]))
				$authorization = apache_request_headers()["Authorization"];
			$host = apache_request_headers()["Host"];
			if($authorization!='19e4002f-f824-4dca-afaf-73fa6526ea33'){
				$authorization = false;
			}
			
			if($host!='main.buanamultiteknik.com' && $authorization == false){
				return $this->respond(['status'   => false]);
			}
			else{
				$session = session();
				$json = $this->request->getJSON();
				$model = new UsersModel();
				$data = $model->auth($json->username, $json->password);
				if(count($data) > 0){
					if($data[0]->auth == true){
						helper("cookie");
						// store a cookie value
    					
    					 $response = service('response');
                        $response->setCookie([
                            'name'   => 'uuid',
                            'value'  => $data[0]->id,
                            'expire' => 3600*24,
                            'domain' => '.buanamultiteknik.com',
                            'path'   => '/',
                            'secure' => false,
                            'httponly' => false,
                            'samesite' => 'Lax'
                        ]);
						
						set_cookie("uuid", $data[0]->id, 3600*24);
						$groups = $model->groups($data[0]->username);
						$g = [];
						foreach ($groups as $key => $value) {
							array_push($g, $value->group_name);
						}
						$session->set([
							'username'     => $data[0]->username,
							'email'     => $data[0]->email,
							'userid'     => $data[0]->id,
							'name'     => $data[0]->name,
							'groups'     => implode(',',$g),
							'loggedin'     => true
						]);
						//$data[0]->groups = $g;
					}
					else
						return $this->respond(['status'   => false]);
				}
				else
					return $this->respond(['status'   => false]);
			}
			
			$response = [
				'status'  => count($data) > 0 ? true : false,
				'data'    => $data,
				'groups'  => $g,
				'authorization'=> $authorization
			];
			
            return $this->respond($response);
        } catch (\Throwable $e) {
            return $this->respond(['status'   => false, 'data'    => $e->getMessage()]);
        }
    }

    public function credential(){
        $session = session();
		$model = new UsersModel();
        if(isset($session->username)){
			$groups = $model->groups($session->username);
			$g = [];
			foreach ($groups as $key => $value) {
				array_push($g, $value->group_name);
			}
            $response = [
                'status'   => true,
                'data'    => [$session->get()],
                'groups'    => $g
            ];
        }else
            $response = [
                'status'   => false,
                'data'    => 'You are not logged in!'
            ];
        return $this->respond($response);
    }
 
}