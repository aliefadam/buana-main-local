<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\PrpartModel;
 
class Prpart extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new PrpartModel();
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
        $model = new PrpartModel();
        $json = $this->request->getJSON();
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
		$session = session();
		$s = $session->get();

        $orderNoQuery = $model->lastNoOrder($data['pr_id']);
        if(!$orderNoQuery[0]){
            $data['order_no'] = 1;
        }else{
            $order_no = $orderNoQuery[1][0]->order_no;
            $data['order_no']  = number_format($order_no)+1;
        }

		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
        $data["flag"]=1;
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
 
    public function update($pk = null)
    {
        $model = new PrpartModel();
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
        $q = $model->info($pk);
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        if($q[0]){
            $d = $q[1][0];
            if($d->pr_rev>0 /* && $d->item_id != $data["item_id"] */){
                $data["rev"]=1;
            }
        }
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
        $model = new PrpartModel();
        $data = $model->find($pk);
        if($data){
            $data = [];
			$data["flag"] = 0;
			$session = session();
			$s = $session->get();
			if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["modified_by"] = $s["userid"];
			if(in_array("modified_date", $model->allowedFields))
				$data["modified_date"] = date('Y-m-d H:i:s');
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
        }else{
            $response = [
                'status'   => false,
				'data' => 'Data not found!'
            ];
            
            return $this->respond($response);
        }
         
    }

    public function editOrderNo(){
        try {
			$model = new PrpartModel();
			$json = $this->request->getJSON();
			$res = $model->db->query("update pr_part set order_no = ? where pr_id = ? and item_id = ?", [$json->order_no, $json->pr_id, $json->item_id]);
			$res = $model->db->query("update pr_part set order_no = (order_no + 1) where pr_id = ? and item_id != ? and order_no >= ?", [$json->pr_id, $json->item_id, $json->order_no]);
			$data = "";
			if(!$res)
				$data = $model->db->error();
			return $this->respond(['status' => true, 'data'=>$data], 200);
		} catch (\Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
}