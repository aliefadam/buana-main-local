<?php namespace App\Controllers\Nomenclature;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Nomenclature\BastModel;
 
class Bast extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new BastModel();
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
        try{

        $model = new BastModel();
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

            $y = date("Y");
            $m = date('m');
            $m = (int)$m;

            function numberToRomanRepresentation($number) {
                $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
                $returnValue = '';
                while ($number > 0) {
                    foreach ($map as $roman => $int) {
                        if($number >= $int) {
                            $number -= $int;
                            $returnValue .= $roman;
                            break;
                        }
                    }
                }
                return $returnValue;
            }

            
            $noQuery = $model->lastNumber();
            if(!$noQuery[0])
                return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
            $no = $noQuery[1][0]->no;
            if (!isset($data['bast_no'])){
                $data["bast_no"] = "$no/BMT-BAST/".numberToRomanRepresentation($m)."/$y";
                // $data["bast_no"] = "abcd";
                $data["flag"]=1;
            }
           
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
        catch(\Throwable $e){
        $response = [
            'status'   => false,
            'data' => $e->getMessage()
        ];
        return $this->respond($response);
    }
}

 
    public function update($pk = null)
    {
        try{
		    helper(['Wa_helper']);
            $model = new BastModel();
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
        
    catch(\Throwable $e){
        $response = [
            'status'   => false,
            'data' => $e->getMessage()
        ];
        return $this->respond($response);
    }
}
 
    public function delete($pk = null)
    {
        $model = new BastModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
			$json = $_REQUEST;
			$data = [];
			$data["flag"] = $json["flag"] ?? 0;
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
        }else{
            $response = [
                'status'   => false,
				'data' => 'Data not found!'
            ];
            
            return $this->respond($response);
        }
         
    }
 
}