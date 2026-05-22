<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PurchaseOrderModel;
use App\Models\PaymentModel;
 
class Purchase_order extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new PurchaseOrderModel();
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
				if(isset($json->invoice) && isset($json->filter["id"])){
					unset($json->filter["total_payment_pct"]);
				}
				//return $this->respond(["a"=>$json]);
                $data = $model->read($json);
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

    public function addcharge(){
        $model = new PurchaseOrderModel();
        $json = $this->request->getJSON();
        $data = ["charge1"=>$json->charge1, "charge2"=>$json->charge2];
        $res = $model->where('id', $json->purchase_order_id)
        ->set($data)
        ->update();
        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
        return $this->respondCreated(["status"=>true], 201);
    }
 
    // create a product
    public function create()
    {
        $model = new PurchaseOrderModel();
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
		
		$session = session();
		$s = $session->get();
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
		
		$y = date("Y");
		$m = date('m');

		$noQuery = $model->lastNumber($y, $data['dept_id']);
		if(!$noQuery[0])
			return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
		$no = $noQuery[1][0]->no;
		$dept_code = $noQuery[1][0]->dept_code;
		if(!isset($data["po_no"])){
			$data["po_no"] = "$no/".$dept_code."-PO/$m/$y";
			$data["year"] = $y;
			$data["no"] = $noQuery[1][0]->number;
		}
		else{
			$tmp = explode('/', $data["po_no"]);
			$data["year"] = $tmp[3];
			$data["no"] = str_pad($tmp[0],4,"0", STR_PAD_LEFT);
		}
		
        $model->insert($data);
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 200);
    }

	public function insertInfo($info, $uid, $pk){
		$model = new PaymentModel();
		$data = $model->db->query("insert into info (info, uid, url) values('".$info."', '".$uid."', 'https://internal.buanamultiteknik.com/api/bom/purchase_order/info?id=".$pk."')");
	}

	public function info(){
        $model = new PurchaseOrderModel();
        $json = $_REQUEST;
		$q = $model->info($json['id']);
		$text = $q[1];
		if($q[0]){
			$d = $q[1][0];
			$text = "
			<table class='tpl'>
			<tr><td><div style='min-width: 100px;'><b>PO No: </b></div></td><td style='width: 100%;'>".$d->po_no."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>PO Date: </b></div></td><td>".$d->po_date."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>PO Title: </b></div></td><td>".$d->title."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Supplier: </b></div></td><td>".$d->supplier."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Ask for approval date: </b></div></td><td>".$d->approval_date."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Approved date: </b></div></td><td>".$d->approval_2_date."</td></tr>
			<tr><td><div style='min-width: 100px;'><b>Approved by: </b></div></td><td>".$d->approved2_by_name."</td></tr>
			";
		}
        $response = [
            'status'   => $q[0],
            'data'    => $text,
			'title'   => "Purchase Order Information"
        ];
        
        return $this->respondCreated($response, 200);
	}
 
    public function update($pk = null)
    {
        $model = new PurchaseOrderModel();
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

		if(isset($data["approved"])){
			if($data["approved"]==2){
				if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
					$data["approval_by"] = $s["userid"];

				if(in_array("approval_date", $model->allowedFields))
					$data["approval_date"] = date("Y-m-d H:i:s");
				
				$this->insertInfo("<b>Approved:</b> ".date("Y-m-d H:i:s")."", "po_approved_".$pk, $pk);
			}

			if($data["approved"]==1){

				if(in_array("ask_approval_date", $model->allowedFields))
					$data["ask_approval_date"] = date("Y-m-d H:i:s");
			}
		}
        // Insert to Database
        $model->update($pk, $data);
        $response = [
            'status'   => true
        ];
        return $this->respond($response);
    }
 
    // delete product
    public function delete($pk = null)
    {
        $model = new PurchaseOrderModel();
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