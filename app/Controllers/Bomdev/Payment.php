<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PaymentModel;
use App\Models\PurchaseOrderModel;
 
class Payment extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new PaymentModel();
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
                $json["filter"] = json_decode($json["filter"], true);
                $json["filterType"] = json_decode($json["filterType"], true);
                $json["filterCondition"] = json_decode($json["filterCondition"], true);
                $json = (Object) $json;
                $data = $model->read($json);
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

	public function complete(){
		$model = new PaymentModel();
		$data = $model->complete();
		return $this->respond(['status' => $data[0], 'data' => $data[1]], 200);
	}

	public function transferstock(){
        $session = session();
		$model = new PaymentModel();
		$json = $_REQUEST;
		$json = (Object) $json;
		$data = $model->transferstock($json, $s["userid"]);
		return $this->respond(['status' => $data[0], 'data' => $data[1]], 200);
	}
 
    // create a product
    public function create()
    {
        $model = new PaymentModel();
        $modelP = new PurchaseOrderModel();
        $json = $this->request->getJSON();
        $options = [
            'cost' => 11  
        ];
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
		
        $model->insert($data);

        $id = $model->getInsertID();
        $model->where('id', $id)
        ->set(['payment_no' => "PAY".str_pad($id, 4, "0", STR_PAD_LEFT)])
        ->update();
        
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 200);
    }

	public function insertInfo($info, $uid, $url){
		$model = new PaymentModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', '".$url."')");
	}

	public function info_validated(){
		return "";
	}

	public function info_approved(){
		return "";
	}
 
    // update product
    public function update($pk = null)
    {
        $model = new PaymentModel();
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

		if(isset($data["approved"])){
			if($data["approved"]==2){
				$this->insertInfo("", "payment_validated_".$pk, "https://internal.buanamultiteknik.com/api/bom/payment/info_validated?id=".$pk);
				//$this->info("<b>Approved:</b> $approved <br/>Invoice: ".implode(', ', $invoice), "payment_approved".$uniqid);
			}
			if($data["approved"]==4){
				$this->insertInfo("", "payment_approved_".$pk, "https://internal.buanamultiteknik.com/api/bom/payment/info_approved?id=".$pk);
			}
		}
        $model->update($pk, $data);
        $response = [
            'status'   => true
        ];
        return $this->respond($response);
    }
 
    // delete product
    public function delete($pk = null)
    {
        $model = new PaymentModel();
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