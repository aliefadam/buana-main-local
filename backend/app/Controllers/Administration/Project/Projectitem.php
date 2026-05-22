<?php namespace App\Controllers\Administration\Project;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Project\ProjectitemModel;
 
class Projectitem extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new ProjectitemModel();
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
        $model = new ProjectitemModel();
        $json = $this->request->getJSON();
        
        $data = [
        ];
    

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
        
        //return print_r($data);
        
        // Hitung jumlah item untuk project_id tersebut
        $existingItems = $model
            ->where('project_id', $data['project_id'])
            ->where('flag', 1)
            ->select('sub_number')
            ->findAll();
            
        //return print_r($existingItems);
    
        // Ambil semua sub_number yang sudah terpakai
        $usedNumbers = array_column($existingItems, 'sub_number');
    
        // Cari angka sub_number 1-9 yang belum terpakai
        $available = null;
        for ($i = 1; $i <= 9; $i++) {
            if (!in_array($i, $usedNumbers)) {
                $available = $i;
                break;
            }
        }
    
        // Jika tidak ada slot yang tersedia
        if ($available === null) {
            return $this->respond([
                'status' => false,
                'data' => 'Maksimal 9 item per project_id telah tercapai'
            ], 400);
        }
    
        // Set sub_number otomatis
        $data['sub_number'] = $available;
        $data['flag'] = 1;
        
        //return print_r($data)
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
        $model = new ProjectitemModel();
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
        $model = new ProjectitemModel();
        $data = $model->find($pk);
        if($data){
            $model->update($pk, ["flag"=>0]);
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
 
}