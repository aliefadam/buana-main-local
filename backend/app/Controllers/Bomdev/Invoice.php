<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\InvoiceModel;
use App\Models\PurchaseOrderModel;
 
class Invoice extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new InvoiceModel();
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

    public function saveBill(){
        $model = new InvoiceModel();
        $json = $this->request->getJSON();

        $data =[
            "payment_pct"=>$json->payment_pct,
            "invoice_charge"=>$json->invoice_charge,
            "invoice_reduction"=>$json->invoice_reduction,
            "notes"=>$json->notes
        ];
        $res = $model->where("id", $json->id)
        ->set($data)
        ->update();

        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
        
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 200);
    }
 
    // create a product
    public function create()
    {
        $model = new InvoiceModel();
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

        $res = $modelP->readAll($json->po_id);
        $check = $model->select(['payment_pct'])->getWhere(['po_id'=>$json->po_id, 'flag'=>1])->getResult();
        $pct = 0;
        foreach ($check  as $value) {
            $pct+= $value->payment_pct;
        }
        if($pct>=100)
            return $this->respondCreated(["status"=>false, "data"=> "PO telah dibayar 100%!"], 200);
        if(!$res)
            return $this->respondCreated($modelP->errors(), 200);
        $res = $res[0];
        $data["payment_pct"] = 100-$pct;
        $data["charge1"] = $res->charge1;
        $data["charge1_desc"] = $res->charge1_desc;
        $data["charge2"] = $res->charge2;
        $data["charge2_desc"] = $res->charge2_desc;
        $data["supplier_id"] = $res->supplier_id;
        $data["grand_total_price"] = $res->grand_total;
        $data["total_price"] = $res->total_price;
		
        $model->insert($data);
        $id = $model->getInsertID();
        $model->where('id', $id)
        ->set(['invoice_no' => "INV".str_pad($id, 4, "0", STR_PAD_LEFT)])
        ->update();
        $response = [
            'status'   => true,
			'res'	=> $res,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 200);
    }
 
    // update product
    public function update($pk = null)
    {
        $model = new InvoiceModel();
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
        // Insert to Database
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
 
    // delete product
    public function delete($pk = null)
    {
        $model = new InvoiceModel();
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