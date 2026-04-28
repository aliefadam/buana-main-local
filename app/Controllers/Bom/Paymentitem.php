<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PaymentItemModel;
use App\Models\InvoiceModel;
 
class Paymentitem extends ResourceController
{
    use ResponseTrait;

    public function invoicebyno()
    {
		$session = session();
		$s = $session->get();
		if(!isset($s["userid"]))
			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
            $invoiceNo = trim($this->request->getGet('invoice_no') ?? '');
            if($invoiceNo == '')
                return $this->respond(['status' => false, 'message' => 'invoice_no is required'], 200);

            $model = new InvoiceModel();
            $data = $model->where('invoice_no', $invoiceNo)
                ->where('flag', 1)
                ->first();
            if(!$data)
                return $this->respond(['status' => false, 'message' => 'Invoice not found'], 200);

            return $this->respond(['status' => true, 'data' => $data], 200);
        } catch (\Throwable $e) {
            return $this->respond(['status' => false, 'message' => $e->getMessage()], 200);
        }
    }

    // get all product
    public function index()
    {
		$session = session();
		$s = $session->get();
		if(!isset($s["userid"]))
			return $this->respond(['status' => false, 'message' => 'Unauthorized'], 401);
        try {
            $model = new PaymentItemModel();
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
                $json["filter"] = json_decode($json["filter"], true);
                $json["filterType"] = json_decode($json["filterType"], true);
                $json["filterCondition"] = json_decode($json["filterCondition"], true);
                $json = (Object) $json;
                $data = $model->read($json);
					
                return $this->respond([
                    'status' => true, 
                    'data'=>$data[0], 
                    'total' => $data[1],
                    "testing" => "HALOOO",
                ], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    // create a product
    public function create()
    {
        $model = new PaymentItemModel();
        $json = $this->request->getJSON();
        $options = [
            'cost' => 11  
        ];
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }
        //$data = json_decode(file_get_contents("php://input"));
        //$data = $this->request->getPost();
        $model->insert($data);
        $response = [
            'status'   => true,
            'data'    => 'Data Saved'
        ];
        
        return $this->respondCreated($response, 201);
    }
 
    // update product
    public function update($pk = null)
    {
        $model = new PaymentItemModel();
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
        $model = new PaymentItemModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
            $model->update($pk, ["flag"=>0]);
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
