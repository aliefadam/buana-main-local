<?php namespace App\Controllers\Suratjalan;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Suratjalan\SuratJalanItemGroupModel;
 
class Suratjalanitemgroup extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new SuratJalanItemGroupModel();
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
    
	public function resizeImage($filename, $max_width, $max_height)
	{
		list($orig_width, $orig_height) = getimagesize($filename);

		$width = $orig_width;
		$height = $orig_height;

		# taller
		if ($height > $max_height) {
			$width = ($max_height / $height) * $width;
			$height = $max_height;
		}

		# wider
		if ($width > $max_width) {
			$height = ($max_width / $width) * $height;
			$width = $max_width;
		}

		$image_p = imagecreatetruecolor($width, $height);

		$image = imagecreatefromjpeg($filename);

		imagecopyresampled($image_p, $image, 0, 0, 0, 0, 
										$width, $height, $orig_width, $orig_height);

		return $image_p;
	}
 
  /*  public function create()
    {
        $model = new SuratJalanItemGroupModel();
        
         $json = $_REQUEST;
		$json2 = (Object) $json;
		if(isset($json2->pk)){
			$response = $this->update($json2->pk);
			return $this->respond($response);
		}else{
			try{
			$data = [
			];

        foreach($model->allowedFields as $value) 
        {
			if(isset($json[$value]))
				$data[$value] = $json[$value];
        }
		$session = session();
		$s = $session->get();
		if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
			$data["created_by"] = $s["userid"];
			
	    $doc = $this->request->getFile('attachment');
				if(!empty($doc)){
					try {
						if (!$doc->hasMoved()) {
							$name = $doc->getRandomName();
							$realName = $doc->getName();
							$filepath = './uploads/sj'.$json["sj_id"].'/';
							$data["attachment"] = $name."+++".$realName;
							$doc->move($filepath, $name);
                            $tmpImg = $this->resizeImage($filepath.$name, 1000, 1000);
                            imagejpeg($tmpImg, $filepath.$name);
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
				
        $model->insert($data);
		$affected = $model->affectedRows();
		if($affected != 0)
			$response = [
				'status'   => true,
				'data'    => 'Data Saved',
				"d"=>!empty($doc),
				"e"=>$data
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors(),
                't'=>$data
			];
        }
        catch(\Throwable $e){
            $response = [
                'status' => false,
                'data'=>$e->getMessage()
            ];
        }
        
        return $this->respondCreated($response, 200);
    }
}*/
 
//     public function update($pk = null)
//     {
//         try{
//             $model = new SuratJalanItemGroupModel();
//             $json = $_REQUEST;//$this->request->getJSON();
//             $json = (Object) $json;
            
//             $data = [];
//             if($json){
    
//                 foreach($json as $key => $value) 
//             {
//                 if($key!='pk' && $key != 'attachment')
//                     $data[$key] = $value;
//             }
//             }
//             $session = session();
//             $s = $session->get();
//             $photo = $this->request->getFile('attachment');
//             if(!empty($photo)){
// 			try {
// 				if (! $photo->hasMoved()) {
					
// 					$name = $photo->getRandomName();
// 					$realName = $photo->getName();
// 					$filepath = './uploads/sj'.$json->sj_id.'/';
// 					$data["attachment"] = $name."+++".$realName;
// 					$photo->move($filepath, $name);
//                     $tmpImg = $this->resizeImage($filepath.$name, 1000, 1000);
// 						imagejpeg($tmpImg, $filepath.$name);
// 				}
// 				else{
// 					$response = [
// 						'status'   => false
// 					];
// 					return $response;
				
// 					//return $this->respond($response, 200);
// 				}
// 			} catch (\Exception $err) {
// 				$response = [
// 					'status'   => false,
// 					'data'	=> $err->getMessage()
// 				];
// 				return $response;
			
// 				//return $this->respond($response, 200);
// 			}
// 		}
		
//             // if(in_array("modified_by", $model->allowedFields) && isset($s["userid"]))
//             //     $data["modified_by"] = $s["userid"];
//             // if(in_array("modified_date", $model->allowedFields))
//             //     $data["modified_date"] = date("Y-m-d H:i:s");
//             if(isset($data["reject"])){
//                 if($data["reject"]==1){
//                     if(in_array("reject_notes", $model->allowedFields))
//                     $data["reject_notes"] = $json->reject_notes;
//                 }
//             }
//             $model->update($pk, $data);
//             $affected = $model->affectedRows();
//             if($affected != 0)
//                 $response = [
//                     'status'   => true
//                 ];
//             else
//                 $response = [
//                     'status'   => false,
//                     'data'    => $model->errors()
//                 ];
//             return $this->respond($response);
//         }
//         catch(\Throwable $e){
//             $response = [
//                 'status'   => false,
//                 'data' => $e->getMessage()
//             ];
//             return $this->respond($response);
//         }
 
//     }
 
    public function delete($pk = null)
    {
        $model = new SuratJalanItemGroupModel();
        $data = $model->find($pk);
        if($data){
            //$model->delete($pk);
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

    public function addnpb(){
        $model = new SuratJalanItemGroupModel();
        $json = $this->request->getJSON();
		
		// $check =  $model->getWhere(['flag' => 1, 'item_id' => $json->sj_item_id])->getResult();

		// if(count($check)>0){
		// 	$response = [
		// 		'status'   => false,
		// 		'data'    => 'Part pada Surat Jalan ini telah digunakan!'
		// 	];
		// 	return $this->respondCreated($response, 201);
		// }
        $res = $model->read_npb_part($json->npb_item_id);

        
        
        if(!$res[0])
            return $this->respondCreated(["status"=>false, "data"=>$res[1]], 200);
        $data = [];

        foreach($res as $value) 
        {
            $tmp = [
                "item_id" => $value->item_id,
                "sj_id" => $json->sj_id,
                "qty" => $value->qty,
                "notes"=>$json->notes,
                "short_desc"=>$json->short_desc,
                "npb_item_id"=>$json->npb_item_id
                ];
			/*$data[$value->item_id] = [];
			$data[$value->item_id]["item_id"] = $value->item_id;
            $data[$value->item_id]["qty"] = $value->qty;
            $data[$value->item_id]["notes"] = $value->notes;*/
            $data[] = $tmp;
        }

        $res = $model->insertBatch($data);

        if(!$res)
            return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
        return $this->respondCreated(["status"=>true], 201);
    }

    // public function addmanual(){
    //     $model = new SuratJalanItemGroupModel();
    //     $modelI= new ItemModel();
    //     $json = $this->request->getJSON();

    //     if(isset($json->item_name)){
    //         $modelI->query("insert into m_item (item_name, unit, is_active, flag) values (?, ?, ?, ?,?)", [$json->item_name, $json->unit, 1, 1])
    //     }
    //     $id=$modelI->getInsertID();

    //     foreach($res as $value) 
    //     {
    //         $tmp = [
    //             "item_id" => $id,
    //             "sj_id" => $json->sj_id,
    //             "qty" => $value->qty,
    //             "notes"=>$json->notes,
    //             "short_desc"=>$json->short_desc
    //             ];
    //         $data[] = $tmp;
    //     }

    //     $res = $model->insertBatch($data);


    //     if(!$res)
    //         return $this->respondCreated(["status"=>false, "data"=>$model->errors()], 201);
        
    //     return $this->respondCreated(["status"=>true], 201);
    // }
 
 
}