<?php namespace App\Controllers\Administration\Item;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\ItemModel;
 
class Item extends ResourceController
{
    use ResponseTrait;
    // get all product
    public function index()
    {
		$session = session();
		$s = $session->get();
        $authorization = false;
		if(!isset($s["userid"]))
            $authorization = false;
        else
            $authorization = true;
        
        if(isset(apache_request_headers()["Authorization"]))
            $authorization = apache_request_headers()["Authorization"];
        $host = apache_request_headers()["Host"];
        if($authorization!='19e4002f-f824-4dca-afaf-73fa6526ea33'){
            $authorization = false;
        }
        if(!$authorization)
			return $this->respond(['status' => false, 'message' => 'You are not allowed to view this page'], 401);
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
                $json["filter"] = json_decode(json_encode($json["filter"] ?? '{}', true), true);
                $json["filterType"] = json_decode(json_encode($json["filterType"] ?? '{}', true), true);
                $json["filterCondition"] = json_decode(json_encode($json["filterCondition"] ?? '{}', true), true);
                $json = (Object) $json;
                $data = $model->read($json);
                return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
            //code...
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

	public function create_from_bom(){
		
        try {
			$model = new ItemModel();
			//$json = $this->request->getJSON();
			$data = $model->db->query("
				insert into m_item(item_no, noid, is_subpart, item_name, unit, item_type, specification)
				select concat(1, LPAD((lastnumber+noid-1+1), 4, 0)) as item_no, noid, 1 as is_subpart, item_name, unit, item_type, specification from(
					select row_number() over(partition by item_id order by part_number asc) as noid, s.part_number as item_name, 'PCS' as unit , 
					'Part' as item_type, concat(description, '\n', material, '\n', mass) as specification, 
					(
						select noid from m_item where item_type = 'Part' order by noid desc limit 1
					) as lastnumber
					from (select s.part_number, max(description) as description, max(material) as material, max(mass) as mass, max(item_id) as item_id from (
						SELECT trim(s.part_number) as part_number, s.description, s.material, s.mass, i.id as item_id
						FROM `bom_receive_item` s
						left join m_item i on i.item_name = s.part_number
					)s
					group by part_number, item_id
				)s where item_id is null)s;
			");
			
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
			
			return $this->respondCreated($response, 200);
		} catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
    // create a product
    public function create()
    {
        try {
			$model = new ItemModel();
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

            $session = session();
		    $s = $session->get();
		    if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];

            $y = date("Y");
		    $m = date('m');

			$noQuery = $model->lastNumber($data['item_type']);
			if(!$noQuery[0])
				return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
			$data['noid'] = $noQuery[1][0]->number;
			$type = ["1"=>'Part', "3"=>'Jasa', "6"=>'Tool'];
			$typeid = array_search($data['item_type'], $type);
			$data['item_no'] = $typeid."".$noQuery[1][0]->no;
			
			//return $this->respondCreated($data, 200);
			//$data['noid'] = 3;
			$insert = $model->insert($data);
			$response = [
				'status'   => true,
				'data'    => 'Data Saved'
			];
			
			return $this->respondCreated($response, 200);
		} catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    // update product
    public function update($pk = null)
    {
        $model = new ItemModel();
        $json = $this->request->getJSON();
        if($json){
            $options = [
                'cost' => 11  
            ];

            
            $data = [
            ];

			
			$current = $model->data($pk);

            foreach($json as $key => $value) 
            {
                if($key!='pk')
                    $data[$key] = $value;
            }

            $session = session();
		    $s = $session->get();
		    if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		    if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");

			if(isset($data['item_type'])){
				if($current[1][0]->item_type != $data['item_type']){
					$noQuery = $model->lastNumber($data['item_type']);
					if(!$noQuery[0])
						return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
					$data['noid'] = $noQuery[1][0]->number;
					$type = ["1"=>'Part', "3"=>'Jasa', "6"=>'Tool'];
					$typeid = array_search($data['item_type'], $type);
					$data['item_no'] = $typeid."".$noQuery[1][0]->no;
				}
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
        $model = new ItemModel();
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