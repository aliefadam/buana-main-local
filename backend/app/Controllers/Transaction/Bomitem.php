<?php namespace App\Controllers\Transaction;
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\BomitemModel;
 
class Bomitem extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new BomitemModel();
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
			$model = new BomitemModel();
			$json = $this->request->getJSON();
			
			$data = [
			];
			$session = session();
			$s = $session->get();

			if(isset($json->batch)){
				//insertBatch
				$arrData = explode(';;;', $json->batch);
				foreach ($arrData as $key => $value) {
					$tmp = explode(';;', $value);
					$tmpData = [];
					$tmpData["bom_receive_id"] = $tmp[0];
					$tmpData["part_number"] = $tmp[1];
					$tmpData["unit_qty"] = $tmp[2];
					$tmpData["qty"] = $tmp[3];
					$tmpData["description"] = $tmp[4];
					$tmpData["material"] = $tmp[5];
					$tmpData["dwg_no"] = $tmp[6];
					$tmpData["mass"] = $tmp[7];
					if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
						$tmpData["created_by"] = $s["userid"];
					$data[] = $tmpData;
				}
				$model->insertBatch($data);
			}
			else if(isset($json->batch2)){
				//insertBatch
				$arrData = explode(';;;', $json->batch2);
				foreach ($arrData as $key => $value) {
					$tmp = explode(';;', $value);
					$tmpData = [];
					$tmpData["bom_receive_id"] = $tmp[0];
					$tmpData["bom_unique_id"] = $tmp[1];
					$tmpData["item"] = $tmp[2];
					$tmpData["part_number"] = $tmp[3];
                    $tmpData["qty"] = $tmp[4];
					$tmpData["unit_qty"] = $tmp[5];
					$tmpData["description"] = $tmp[6];
					$tmpData["material"] = $tmp[7];
					$tmpData["dwg_no"] = $tmp[8];
					$tmpData["mass"] = $tmp[9];
					$tmpData["photo"] = $tmp[10];
					if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
						$tmpData["created_by"] = $s["userid"];
					$data[] = $tmpData;
				}
				$model->insertBatch($data);
 			}
			else{
				foreach($model->allowedFields as $value) 
				{
					if(isset($json->{$value}))
						$data[$value] = $json->{$value};
				}
				if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
					$data["created_by"] = $s["userid"];
					$data["flag"]=1;
				$model->insert($data);
			}
			$affected = $model->affectedRows();
			$data = $model->query("
				insert into m_item(item_no, noid, is_subpart, item_name, unit, item_type, specification)
				select concat(1, LPAD((lastnumber+noid-1+1), 4, 0)) as item_no, noid, 1 as is_subpart, item_name, unit, item_type, specification from(
					select row_number() over(partition by item_id order by part_number asc) as noid, s.part_number as item_name, 'PCS' as unit , 
					'Part' as item_type, concat(description, '\n', material, '\n', mass) as specification, 
					(
						select noid from m_item where item_type = 'Part' order by noid desc limit 1
					) as lastnumber
					from (
						select s.part_number, max(description) as description, max(material) as material, max(mass) as mass, max(item_id) as item_id from (
							SELECT trim(s.part_number) as part_number, s.description, s.material, s.mass, i.id as item_id
							FROM `bom_receive_item` s
							left join m_item i on i.item_name = s.part_number and i.specification = concat(description, '\n', material, '\n', mass)
						)s
						group by part_number, item_id
					)s 
					where item_id is null
				)s;
			");
			$data = $model->query("
				update bom_receive_item as t inner join m_item i on i.item_name = t.part_number and i.specification = concat(description, '\n', material, '\n', mass) set t.item_id = i.id where item_id is null
			");
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
        } catch (\Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function update($pk = null)
    {
        $model = new BomitemModel();
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
        $model = new BomitemModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
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