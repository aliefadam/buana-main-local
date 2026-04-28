<?php namespace App\Controllers\Finance;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Finance\PettycashdetailModel;
 
class Pettycashdetail extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new PettycashdetailModel();
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
    public function create(){
        $model = new PettycashdetailModel();
        $json2 = $this->request->getJSON();
        $json = $_REQUEST;
        
        $data = [];

        $session = session();
        $s = $session->get();
        //return print_r($json);
        if (isset($json2->batch)) {
            //insertBatch
            $arrData = explode(';;;', $json2->batch);
            foreach ($arrData as $key => $value) {
                $tmp = explode(';;', $value);
                $tmpData = [];
                
                $tmpData["rincian"] = $tmp[0];
                $tmpData["bukti"] = $tmp[1];
                $tmpData["pos_akun_kode"] = $tmp[2];
                $tmpData["debet"] = $tmp[3];
                $tmpData["kredit"] = $tmp[4];
                $tmpData["date_voucher"] = $tmp[5];
                $tmpData["no_voucher"] = $tmp[6];
                $tmpData["rekon_bank"] = $tmp[7];
                $tmpData["ppn"] = $tmp[8];
                $tmpData["pph23"] = $tmp[9];
                $tmpData["other"] = $tmp[10];
                $tmpData["beneficiary_name"] = $tmp[11];
                $tmpData["bank_account"] = $tmp[12];
                $tmpData["npwp"] = $tmp[13];
                $tmpData["po_reference"] = $tmp[14];
                $tmpData["invoice_date"] = $tmp[15];
                $tmpData["due_date"] = $tmp[16];
                $tmpData["invoice_ref"] = $tmp[17];
                $tmpData["nsfp"] = $tmp[18];
                $tmpData["status"]= $tmp[19];
                $tmpData["notes"] = $tmp[20];
                $tmpData["petty_cash_id"] = $tmp[21];
                
                if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
                    $tmpData["created_by"] = $s["userid"];
                    $tmpData["flag"] = '1';
                    $data[] = $tmpData;
                }
                //return print_r($data);
            $model->insertBatch($data);
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
        else {
            foreach($model->allowedFields as $value) 
            {
                if(isset($json[$value]) && $value != 'bukti')
                    $data[$value] = $json[$value];
            }
            //uploadfile
            $file = $this->request->getFile('bukti');		
            if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
                $data["created_by"] = $s["userid"];
            if (!empty($file) && ! $file->hasMoved()) {
                $name = $file->getRandomName();
                $filepath = './uploads/finance/bukti/'.$data['date_voucher'].random_int(0,3).'/';
                $data['bukti'] = 'https://internal.buanamultiteknik.com/api/'.$filepath.$name;
                $file->move($filepath, $name);
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

    }
    public function create2()
    {
        $model = new PettycashdetailModel();
        //$json = $this->request->getJSON();
        $json = $_REQUEST;
        
        $data = [];

        $session = session();
        $s = $session->get();

        if(isset($json->batch)){
            //insertBatch
            $arrData = explode(';;;', $json->batch);
            foreach ($arrData as $key => $value) {
                $tmp = explode(';;', $value);
                $tmpData = [];
                
                $tmpData["rincian"] = $tmp[0];
                $tmpData["bukti"] = $tmp[1];
                $tmpData["pos_akun_kode"] = $tmp[2];
                $tmpData["debet"] = $tmp[3];
                $tmpData["kredit"] = $tmp[4];
                $tmpData["date_voucher"] = $tmp[5];
                $tmpData["no_voucher"] = $tmp[6];
                $tmpData["rekon_bank"] = $tmp[7];
                $tmpData["ppn"] = $tmp[8];
                $tmpData["pph23"] = $tmp[9];
                $tmpData["other"] = $tmp[10];
                $tmpData["beneficiary_name"] = $tmp[11];
                $tmpData["bank_account"] = $tmp[12];
                $tmpData["npwp"] = $tmp[13];
                $tmpData["po_reference"] = $tmp[14];
                $tmpData["invoice_date"] = $tmp[15];
                $tmpData["due_date"] = $tmp[16];
                $tmpData["invoice_ref"] = $tmp[17];
                $tmpData["nsfp"] = $tmp[18];
                $tmpData["status"]= $tmp[19];
                $tmpData["notes"] = $tmp[20];
                
                
                if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
                    $tmpData["created_by"] = $s["userid"];
                    $tmpData["flag"] = '1';
                    $data[] = $tmpData;
                }
            $model->insertBatch($data);
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
    }
 
    public function update($pk = null)
    {
        $model = new PettycashdetailModel();
        $json = $this->request->getJSON();
        //json = $_REQUEST;
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
        $model = new PettycashdetailModel();
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
 
}