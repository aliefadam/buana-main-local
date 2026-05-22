<?php namespace App\Controllers\Tickets;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Tickets\TicketsModel;
 
class Tickets extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new TicketsModel();
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

        $model = new TicketsModel();
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
        if(in_array("user_id", $model->allowedFields) && isset($s["userid"]))
            $data["user_id"] = $s["userid"];
        $noQuery = $model->lastNumber();
        
        if(!$noQuery[0])
            return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
        $no = $noQuery[1][0]->no;
        
        $data["ticket_no"] = date("Ym").$no;
        $data["flag"]=1;
        $insert = $model->insert($data);
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
        $model = new TicketsModel();
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
		if(in_array("user_id", $model->allowedFields) && isset($s["userid"]))
			$data["user_id"] = $s["userid"];
		if(in_array("updated_at", $model->allowedFields))
			$data["updated_at"] = date("Y-m-d H:i:s");
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
        $model = new TicketsModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
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

    public function getSummary(){
        $model = new TicketsModel();
        $query = $model->monitorSummary();
        return $this->respond(['data'=>$query]);
    }
 
}