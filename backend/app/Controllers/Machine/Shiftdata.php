<?php namespace App\Controllers\Machine;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Machine\ShiftdataModel;
 
class Shiftdata extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new ShiftdataModel();
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
                $json["filter"] = json_decode(json_encode($json["filter"] ?? '{}', true), true);
                $json["filterType"] = json_decode(json_encode($json["filterType"] ?? '{}', true), true);
                $json["filterCondition"] = json_decode(json_encode($json["filterCondition"] ?? '{}', true), true);
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
        $model = new ShiftdataModel();
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
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
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
        $model = new ShiftdataModel();
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
        $model = new ShiftdataModel();
        $data = $model->find($pk);
        if($data){
            $model->delete($pk);
			//$data = [];
			//$data["flag"] = 0;
			//$session = session();
			//$s = $session->get();
			//if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			//	$data["modified_by"] = $s["userid"];
			//if(in_array("modified_date", $model->allowedFields))
			//	$data["modified_date"] = date('Y-m-d H:i:s', now());
            //$model->update($pk, ["flag"=>0]);
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

    public function without_detail(){
        try {
			$json = $_REQUEST;
            $model = new ShiftdataModel();
            if($json['limit']==3)
                $data = $model->data("select * from (select row_number() OVER (PARTITION BY shift_date ORDER BY shift_id asc) as shift, 
                    shift_date, id, shift_id, brand, shift_start, shift_time, break_time, total_production, total_waste, good_production, 
                    utilization_t, utilization_p, efficiency_t, efficiency_p, uptime, downtime from shift_data) s
                    where shift_date between '".$json['start']."' and '".$json['end']."' order by shift_id desc limit 3", []);
            else
                $data = $model->data("select * from (select row_number() OVER (PARTITION BY shift_date ORDER BY shift_id asc) as shift, 
                    shift_date, id, shift_id, brand, shift_start, shift_time, break_time, total_production, total_waste, good_production, 
                    utilization_t, utilization_p, efficiency_t, efficiency_p, uptime, downtime from shift_data) s
                    where shift_date between '".$json['start']."' and '".$json['end']."' order by id asc", []);
			if(!$data[0])
            	return $this->response->setJSON(['status'   => false, 'data'    => $data[1]]);
			return $this->response->setJSON(['status'   => true, 'data'    => $data[1]]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status'   => false, 'data'    => $e->getMessage()]);
        }

    }

    public function realtime(){
        try {
			$json = $_REQUEST;
            $model = new ShiftdataModel();
            $data = $model->data("select speed, good, waste, DATE_ADD(created_date, INTERVAL -14 HOUR) as created_date from realtime_data where DATE_ADD(created_date, INTERVAL -14 HOUR) between '".$json['start']."' and '".$json['end']."' order by id asc", []);
			if(!$data[0])
            	return $this->response->setJSON(['status'   => false, 'data'    => $data[1]]);
			return $this->response->setJSON(['status'   => true, 'data'    => $data[1]]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status'   => false, 'data'    => $e->getMessage()]);
        }

    }
 
}