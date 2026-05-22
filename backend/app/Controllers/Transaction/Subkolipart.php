<?php namespace App\Controllers\Transaction;
set_time_limit(0);
ini_set('memory_limit', '550M');
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Transaction\SubkolipartModel;
 
class Subkolipart extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new SubkolipartModel();
            if($this->request->getMethod() == 'put'){
                $json = $this->request->getJSON();
				if(isset($json->pk_subkoli))
					return $this->updateKoliPhoto($json->pk_koli);
				if(isset($json->pk_koli))
					return $this->updateSubkoliPhoto($json->pk_subkoli);
				// else
    //             	return $this->update($json->pk);
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
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function create()
    {
        $model = new SubkolipartModel();
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
        try{
        $model = new SubkolipartModel();
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
        catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }

    }
 
    public function updateKoliPhoto($pk = null)
    {
        try{
                    $model = new SubkolipartModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk_koli')
                    $data[$key] = $value;
            }
        }
        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        //$model->update($pk, $data);
		$model->where('koli_id',  $json->pk_koli)
		->set(['photo' =>$json->photo, "modified_by" => $s["userid"], "modified_date" => date("Y-m-d H:i:s")])
		->update();
		$affected = $model->affectedRows();
		//if($affected != 0)
		$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response);
// 		else
// 			$response = [
// 				'status'   => false,
// 				'data'    => $model->errors()
// 			];
//         return $this->respond($response);
            
        }
        catch (\Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$json], 200);
        }
        

    }

    public function photokoli(){
        $model = new SubkolipartModel();
        /* $q = $model->query("select photo from subkoli_part where koli_id = 1");
        $data = $q->getResult();
			$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response); */
            
        $json = $_REQUEST;
        $json = (Object) $json;
        $data = $model->photo(1);
        header('Content-Type: '.$data[2]);
        if($data[0]==false)
            return $this->respond($data[1], 200);
        
        header('Content-Type: '.$data[2]);
        return $this->respond($data[1], 200);
    }
 
    public function updateSubkoliPhoto($pk = null)
    {
        try{
                    $model = new SubkolipartModel();
        $json = $this->request->getJSON();
        if($json){
            $data = [
            ];

            foreach($json as $key => $value) 
            {
                if($key!='pk_subkoli')
                    $data[$key] = $value;
            }
        }
        $session = session();
		$s = $session->get();
		if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
			$data["modified_by"] = $s["userid"];
		if(in_array("modified_date", $model->allowedFields))
			$data["modified_date"] = date("Y-m-d H:i:s");
        //$model->update($pk, $data);
		$model->where('subkoli_id',  $json->pk_subkoli)
		->set(['photo2' =>$json->photo2, "modified_by" => $s["userid"], "modified_date" => date("Y-m-d H:i:s")])
		->update();
		$affected = $model->affectedRows();
		//if($affected != 0)
		$response = [
				'status'   => true,
				"data" => $data
			];
			return $this->respond($response);
// 		else
// 			$response = [
// 				'status'   => false,
// 				'data'    => $model->errors()
// 			];
//         return $this->respond($response);
            
        }
        catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$json], 200);
        }
        

    }
 
    public function delete($pk = null)
    {
        $model = new SubkolipartModel();
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

	public function photo(){
		try{
			$model = new SubkolipartModel();
			$json = $_REQUEST;

            header('Content-Type: image/png');
            $json = $_REQUEST;
            $json = (Object) $json;
            $data = $model->photo($json->id);
            if($data[0]==false)
                return $this->respond($data[1], 200);
            
            header('Content-Type: image/png');
            return $this->respond($data[1], 200);

        }
        catch(Throwable $e){
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}

	public function photo2(){
		try{
			$model = new SubkolipartModel();
			$json = $_REQUEST;

            header('Content-Type: image/jpeg');
            $json = $_REQUEST;
            $json = (Object) $json;
            $data = $model->photo2($json->id);
            if($data[0]==false)
                return $this->respond($data[1], 200);
            
            header('Content-Type: image/jpeg');
            return $this->respond($data[1], 200);

        }
        catch(Throwable $e){
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}

	public function photo3(){
		try{
			$model = new SubkolipartModel();
			$json = $_REQUEST;

            header('Content-Type: image/jpeg');
            $json = $_REQUEST;
            $json = (Object) $json;
            $data = $model->image($json->id);
            if($data[0]==false)
                return $this->respond($data[1], 200);
            
            header('Content-Type: image/jpeg');
            return $this->respond($data[1], 200);

        }
        catch(Throwable $e){
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
    public function report()
    {
        try {
			$model = new SubkolipartModel();
			$json = $_REQUEST;
			$q = $model->query("
            select no, no_subkoli, no_po, id, po_no, qty, location, 
            case when no = 1 then
                (select photo2 from subkoli_part where koli_id = b.koli_id and photo2 is not null order by modified_date desc limit 1)
            else '' end as photo2,
			case when no_subkoli = 1 then
                (select photo from subkoli_part where subkoli_id = b.subkoli_id and photo is not null order by modified_date desc limit 1)
            else '' end as photo, subkoli_id,
            po_id, br_id, koli_id, item, dwg_no, part_number, description, material, application
            from (
                select ROW_NUMBER() OVER (partition by s.koli_id order by s.koli_id, s.subkoli_id, s.po_id, s.id) AS no, 
                ROW_NUMBER() OVER (partition by s.subkoli_id order by s.koli_id, s.subkoli_id, s.po_id, s.id) AS no_subkoli, 
                ROW_NUMBER() OVER (partition by s.po_id order by s.koli_id, s.subkoli_id, s.po_id, s.id) AS no_po, 
                s.* from (
                    Select s.id, po.po_no, s.qty, s.location, s.photo, s.photo2, s.subkoli_id, po.id as po_id, br.id as br_id, s.koli_id, bi.item, bi.dwg_no,
                    bi.part_number, bi.description, bi.material, concat(bh.description, '-', br.machine_no ) as application
                    from subkoli_part s 
                    left join bom_receive_header bh on bh.id=s.bom_header_id
                    left join bom_receive br on br.id=s.bom_receive_id
                    left join purchase_order po on po.id = br.po_id
                    left join koli k on k.id = s.koli_id
                    left join m_item i on i.id=s.item_id
                    left join bom_receive_item bi on bi.item_id=s.item_id and bi.bom_receive_id=br.id
                    where s.container_id = ? and s.flag=1 and bi.id is not null
                )s
            )b
			", $json["container_id"]);
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
 
    public function update_container()
    {
        try {
			$model = new SubkolipartModel();
			$json = $_REQUEST;
			$q = $model->query("
			update subkoli_part set container_id = ".$json["container_id"]." where koli_id = ".$json["koli_id"]."");
			$response = [
				'status'   => true
			];
			return $this->respond($response);
        } catch (Throwable $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
}