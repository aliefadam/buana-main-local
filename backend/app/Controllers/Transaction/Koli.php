<?php namespace App\Controllers\Transaction;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\KoliModel;
use App\Models\Transaction\SubkolipartModel;
use Throwable;

class Koli extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new KoliModel();
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
        $model = new KoliModel();
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
			$data["flag"]=1;
		
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
        $model = new KoliModel();
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
        $model = new KoliModel();
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

	public function auto(){
		
        try {
			$model = new KoliModel();
			$json = $this->request->getJSON();
			$session = session();
			$s = $session->get();
			$user = 0;
			if(isset($s["userid"]))
				$user = $s["userid"];
			$tmp = Array();
			for ($i=0; $i < $json->qty; $i++) { 
				$tmp[] = "('-', '".$user."')";
			}
			$q = $model->query("
				insert into koli (description, created_by) 
				values".implode(',', $tmp).";
			");
			$q = $model->query("
				select * from koli order by id desc limit ".$json->qty."
			");
			$data = $q->getResult();
			$response = [
				'status'   => true,
				'data'   => $data
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}

	public function addscan(){
		
        try {
			$model = new KoliModel();
			$json = $this->request->getJSON();
			$session = session();
			$s = $session->get();
			$user = 0;
			if(isset($s["userid"]))
				$user = $s["userid"];
			$tmp = Array();
			$columns = array();
			$where = array();
			if($json->subkoli_id != null && $json->item_id != null && $json->koli_id != null){
				$check = $model->query("select * from subkoli_part where subkoli_id = ? and item_id = ? limit 1", [$json->subkoli_id, $json->item_id]);
				$columns = ['photo', 'photo2', 'photo3', 'qty', 'koli_id'];
				$where = ["subkoli_id", 'item_id'];
			}else if($json->subkoli_id != null && $json->item_id != null){
				$check = $model->query("select * from subkoli_part where subkoli_id = ? and item_id = ? limit 1", [$json->subkoli_id, $json->item_id]);
				$columns = ['photo', 'photo3', 'qty', 'koli_id'];
				$where = ["subkoli_id", 'item_id'];
			}else if($json->subkoli_id != null && $json->koli_id != null){
				$check = $model->query("select * from subkoli_part where subkoli_id = ? and koli_id = ? limit 1", [$json->subkoli_id, $json->koli_id]);
				$columns = ['photo', 'photo2'];
				$where = ["subkoli_id", 'koli_id'];
			}else if($json->item_id != null && $json->koli_id != null){
				$check = $model->query("select * from subkoli_part where item_id = ? and koli_id = ? limit 1", [$json->item_id, $json->koli_id]);
				$columns = ['photo2', 'photo3', 'qty'];
				$where = ["item_id", 'koli_id'];
			}else if($json->koli_id != null){
				$check = $model->query("select * from subkoli_part where koli_id = ? limit 1", [$json->koli_id]);
				$columns = ['photo2'];
				$where = ['koli_id'];
			}else if($json->subkoli_id != null){
				$check = $model->query("select * from subkoli_part where subkoli_id = ? limit 1", [$json->subkoli_id]);
				$columns = ['photo'];
				$where = ['subkoli_id'];
			}
			if(count($columns) > 0 && isset($check->getResult()[0])){
				$updates = ['modified_by = ?'];
				$params = [$user];
				$whereQ = [];
				foreach($json as $key => $value) 
				{
					if(in_array($key, $columns)){
						$updates[] = $key." = ?";
						$params[] = $value;
					}
				}
				foreach($json as $key => $value) 
				{
					if(in_array($key, $where)){
						$whereQ[] = $key." = ?";
						$params[] = $value;
					}
				}
				
				$check = $model->query("
					update subkoli_part set modified_date = now(), ".implode(", ", $updates)." where ".implode(" and ", $whereQ)."
				", $params);
			}
			else{
				$check = $model->query("
					insert into subkoli_part (koli_id, subkoli_id, item_id, bom_header_id, photo, photo2, photo3, qty, location, created_by, bom_receive_id) 
					values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
				", [$json->koli_id, $json->subkoli_id, $json->item_id, $json->bom_header_id, $json->photo, $json->photo2, $json->photo3, $json->qty, $json->location, $user, $json->bom_receive_id]);
			
				/*if($json->item_id!=null && $json->item_id!=false && $json->item_id!='')
					if($json->subkoli_id!=null && $json->subkoli_id!=false && $json->subkoli_id!='')
						if($json->koli_id!=null && $json->koli_id!=false && $json->koli_id!='')
							$model->query("
								update subkoli_part set koli_id = 
							");*/
			}
			$response = [
				'status'   => true,
				"data" => $model->db->error()
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}

	public function bomreceive(){
		
        try {
			$model = new KoliModel();
			$json = $_REQUEST;
			
			$q = $model->query("
				select br.id as value, concat(br.name, '-', br.machine_no) as text from bom_receive br
				left join bom_receive_item bi on bi.bom_receive_id = br.id
				left join m_item i on i.item_name = bi.part_number
				where i.id = ? and br.bom_header_id = ?
			", [$json["item_id"], $json["bom_header_id"]]);
			$data = $q->getResult();
			$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
    public function updatescan()
    {
        $model = new SubkolipartModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='id')
                    $data[$key] = $value;
            }
        }
        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        $model->update($json->id, $data);
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

	public function updatescan2(){
		
        try {
			$model = new KoliModel();
			$json = $this->request->getJSON();
			$session = session();
			$s = $session->get();
			$user = 0;
			if(isset($s["userid"]))
				$user = $s["userid"];
			$tmp = Array();
			$q = $model->query("
				update subkoli_part set koli_id = ?, subkoli_id = ?, bom_header_id = ?, photo = ?, qty = ?, location = ?, modified_by = ?, modified_date = now()
				where id = ?
			", [-1, $json->subkoli_id, $json->item_id, $json->bom_header_id, $json->photo, $json->qty, $json->location, $json->id, $user]);
			$r = $model->db->getLastQuery();
			$response = [
				'status'   => true,
				"data" => $model->db->error(),
				"q" => $r->getQuery()
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
	
    public function subkolilist(){
		
        try {
			$model = new KoliModel();
			$json = $_REQUEST;
			$q = $model->query("
		select ROW_NUMBER() OVER () AS no,  s.* from (Select s.id, bh.description, br.machine_no, p.photo from subkoli s 
			left join subkoli_part p on p.subkoli_id = s.id
			left join bom_receive_header bh on bh.id=p.bom_header_id
            left join bom_receive br on br.id=p.bom_receive_id
			where p.id is not null and p.koli_id = ? and p.flag=1) s;
			", $json["koli_id"]);
			$data = $q->getResult();
			$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
	    public function subkolipartlist(){
		
        try {
			$model = new KoliModel();
			$json = $_REQUEST;
			$q = $model->query("
		select ROW_NUMBER() OVER () AS no,  s.* from (Select s.qty, s.location, s.photo,i.item_name, i.specification, s.photo3 from subkoli_part s 
left join bom_receive_header bh on bh.id=s.bom_header_id
left join bom_receive br on br.id=s.bom_receive_id
left join m_item i on i.id=s.item_id

where s.subkoli_id = ? and s.flag=1) s;
			", $json["subkoli_id"]);
			$data = $q->getResult();
			$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
}