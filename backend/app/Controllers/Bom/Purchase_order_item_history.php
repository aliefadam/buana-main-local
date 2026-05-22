<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PurchaseOrderItemHistoryModel;
 
class Purchase_order_item_history extends ResourceController
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
            $model = new PurchaseOrderItemHistoryModel();
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

    public function addbom(){
        $model = new PurchaseOrderItemHistoryModel();
        $json = $this->request->getJSON();
        $res = $model->read_bom( $json->bom_id, $json->purchase_order_id);
        
        if(!$res[0])
            return $this->respondCreated(["status"=>false, "data"=>$res[1]], 200);
        $data = [];

        foreach($res as $value) 
        {
            if(!isset($data[$value->item_id])){
                $data[$value->item_id] = [];
                $data[$value->item_id]["item_id"] = $value->item_id;
                $data[$value->item_id]["order_qty"] = $value->order_qty;
                $data[$value->item_id]["bom_id"] = $json->bom_id;
                $data[$value->item_id]["active"] = 1;
                $data[$value->item_id]["purchase_order_id"] = $json->purchase_order_id;
            }
            else{
                $data[$value->item_id]["order_qty"] += $value->order_qty;
            }
        }

        $tmp =[];

        foreach ($data as $value) {
            array_push($tmp, $value);
        }

        $res = $model->where('purchase_order_id', $json->purchase_order_id)
        ->set(['active' => 0])
        ->update();

        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);

        $res = $model->insertBatch($tmp);

        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
        $model->where(['purchase_order_id'=> $json->purchase_order_id, "active"=>0])
            ->delete();
        
        return $this->respondCreated(["status"=>true], 201);
    }
 
    // create a product
    public function create()
    {
        $model = new PurchaseOrderItemHistoryModel();
        $json = $this->request->getJSON();
		

		/* $check =  $model->getWhere(['item_id' => $json->item_id])->getResult();

		if(count($check)>0){
			$response = [
				'status'   => false,
				'data'    => 'Item telah ada, gunakan Edit untuk mengganti qty!'
			];
			return $this->respondCreated($response, 201);
		} */
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
		
		$data["active"] = 1;
		
		$session = session();
		$s = $session->get();
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
        
        
        $model->insert($data);
		$insert = $model->affectedRows();
		$model->where(["purchase_order_id"=>$json->purchase_order_id, "item_id"=>$json->item_id])
		->set(["price_per_item"=>$json->price_per_item])
		->update();
		if($insert == 0)
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
		else
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
        
        return $this->respondCreated($response, 201);
    }
 
    // update product
    public function update($pk = null)
    {
        $model = new PurchaseOrderItemHistoryModel();
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
        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
		$model->update($pk, $data);
        $response = [
            'status'   => true
        ];
        return $this->respond($response);
    }
 
    // delete product
    public function delete($pk = null)
    {
        $model = new PurchaseOrderItemHistoryModel();
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