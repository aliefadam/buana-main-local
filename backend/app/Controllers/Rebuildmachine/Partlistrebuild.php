<?php namespace App\Controllers\Rebuildmachine;

 

use CodeIgniter\RESTful\ResourceController;

use CodeIgniter\API\ResponseTrait;

use App\Models\Rebuildmachine\PartlistrebuildModel;

 

class Partlistrebuild extends ResourceController

{

    use ResponseTrait;

	

    public function index()

    {

        try {

            $model = new PartlistrebuildModel();

            if($this->request->getMethod() == 'put'){

                $json = $this->request->getJSON();



                return $this->update($json->pk);

            }

            else if($this->request->getMethod() == 'delete'){

                $json = $_REQUEST;

                return $this->delete($json[$model->primaryKey]);

            }

            else if($this->request->getMethod() == 'post'){

                $json = $this->request->getJSON();

                return $this->create();

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
        try{
        $model = new PartlistrebuildModel();
        $json = $this->request->getJSON();
        
        $data = [

        ];
        $session = session();
		$s = $session->get();

        //return print_r($json);
        
        if(isset($json->batch)){
            //insertBatch
			$arrData = explode(';;;', $json->batch);
			foreach ($arrData as $key => $value) {
				$tmp = explode(';;', $value);
				$tmpData = [];
                $tmpData["sub_assembly"] = $tmp[0];
				$tmpData["sub_assembly_desc"] = $tmp[1];
				$tmpData["part_number"] = $tmp[2];
				$tmpData["part_number_desc"] = $tmp[3];
                $tmpData["section"] = $tmp[4];
				$tmpData["article_number"] = $tmp[5];
				$tmpData["index_no"] = $tmp[6];
				$tmpData["qty"] = $tmp[7];
				$tmpData["uom"] = $tmp[8];
				$tmpData["brand"] = $tmp[9];
				$tmpData["is_part"] = $tmp[10];
				if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			    	$tmpData["created_by"] = $s["userid"];
			    	$tmpData["flag"] = '1';
					$data[] = $tmpData;
				}
			$model->insertBatch($data);
		} else{
		    foreach($model->allowedFields as $value) {
		        if(isset($json->{$value}))
		            $data[$value] = $json->{$value}; 
		    }
		    if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
                $data["created_by"] = $s["userid"];
                $data["flag"] = '1';
            $model->insert($data);
		}

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
    } catch(\Throwable $e){
        return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);

        }

}

 

    public function update($pk = null)

    {

        $model = new PartlistrebuildModel();

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

        $model = new PartlistrebuildModel();

        $data = $model->find($pk);

        if($data){

            $model->update($pk, ["flag"=>0]);

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