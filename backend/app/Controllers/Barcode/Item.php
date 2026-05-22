<?php namespace App\Controllers\Barcode;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Barcode\ItemModel;
 
class Item extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new ItemModel();
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

	public function report()
	{
		$model = new ItemModel();
		$json = $_REQUEST;
        $json = (Object) $json;
		$data = $model->report($json);
		return $this->respond(['status' => $data[0], 'data'=>$data[1]], 200);
	}

	public function infopercent()
	{
		$model = new ItemModel();
		$json = $_REQUEST;
        $json = (Object) $json;
		$data = $model->infopercent($json);
		return $this->respond(['status' => $data[0], 'data'=>$data[1]], 200);
	}

	public function infoarea()
	{
		$model = new ItemModel();
		$json = $_REQUEST;
        $json = (Object) $json;
		$data = $model->infoarea($json);
		return $this->respond(['status' => $data[0], 'data'=>$data[1]], 200);
	}

	public function image()
	{
		header('Content-Type: image/jpeg');
		$model = new ItemModel();
		$json = $_REQUEST;
        $json = (Object) $json;
		$data = $model->image($json->id);
		if($data[0]==false)
			return $this->respond($data[1], 200);
		
		header('Content-Type: image/jpeg');
		return $this->respond($data[1], 200);
	}

	public function imagefull()
	{
		$model = new ItemModel();
		$json = $_REQUEST;
        $json = (Object) $json;
		$data = $model->imagefull($json->id);
		if($data[0]==false)
			return $this->respond($data[1], 200);
		
		/* header('Content-Type: image/jpeg');
		header('Content-Disposition: attachment; filename="'+ $json->id+'.jpg"'); */
		return '<img src="'.$data[1].'" />';//$data[1];
	}

	public function imagebarcode()
	{
		$model = new ItemModel();
		$json = $_REQUEST;
        $json = (Object) $json;
		$data = $model->imagebarcode($json->barcode);
		if($data[0]==false)
			return $this->respond($data[1], 200);
		header('Content-Type: image/jpeg');
		return $this->respond($data[1], 200);
	}

	public function partnotregistered()
	{
		$model = new ItemModel();
		$json = $_REQUEST;
                $json = (Object) $json;
		$data = $model->partnotregistered($json);
		return $this->respond(['status' => $data[0], 'data'=>$data[1]], 200);
	}
 
    public function create()
    {
        $model = new ItemModel();
        $json = $this->request->getJSON();
        
        $data = [
        ];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json->{$value}))
				$data[$value] = $json->{$value};
        }

		if(trim($data["barcode"])==""){
			$response = [
				'status'   => false,
				'data'    => 'Barcode empty'
			];
        
			return $this->respondCreated($response, 200);
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
				'data'    => 'Data Saved',
				'insertID' => $model->getInsertID()
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
        $model = new ItemModel();
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

		if(trim($data["barcode"])==""){
			$response = [
				'status'   => false,
				'data'    => 'Barcode empty'
			];
        
			return $this->respondCreated($response, 200);
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
        $model = new ItemModel();
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
 
}