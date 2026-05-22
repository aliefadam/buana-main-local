<?php namespace App\Controllers\Warehouse;
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Warehouse\SpbitemModel;
 
class Spbitem extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new SpbitemModel();
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
        try {
			$model = new SpbitemModel();
			$json = $this->request->getJSON();
			
			$data = [
			];

			foreach($model->allowedFields as $value) 
			{
				if(isset($json->{$value}))
					$data[$value] = $json->{$value};
			}
			if(isset($data['notes']))
				$data['notes'] = urldecode($data['notes']);
			if(isset($data['photo']))
				$data['photo'] = urldecode($data['photo']);
			$session = session();
			$s = $session->get();
			if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
				$data["created_by"] = $s["userid"];
			//return $this->respondCreated($data, 200);
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
		} catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function update($pk = null)
    {
        $model = new SpbitemModel();
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
        
        $data['notes'] = urldecode($data['notes']);
        $data['photo'] = urldecode($data['photo']);
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
        $model = new SpbitemModel();
        $data = $model->find($pk);
        if($data){
			$stock = $model->query("select purchase_order_item_id from stock where id = ".$pk."");
			$stock = $stock->getResult()[0];
			//return "update purchase_order_item set complete_qty = (complete_qty - (select qty from stock where id = ".$pk.")) where id = (select purchase_order_item_id from stock where id = ".$pk.")";
			$model->transBegin();
			if($stock->purchase_order_item_id!=null)
				$model->query("update purchase_order_item set complete_qty = (complete_qty - (select qty from stock where id = ".$pk.")) where id = (select purchase_order_item_id from stock where id = ".$pk.")");
            $model->delete($pk);
			$model->transComplete();
			if($model->transStatus() === false){
				$model->transRollback();
				$response = [
					'status'   => false,
					'data'    => $model->errors()
				];
			}
			else{
				$model->transCommit();
				$affected = $model->affectedRows();
				/* if($affected != 0)
					$response = [
						'status'   => true
					];
				else */
				$response = [
					'status'   => true
				];
			}
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