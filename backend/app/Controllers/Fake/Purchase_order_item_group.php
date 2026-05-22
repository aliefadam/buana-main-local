<?php namespace App\Controllers\Fake;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Fake\PurchaseOrderItemGroupModel;
 
class Purchase_order_item_group extends ResourceController
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
            $model = new PurchaseOrderItemGroupModel();
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
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

	public function editOrderNo(){
        try {
			$model = new PurchaseOrderItemGroupModel();
			$json = $this->request->getJSON();
			$res = $model->db->query("update purchase_order_item set order_no = ? where purchase_order_id = ? and item_id = ?", [$json->order_no, $json->purchase_order_id, $json->item_id]);
			$res = $model->db->query("update purchase_order_item set order_no = (order_no + 1) where purchase_order_id = ? and item_id != ? and order_no >= ?", [$json->purchase_order_id, $json->item_id, $json->order_no]);
			$data = "";
			if(!$res)
				$data = $model->db->error();
			return $this->respond(['status' => true, 'data'=>$data], 200);
		} catch (\Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}

    public function addbom(){
        $model = new PurchaseOrderItemGroupModel();
        $json = $this->request->getJSON();
		
		$check =  $model->getWhere(['flag' => 1, 'bom_id' => $json->bom_id, 'purchase_order_id' => $json->purchase_order_id])->getResult();

		if(count($check)>0){
			$response = [
				'status'   => false,
				'data'    => 'BOM telah digunakan di PO ini!'
			];
			return $this->respondCreated($response, 201);
		}
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

        $res = $model->where(['flag' => 1, 'purchase_order_id', $json->purchase_order_id])
        ->set(['active' => 0])
        ->update();

        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);

        $res = $model->insertBatch($tmp);

        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
        $model->where(['flag' => 1, 'purchase_order_id'=> $json->purchase_order_id, "active"=>0])
            ->delete();
        
        return $this->respondCreated(["status"=>true], 201);
    }

    public function addpr(){
        $model = new PurchaseOrderItemGroupModel();
        $json = $this->request->getJSON();
        $session = session();
		$s = $session->get();
		
		// $check =  $model->getWhere(['flag' => 1, 'pr_part_id' => $json->pr_part_id])->getResult();

		// if(count($check)>0){
		// 	$response = [
		// 		'status'   => false,
		// 		'data'    => 'Part pada PR ini telah digunakan!'
		// 	];
		// 	return $this->respondCreated($response, 201);
		// }
        $res = $model->read_pr_part($json->subledger_id);
        
        if(!$res[0])
            return $this->respondCreated(["status"=>false, "data"=>$res[1]], 200);
        $data = [];

        foreach($res as $value) 
        {
			$data[$value->item_id] = [];
			$data[$value->item_id]["item_id"] = $value->item_id;
			$data[$value->item_id]["allocation"] = $value->allocation;
			$data[$value->item_id]["order_qty"] = $value->order_qty;
			$data[$value->item_id]["pr_part_id"] = $json->pr_part_id;
			$data[$value->item_id]["price_per_item"] = $json->price_per_item;
			$data[$value->item_id]["subledger_id"] = $json->subledger_id;;
			$data[$value->item_id]["active"] = 1;
			$data[$value->item_id]["purchase_order_id"] = $json->purchase_order_id;
            $data[$value->item_id]["created_by"] =  $s["userid"];
        }

        $res = $model->insertBatch($data);

        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
        return $this->respondCreated(["status"=>true], 201);
    }
 
    // create a product
    public function create()
    {
        $model = new PurchaseOrderItemGroupModel();
        $json = $this->request->getJSON();
		

		$check =  $model->getWhere(['item_id' => $json->item_id])->getResult();

		if(count($check)>0){
			$response = [
				'status'   => false,
				'data'    => 'Item telah ada, gunakan Edit untuk mengganti qty!'
			];
			return $this->respondCreated($response, 201);
		}
        
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
        $model = new PurchaseOrderItemGroupModel();
        $json = $this->request->getJSON();
        if($json){
            $options = [
                'cost' => 11  
            ];

            
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
        $model = new PurchaseOrderItemGroupModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
            $model->update($pk, ["flag"=>0, "active"=>0]);
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