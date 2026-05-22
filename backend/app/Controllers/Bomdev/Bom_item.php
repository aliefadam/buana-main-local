<?php namespace App\Controllers\Bom;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BomItemModel;
 
class Bom_item extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
        try {
            $model = new BomItemModel();
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
					
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    // create a product
    public function create()
    {
        $model = new BomItemModel();
        $json = $this->request->getJSON();
        
        $data = [
        ];

        if($json->required_qty<=0||$json->order_qty<=0){
            $response = [
                'status'   => false,
                'data'    => 'Order qty atau required qty tidak sesuai!'
            ];
            return $this->respondCreated($response, 201);
        }

		$check =  $model->getWhere(['item_id' => $json->item_id, 'bom_header_id' => $json->bom_header_id])->getResult();

		if(count($check)>0){
			$response = [
				'status'   => false,
				'data'    => 'Item telah ada, gunakan Edit untuk mengganti qty!'
			];
			return $this->respondCreated($response, 201);
		}

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
        $model = new BomItemModel();
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
        $model = new BomItemModel();
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