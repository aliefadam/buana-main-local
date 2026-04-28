<?php namespace App\Controllers\Rebuildmachine;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Rebuildmachine\Reportrebuild2Model;
 
class Reportrebuild2 extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new Reportrebuild2Model();
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
                $json["filter"] = json_decode($json["filter"] ?? '{}', true);
                $json["filterType"] = json_decode($json["filterType"] ?? '{}', true);
                $json["filterCondition"] = json_decode($json["filterCondition"] ?? '{}', true);
                $json = (Object) $json;

                // if(isset($json->filterCondition["full"])){
                //     $json->filterCondition["full"] = 'and';
                // }
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
 
    public function create()
    {
        $model = new Reportrebuild2Model();
        $json =  $_REQUEST;
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
				$noQuery = $model->lastNumber();
				if(!$noQuery[0])
				    return $this->respondCreated(['status' => false, 'data'=>$noQuery[1]], 200);
				$no = $noQuery[1][0]->no;
				if(in_array("created_by", $model->allowedFields) && isset($s["userid"]))
					$data["created_by"] = $s["userid"];
					$data["report_id"]=$no;
				
				$doc = $this->request->getFile('attachment');
				if(!empty($doc)){
					try {
						if (!$doc->hasMoved()) {
							$name = $doc->getRandomName();
							$realName = $doc->getName();
							$filepath = './uploads/rm2_'.$json["part_id"].'/';
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
    }
 
    public function update($pk = null)
    {
		try {
		
        $model = new Reportrebuild2Model();
        $json = $_REQUEST;//$this->request->getJSON();
		$json = (Object) $json;
        
        $data = [
        ];


        if($json){

             foreach($json as $key => $value) 
            {
                if($key!='pk' && $key != 'attachment')
                    $data[$key] = $value;
            }
		}
        $photo = $this->request->getFile('attachment');
		if(!empty($photo)){
			try {
				if (! $photo->hasMoved()) {
					
					$name = $photo->getRandomName();
					$realName = $photo->getName();
					$filepath = './uploads/rm2_'.$json->part_id.'/';
					$data["attachment"] = $name."+++".$realName;
					$photo->move($filepath, $name);
                    $tmpImg = $this->resizeImage($filepath.$name, 1000, 1000);
						imagejpeg($tmpImg, $filepath.$name);
				}
				else{
					$response = [
						'status'   => false
					];
					return $response;
				
					//return $this->respond($response, 200);
				}
			} catch (\Exception $err) {
				$response = [
					'status'   => false,
					'data'	=> $err->getMessage()
				];
				return $response;
			
				//return $this->respond($response, 200);
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
				'status'   => true,
				'data' => $data
			];
		else
			$response = [
				'status'   => false,
				'data'    => $model->errors()
			];
			return $response;
            //return $this->respond($response, 200);
		} 
		
    catch(\Throwable $e){
        $response = [
            'status'   => false,
            'data' => $e->getMessage()
        ];
		return $response;
       //return $this->respond($response);
    }

	}
 
    public function delete($pk = null)
    {
        $model = new Reportrebuild2Model();
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
}