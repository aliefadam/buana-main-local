<?php namespace App\Controllers\Document;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Document\NotesModel;
 
class Notes extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new NotesModel();
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
				$uri = service('uri');
				$segment = [];
				if(count($uri->getSegments())>2){
					$segment = $uri->getSegments();
				}
                $data = $model->read($json, $segment);
				$ex = explode('.', end($segment));
                
				if(end($ex)=='xlsx'){ 
				    helper(['Excel_helper']);
					$exporter = new \Xerobase\ExcelReporter\Export();
					exportExcel($data[1], $segment[2]);
				}
				else
                	return $this->respond(['status' => $data[0], 'data'=>$data[1], 'total' => $data[2]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }
 
    public function create()
    {
		try {
			helper(['Upload_helper']);
			$model = new NotesModel();
			$json = $_REQUEST;
			$json = (Object) $json;
			if(isset($json->pk)){
				$response = $this->update($json->pk);
				return $this->respond($response);
			}else{
				$uri = service('uri');
				$debug = [];
				$segment = [];
				if(count($uri->getSegments())>2){
					$segment = $uri->getSegments();
				}

				if(isset($segment)){
					//select table from alias
					$q = $model->db->query("select * from document_alias where alias = '".$segment[2]."'");
					$res = $q->getResult();
					$dt = $res[0]->table_name;
					$tmpDt = explode('dt_', $dt);
					$notes = 'notes_'.$tmpDt[1];
					$values = 'values_'.$tmpDt[1];
				}
				/* $document_id = null;
				if(isset($segment)){
					if(isset($segment[2])){
						$identifier = $segment[2];
						$q = $model->db->query("select document_id from document where identifier = '$identifier'");
						$res = $q->getResult();
						$document_id = $res[0]->document_id;
					}
				} */

				$data = [
				];
				$dataDoc = [];
				$status = true;
				$err = "";

				$uniqid = uniqid();
				$session = session();
				$s = $session->get();

				foreach($model->allowedFields as $value) 
				{
					if(isset($json->{$value}))
						$data[$value] = $json->{$value};
				}
				
				if(isset($json->root_id)){
					$dataDoc["root_id"] = $json->root_id;
				}

				//$data["document_id"] = $document_id;
				
				if(in_array("created_by", $model->allowedFields) && isset($s["userid"])){
					$data["created_by"] = $s["userid"];
					$dataDoc["created_by"] = $s["userid"];
				}

				$files = explode(';', $json->fileInfoParams);
				unset($data['fileInfoParams']);
				$statusUpload = true;
				foreach ($files as $key => $value) {
					$doc = $this->request->getFile($value);
					if(!empty($doc)){
						$error = false;
						$upload = uploadFile($doc, $error);
						if($upload==false){
							$statusUpload = false;
						}
						else{
							$json->{$value} = $upload["filepath"]."+++".$upload["filename"];
						}
					}
				}

				if($statusUpload==false){
					$response = [
						'status'   => false,
						'data'    => "Upload file failed!"
					];
					return $this->respondCreated($response, 200);
				}
				else{
				
					$dtTable = $model->db->table($dt);
					$dtTable->insert($dataDoc);
					$affected = $model->db->affectedRows();
					if($affected != 0){
						$idDoc = $model->db->insertID();
						$model->db->transBegin();
						
						$data["document_id"] = $idDoc;
						
						$model->db->table($notes)->insert($data);
						$affected = $model->db->affectedRows();
						if($affected != 0){
							$response = [
								'status'   => true,
								'data'    => 'Data Saved'
							];
							$id = $model->db->insertID();
							//$model->db->query("insert into document_identifier (document_id, identifier, note_id) values(?, coalesce((select identifier from document_identifier i where i.document_id = ? order by i.identifier desc limit 1 ), 0)+1, ?)", [$document_id, $document_id, $id]);
							foreach($json as $key => $value) 
							{
								if($key!='pk' && !preg_match("/^field_/", $key)){
									$data[$key] = $value;
								}else if(preg_match("/^field_/", $key)){
									$fields = explode('_', $key);
			
									$valueData = [];
									$q = "insert into $values(uniqid, document_field_id, value_".$fields[1].", notes_id, created_by) values(?,?,?,?,?)";
									$valueData[] = $uniqid;
									$valueData[] = $fields[2];
									$valueData[] = $value;
									$valueData[] = $id;
									$valueData[] = $s["userid"];
									$debug[] = [$q, $valueData];
									$model->db->query($q, $valueData);
									/* $valueData["document_field_id"] = $fields[2];
									$valueData["value_".$fields[1]] = $value;
									$valueData["notes_id"] = $json->pk;
									$valueData["created_by"] = $s["userid"]; */
								}
							}
							if ($model->db->transStatus() === false) {
								$model->db->transRollback();
								$status = false;
								//$debug[] = $model->db->error();
								$err = $debug;//"Insert Error";
							} else {
								$model->db->transCommit();
							}
						}
						else{
							$model->db->transRollback();
							$response = [
								'status'   => false,
								'data'    => $model->errors()
							];
						}
					}else{
						$response = [
							'status'   => false,
							'data'    => $model->errors()
						];
					}
					if(!$status){
						$response = [
							'status'   => false,
							"data" => $err
						];
						return $this->respond($response);
					}
					else
						return $this->respondCreated($response, 200);
				}
			}
			//code...
		} catch (\Throwable $th) {
			$response = [
				'status'   => false,
				"data" => $th->getMessage()
			];
			return $this->respond($response);
		}
    }
 
    public function update($pk = null)
    {
		try {
			helper(['Upload_helper']);
			$model = new NotesModel();
			$json = $_REQUEST;
			$json = (Object) $json;
			$session = session();
			$s = $session->get();
			$q = [];
			$status = true;
			$err = "";
			$uri = service('uri');
			$debug = [];
			$segment = [];
			
			if(count($uri->getSegments())>2){
				$segment = $uri->getSegments();
			}

			if(isset($segment)){
				//select table from alias
				$q = $model->db->query("select * from document_alias where alias = '".$segment[2]."'");
				$res = $q->getResult();
				$dt = $res[0]->table_name;
				$tmpDt = explode('dt_', $dt);
				$notes = 'notes_'.$tmpDt[1];
				$values = 'values_'.$tmpDt[1];
			}
			$uniqid = uniqid();
			try {
				if($json){
					$data = [
					];

					$files = explode(';', $json->fileInfoParams);
					unset($data['fileInfoParams']);
					$statusUpload = true;
					foreach ($files as $key => $value) {
						$doc = $this->request->getFile($value);
						if(!empty($doc)){
							$error = false;
							$upload = uploadFile($doc, $error);
							if($upload==false){
								$statusUpload = false;
							}
							else{
								$json->{$value} = $upload["filepath"]."+++".$upload["filename"];
							}
						}
					}

					if($statusUpload==false){
						$response = [
							'status'   => false,
							'data'    => "Upload file failed!"
						];
						$status = false;
					}
					else{
		
						$model->db->transBegin();
						foreach($json as $key => $value) 
						{
							if($key!='pk' && !preg_match("/^field_/", $key) && $key != 'fileInfoParams' && !isset($data[$key])){
								$data[$key] = $value;
							}else if(preg_match("/^field_/", $key)){
								$fields = explode('_', $key);
			
								$valueData = [];
								$q = "insert into $values(uniqid, document_field_id, value_".$fields[1].", notes_id, created_by) values(?,?,?,?,?)";
								$valueData[] = $uniqid;
								$valueData[] = $fields[2];
								$valueData[] = $value;
								$valueData[] = $json->pk;
								$valueData[] = $s["userid"];
								$debug[] = [$q, $valueData];
								$model->db->query($q, $valueData);
								/* $valueData["document_field_id"] = $fields[2];
								$valueData["value_".$fields[1]] = $value;
								$valueData["notes_id"] = $json->pk;
								$valueData["created_by"] = $s["userid"]; */
							}
						}
						if ($model->db->transStatus() === false) {
							$model->db->transRollback();
							$status = false;
							$err = "Insert Error";
							$debug[] = $model->errors();
						} else {
							$model->db->transCommit();
							$debug[] = $model->errors();
						}
					}
				}
			} catch (\Throwable $error) {
				$status = false;
				$err = $error->getMessage();	
				$debug[] = $err;	
			}
			if(!$status){
				$response = [
					'status'   => false,
					"data" => $debug
				];
				return $response;//$this->respond($response);
			}
			if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
				$data["modified_by"] = $s["userid"];
			if(in_array("modified_date", $model->allowedFields))
				$data["modified_date"] = date("Y-m-d H:i:s");
			//$model->update($json->pk, $data);
			$model->db->table($notes)->where(["note_id" => $json->pk])->set($data)->update();
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
			return $response;//$this->respond($response);
		} catch (\Throwable $th) {
			$response = [
				'status'   => false,
				'data'    => $th->getMessage()
			];
			return $response;//$this->respond($response);
		}
		
    }
 
    public function delete($pk = null)
    {
		try {
			$model = new NotesModel();
			$json = $_REQUEST;
			$segment = [];
			$uri = service('uri');
			
			if(count($uri->getSegments())>2){
				$segment = $uri->getSegments();
			}

			if(isset($segment)){
				//select table from alias
				$q = $model->db->query("select * from document_alias where alias = '".$segment[2]."'");
				$res = $q->getResult();
				$dt = $res[0]->table_name;
				$tmpDt = explode('dt_', $dt);
				$notes = 'notes_'.$tmpDt[1];
				$values = 'values_'.$tmpDt[1];
			}

			$q = $model->db->query("select * from $notes where note_id = '".$json[$model->primaryKey]."'");//$model->$db->table($notes)->find($json[$model->primaryKey]);
			
			$data = $q->getResult();

			if(count($data)>0){

				//$model->delete($pk);
				$data = [];
				$data["flag"] = $json["flag"] ?? 0;
				$session = session();
				$s = $session->get();
				if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
					$data["modified_by"] = $s["userid"];
				if(in_array("modified_date", $model->allowedFields))
					$data["modified_date"] = date("Y-m-d H:i:s");
				$q = $model->db->table($notes)->where(["note_id" => $json[$model->primaryKey]])->set($data)->update();
				$affected = $model->db->affectedRows();
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
			//code...
		} catch (\Throwable $th) {
			$response = [
				'status'   => false,
				'data' => $th->getMessage()
			];
			
			return $this->respond($response);
		}
         
    }
 
}