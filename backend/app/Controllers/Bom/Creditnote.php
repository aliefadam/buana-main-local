<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\CreditnoteModel;
 
class Creditnote extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new CreditnoteModel();
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
            $model = new CreditnoteModel();
            $json = $this->request->getJSON();

            if (!$json) {
                return $this->failValidationErrors('Invalid or missing JSON payload.');
            }

            $data = [];

            foreach($model->allowedFields as $value) 
            {
                if(isset($json->{$value}))
                    $data[$value] = $json->{$value};
            }
            if(!isset($data["credit_no"])) {
                // Generate the next available credit_no in the format 0001/CN/MM/YY
                $currentMonth = date('m');
                $currentYear = date('y');
                $lastId = $model->orderBy('id', 'DESC')->first();
                if ($lastId) {
                    $lastCreditNo = $lastId->credit_no;
                    $lastParts = explode('/', $lastCreditNo);
                    if (count($lastParts) >= 3 && $lastParts[1] == 'CN' && $lastParts[2] == $currentMonth && $lastParts[3] == $currentYear) {
                        // Increment the last number
                        $nextNumber = intval($lastParts[0]) + 1;
                    } else {
                        // Reset to 0001 for a new month/year
                        $nextNumber = 1;
                    }
                } else {
                    // First entry
                    $nextNumber = 1;
                }
                $prefix = '/CN/' . $currentMonth . '/' . $currentYear;
                $data["credit_no"] = str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . $prefix;
            }
            $session = session();
            $s = $session->get();
            if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
                $data["created_by"] = $s["userid"];

            if (empty($data)) {
                return $this->failValidationErrors('No valid data provided for insert.');
            }

            if (!$model->insert($data)) {
                return $this->failValidationErrors($model->errors());
            }

            $response = [
                'status'   => true,
                'data'    => 'Data Saved'
            ];
            
            return $this->respondCreated($response, 200);
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
 
    public function update($pk = null)
    {
        $model = new CreditnoteModel();
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
    public function upload()
    {
        $json = $_REQUEST;
        $model = new CreditnoteModel();
		$session = session();
		$s = $session->get();
		
		$doc = $this->request->getFile('file');
		if(!empty($doc)){
			try {
				if (!$doc->hasMoved()) {
					$name = $doc->getRandomName();
					$realName = $doc->getName();
					$filepath = './uploads/credit_note/'.$json["id"].'/';
					$data["file"] = $filepath.$name;
					//$data['name'] = $realName;
					$doc->move($filepath, $name);
				}
				else{
					$response = [
						'status'   => false
					];
				
					return $this->respondCreated($response, 200);
				}
			} catch (Exception $err) {
				$response = [
					'status'   => false,
					'data'	=> $err
				];
			
				return $this->respondCreated($response, 200);
			}
		}
		
		$model->update($json["id"], $data);
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

    public function kurs(){
        $json = $_REQUEST;
        $currency = $json["currency"] ?? "IDR";
        $amount = $json["amount"] ?? 1;
        $html = file_get_contents('https://www.bi.go.id/id/statistik/informasi-kurs/transaksi-bi/Default.aspx');
        if(!$html){
            return $this->respond(['status' => false, 'data' => 'Failed to fetch data from BI'], 500);
        }
        // Cari kurs CNY
        $pattern = '/'.$currency.'\s*<\/td>\s*<td[^>]*>\s*([\d\.,]+)\s*<\/td>\s*<td[^>]*>\s*([\d\.,]+)\s*<\/td>/i';
        if (preg_match($pattern, $html, $m)) {
            $sell = floatval(str_replace(['.', ','], ['', '.'], $m[2]));
            return $this->respond(['sell'=>$sell, 'amount'=>$amount, 'exchange' => $amount * $sell], 200);
        } else {
            // Debug: return the HTML or error message for troubleshooting
            return $this->respond(['status' => false, 'data' => 'Currency not found or HTML structure changed.'], 500);
        }
    }

    public function delete($pk = null)
    {
        $model = new CreditnoteModel();
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