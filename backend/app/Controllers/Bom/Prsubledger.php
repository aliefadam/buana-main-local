<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Bom\PrsubledgerModel;
use App\Models\Bom\PrpartModel;
 
class Prsubledger extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new PrsubledgerModel();
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
        $model = new PrsubledgerModel();
        $modelPart = new PrpartModel();
        $json = $this->request->getJSON();
        $q = $modelPart->info($json->pr_part_id);

        if($q[0]){
            $d = $q[1][0];
            if($d->pr_rev>0/*  && $d->item_id != $data["item_id"] */){
                $dataPart = [
                ];
                $dataPart["rev"]=1;
                $modelPart->update($json->pr_part_id, $dataPart);
            }
        }
        
        $is_warning = false;
        $force_budget_minus_reason = null;
        if ($json->project_type == "Project") {
            $client = \Config\Services::curlrequest();
            $url = "https://panel.buanamultiteknik.com/api/budget/project-budget/index"
                 . "?project_id={$json->project_id}"
                 . "&budget_id={$json->budget_id}";
    
            $response = $client->get($url);
            $data = json_decode($response->getBody(), true);
            
            $remaining = $data['budget']['remaining'];
            
            $total = $json->unit_price * $json->qty;
            if ($json->currency != "IDR") {
                $total *= $json->exchange_rate;
            }
            
            if ($total > $remaining) {
        //      $response = [
     	//			'status'   => false,
     	//			'data'    => "SISA BUDGET TIDAK CUKUP!",
     	//			'reason' => $json->force_budget_minus_reason ?? null,
		//	    ];
        //        return $this->respondCreated($response, 500);
                
                $is_warning = true;
                $force_budget_minus_reason = $json->force_budget_minus_reason ?? null;
            }
        }
        
                
        $data = [];

        foreach($model->allowedFields as $value)
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
        
        $data['is_warning'] = $is_warning ? 1 : 0;
        $data['force_budget_minus_reason'] = $force_budget_minus_reason;
    
		$session = session();
		$s = $session->get();
        $data["flag"] = 1;
        
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"])) {
			$data["created_by"] = $s["userid"];
		}
		
        $model->insert($data);
		$affected = $model->affectedRows();
		if($affected != 0) {
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
		}
		else {
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
		}
        
        return $this->respondCreated($response, 200);
    }
 
    public function update($pk = null)
    {
        try {
            $model = new PrsubledgerModel();
            $json = $this->request->getJSON();

            /*$modelPart = new PrpartModel();
            $q = $modelPart->info($json->pr_part_id);

            if($q[0]){
                $d = $q[1][0];
                if($d->pr_rev>0/*  && $d->item_id != $data["item_id"]){
                    $dataPart = [
                    ];
                    $dataPart["rev"]=1;
                    //$modelPart->update($json->pr_part_id, $dataPart);
                }
            }*/
            
            if ($json->project_type == "Project") {
                $client = \Config\Services::curlrequest();
                $url = "https://panel.buanamultiteknik.com/api/budget/project-budget/index"
                     . "?project_id={$json->project_id}"
                     . "&budget_id={$json->budget_id}";
        
                $response = $client->get($url);
                $data = json_decode($response->getBody(), true);
                
                $remaining = $data['budget']['remaining'];
                
                $total = $json->unit_price * $json->qty;
                if ($json->currency != "IDR") {
                    $total *= $json->exchange_rate;
                }
                
        //         if ($total > $remaining) {
        //             $response = [
        // 				'status'   => false,
        // 				'data'    => "SISA BUDGET TIDAK CUKUP!"
    			 //   ];
        //             return $this->respondCreated($response, 500);
        //         }
            }
            
            if($json){
                $data = [];
    
                foreach($json as $key => $value) 
                {
                    if($json->project_type != "Project") {
                        $data["project_id"] = "";
                    }
                    if($key!='pk') {
                        $data[$key] = $value;
                    }
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
                if($d->pr_rev>0 && ($d->project_id != $data["project_id"] || $d->description != $data["description"] || $d->allocation != $data["allocation"]) || $d->qty !=$data['qty']){
                    $model->db->query("update pr_part set rev = 1 where id = ".$d->pp_id);
                    $response = [
                        'status'   => true
                    ];
                }
            }
        
            $model->update($pk, $data);
            $affected = $model->affectedRows();
        
            if ($affected != 0) {
                $response = [
                    'status' => true
                ];
            } else {
                $response = [
                    'status' => false,
                    'data'   => $model->errors()
                ];
            }
        } catch (\Throwable $e) {
            $response = [
                'status'  => false,
                'data' => $e->getMessage()
            ];
            return $this->respond($response, 500);
        }
        return $this->respond($response);
    }
 
    public function delete($pk = null)
    {
        $model = new PrsubledgerModel();
        $data = $model->find($pk);
        $q = $model->info($pk);
        if($data){
            // $model->delete($pk);
            $data = [];
			$data["flag"] = 0;
			$session = session();
			$s = $session->get();
        
			if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["modified_by"] = $s["userid"];
			if(in_array("modified_date", $model->allowedFields))
				$data["modified_date"] = date('Y-m-d H:i:s');
            if($q[0]){
                $d = $q[1][0];
                if($d->pr_rev>0){
                    $model->db->query("update pr_part set rev = 1 where id = ".$d->pp_id);
                    $response = [
                        'status'   => true
                    ];
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
        }else{
            $response = [
                'status'   => false,
				'data' => 'Data not found!'
            ];
            
            return $this->respond($response);
        }
         
    }
 
}