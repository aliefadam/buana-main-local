<?php namespace App\Controllers\Ragic;
 
require 'vendor/autoload.php';
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Ragic\RagicModel;
use App\Models\Ragic\RagicsubModel;
use Tcdent\PhpRestclient;
 
class Ragic extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
            $model = new RagicModel();
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

	public function import(){
		//require 'restclient.php';
		//require '../../restclient.php';
		try{
				
			$db      = \Config\Database::connect();
			$api = new \Restclient([
				'base_url' => "https://na3.ragic.com/Websiterd/ragicrd", 
				//'format' => "json", 
				'parameters' => [
					'v'=>3,
					'api'=>''
				],
				// https://dev.twitter.com/docs/auth/application-only-auth
				'headers' => ['Authorization' => 'Basic VERJZUd5Vit4VWZ0aTl4R0toL01hS3I3TnJNbTlueVk0L01nL2hvQm1ZSTFqVUhxMVdjVFNRcS85VktmUFBEOA=='], 
			]);

			$q = $db->query("select modified_date from ragic where id=10004 order by modified_date desc limit 1");
			$q = $q->getResult();
			if(isset($q[0]))
				$result = $api->get("10004", ["info"=>"true", "where"=>"109,gt,".$q[0]->modified_date]);
			else
				$result = $api->get("10004", ["info"=>"true"]);
			
			/*$response = [
				'status'   => true,
				'data'    => $result
			];
			/* else
				$response = [
					'status'   => false,
					'data'    => $model->errors()
				]; */
			//return $this->respondCreated($response, 200);
			// GET http://api.twitter.com/1.1/search/tweets.json?q=%23php
			if($result->info->http_code == 200){
				//echo json_encode($result->decode_response(), JSON_PRETTY_PRINT);
				$model = new RagicModel();
				$model2 = new RagicsubModel();
				$this->request->setHeader('Accept', 'application/json');
				$data = $result->decode_response();
				$tmp = [];
				$tmp2 = [];
				$delete = [];
				$delete2 = [];
				foreach ($data as $key => $val) {
					$tmp[] = [
						"id"=>10004,
						"_ragicId"=>$val->{'_ragicId'},
						"created_by"=>$val->_create_user,
						"created_date"=>$val->{'_create_date'},
						"modified_date"=>$val->{'_update_date'},
						"description"=>$val->Description,
						"status"=>$val->Status,
						"priority"=>$val->Priority,
						"vendor"=>$val->Vendor,
						"report_by"=>$val->{'Report By'},
						"order_id"=>$val->{'Issue ID'},
						"item_name"=>$val->{'Issue Summary'},
						"order_date"=>$val->{'Reported Date'}
					];
					$delete[] = ["id"=>10004,
					"_ragicId"=>$val->{'_ragicId'}];

					if(isset($val->{'_subtable_2001279'})){
						foreach ($val->{'_subtable_2001279'} as $subkey => $sub) {
							$tmp2[] = [
								"description"=>$sub->Description,
								"modified_date"=>$sub->{'Update Date'},
								"modified_by"=>$sub->{'Update By'},
								"attachment"=>$sub->{'Attachment'},
								"parent_id"=>10004,
								"id"=>$sub->{'_ragicId'},
								"parent_ragic_id"=>$val->{'_ragicId'}
							];
							$delete2[] = [
								"parent_id"=>10004,
								"id"=>$sub->{'_ragicId'},
								"parent_ragic_id"=>$val->{'_ragicId'}
							];
						}
					}
				}
				$builder = $db->table('ragic');
				//var_dump(get_class_methods($builder));
				//$builder->deleteBatch($delete);
				foreach ($delete as $key => $del) {
					$builder->delete($del);
				}
				$builder->insertBatch($tmp);

				$builder = $db->table('ragic_sub');
				/* $builder->deleteBatch($delete2); */
				
				foreach ($delete2 as $key => $del) {
					$builder->delete($del);
				}
				$builder->insertBatch($tmp2);
				/* $affected = $builder->affectedRows();
				if($affected != 0) */
				$response = [
					'status'   => true,
					'data'    => 'Import done'
				];
				/* else
					$response = [
						'status'   => false,
						'data'    => $model->errors()
					]; */
				return $this->respondCreated($response, 200);
				//return $this->respondCreated(['asdf'], 200);
			}
			else
				return $this->respondCreated([
					'status'   => false,
					'data'    => 'Error Connection'
				], 200);
		}
		catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
		//var_dump(ROOTPATH . 'vendor/autoload.php');
	}

	public function import2(){
		//require 'restclient.php';
		//require '../../restclient.php';
		try{
				
			$db      = \Config\Database::connect();
			$api = new \Restclient([
				'base_url' => "https://na3.ragic.com/Websiterd/ragicrd", 
				//'format' => "json", 
				'parameters' => [
					'v'=>3,
					'api'=>''
				],
				// https://dev.twitter.com/docs/auth/application-only-auth
				'headers' => ['Authorization' => 'Basic VERJZUd5Vit4VWZ0aTl4R0toL01hS3I3TnJNbTlueVk0L01nL2hvQm1ZSTFqVUhxMVdjVFNRcS85VktmUFBEOA=='], 
			]);

			$q = $db->query("select modified_date from ragic where id=1 order by modified_date desc limit 1");
			$q = $q->getResult();
			//return isset($q[0]);
			if(isset($q[0]))
				$result = $api->get("1", ["info"=>"true", "where"=>"109,gt,".$q[0]->modified_date]);
			else
				$result = $api->get("1", ["info"=>"true"]);
			// GET http://api.twitter.com/1.1/search/tweets.json?q=%23php
			if($result->info->http_code == 200){
				//echo json_encode($result->decode_response(), JSON_PRETTY_PRINT);
				$model = new RagicModel();
				$model2 = new RagicsubModel();
				$this->request->setHeader('Accept', 'application/json');
				$data = $result->decode_response();
				$tmp = [];
				$tmp2 = [];
				$delete = [];
				$delete2 = [];
				foreach ($data as $key => $val) {
					$tmp[] = [
						"id"=>1,
						"_ragicId"=>$val->{'_ragicId'},
						"created_by"=>$val->_create_user,
						"created_date"=>$val->{'_create_date'},
						"modified_date"=>$val->{'_update_date'},
						"description"=>$val->{'Detail Description'},
						"status"=>$val->Status,
						"priority"=>$val->Priority,
						"vendor"=>$val->{'Related Customer'},
						"report_by"=>$val->{'Report By'},
						"order_id"=>$val->{'Order ID'},
						"item_name"=>$val->{'Item Name'},
						"order_date"=>$val->{'Order Date'}
					];
					$delete[] = ["id"=>1,
					"_ragicId"=>$val->{'_ragicId'}];

					if(isset($val->{'_subtable_1000111'})){
						foreach ($val->{'_subtable_1000111'} as $subkey => $sub) {
							$tmp2[] = [
								"description"=>$sub->Description,
								"modified_date"=>$sub->{'Update Date'},
								"modified_by"=>$sub->{'Update By'},
								"attachment"=>$sub->{'Attachment'},
								"parent_id"=>1,
								"id"=>$sub->{'_ragicId'},
								"parent_ragic_id"=>$val->{'_ragicId'}
							];
							$delete2[] = [
								"parent_id"=>1,
								"id"=>$sub->{'_ragicId'},
								"parent_ragic_id"=>$val->{'_ragicId'}
							];
						}
					}
				}
				$builder = $db->table('ragic');
				foreach ($delete as $key => $del) {
					$builder->delete($del);
				}
				$builder->insertBatch($tmp);

				$builder = $db->table('ragic_sub');
				
				foreach ($delete2 as $key => $del) {
					$builder->delete($del);
				}
				$builder->insertBatch($tmp2);
				$response = [
					'status'   => true,
					'data'    => 'Import done'
				];
				return $this->respondCreated($response, 200);
			}
			else
				return $this->respondCreated([
					'status'   => false,
					'data'    => 'Error Connection'
				], 200);
		}
		catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
    public function create()
    {
        $model = new RagicModel();
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
        $model = new RagicModel();
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
        $model = new RagicModel();
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

	public function logistic(){
		try{
			$db      = \Config\Database::connect();
			$q = $db->query("select i.item_no, po.currency, i.item_name, sum(pi.order_qty) as order_qty, 
			(select pi2.price_per_item from purchase_order po2 left join purchase_order_item pi2 on pi2.item_id = pi.item_id order by po2.po_date, pi2.id desc limit 1) as price_per_item
			from purchase_order_item pi 
			left join purchase_order po on po.id = pi.purchase_order_id 
			left join m_item i on i.id = pi.item_id 
			where pi.flag = 1 and i.id is not null and po.id is not null
			group by pi.item_id, i.item_no, i.item_name");
			$q = $q->getResult();
			//https://na3.ragic.com/Websiterd/logistic/1?api&where=1000430,eq,123
			

			$d = [];
			$a = [];
			$c = [];
			$e = [];
			$a["t"] = Array(
				"item_no"=>"item no",
				"item_name"=>"item name",
				"currency"=>"item currency",
				"order_qty"=>"order qty",
				"price_per_item"=>"price per item",
				"kdr"=>"On Hand Qty - Kediri",
				"sby"=>"On Hand Qty - Surabaya"
			);
			foreach ($q as $key => $value) {
				$d[] = "1000430,eq,".$value->item_no;
				$a[$value->item_no] = Array(
					"item_no"=>$value->item_no,
					"item_name"=>$value->item_name,
					"currency"=>$value->currency,
					"order_qty"=>number_format($value->order_qty, 1, ".", "") + 0,
					"price_per_item"=>number_format($value->price_per_item, 1, ".", "") + 0
				);
			}
			$api = new \Restclient([
				'base_url' => "https://na3.ragic.com/Websiterd/logistic", 
				//'format' => "json", 
				'parameters' => [
					'v'=>3,
					'api'=>'',
					"where[]"=>$d
				],
				// https://dev.twitter.com/docs/auth/application-only-auth
				'headers' => ['Authorization' => 'Basic VERJZUd5Vit4VWZ0aTl4R0toL01hS3I3TnJNbTlueVk0L01nL2hvQm1ZSTFqVUhxMVdjVFNRcS85VktmUFBEOA=='], 
			]);
			$result = $api->get("1");
			$b = '';
			$c[] = Array(
				"item_no"=>"item no",
				"item_name"=>"item name",
				"currency"=>"item currency",
				"order_qty"=>"order qty",
				"sby"=>"On Hand Qty - Kediri",
				"kdr"=>"On Hand Qty - Surabaya",
				"price_per_item"=>"price per item"
			);
			if($result->info->http_code == 200){
				$this->request->setHeader('Accept', 'application/json');
				$data = $result->decode_response();

				foreach ($data as $key => $value) {
					if(isset($a[$value->{"Item No."}])){
						$tmp = $a[$value->{"Item No."}];
						$a[$value->{"Item No."}]["kdr"] = number_format($value->{"On Hand Qty - Kediri"}, 1, ".", "") + 0;
						$a[$value->{"Item No."}]["sby"] = number_format($value->{"On Hand Qty - Surabaya"}, 1, ".", "") + 0;
						/* $a[$value->{"Item No."}] = Array(
							"item_no"=>$tmp->item_no,
							"item_name"=>$tmp->item_name,
							"order_qty"=>$tmp->order_qty,
							"sby"=>$value->{"On Hand Qty - Kediri"},
							"kdr"=>$value->{"On Hand Qty - Surabaya"}
						); */
					}


				}

				$fp = fopen('./download/file.csv', 'w');

				/* fputcsv($fp, Array(
					"item_no"=>"item no",
					"item_name"=>"item name",
					"order_qty"=>"order qty",
					"sby"=>"On Hand Qty - Kediri",
					"kdr"=>"On Hand Qty - Surabaya"
				)); */
				foreach ($a as $fields) {
					fputcsv($fp, $fields);
				}

				fclose($fp);

				$path2 = "./download/file.csv";

				header('Expires: 0');         // no cache
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($path2)).' GMT');
				header('Cache-Control: private',false);
				header('Content-Type: application/pdf');  // Add the mime type from Code igniter.
				header('Content-Disposition: attachment; filename="'.basename("file.csv").'"');  // Add the file name
				header('Content-Transfer-Encoding: binary');
				header('Content-Length: '.filesize($path2)); // provide file size
				header('Connection: close');
				readfile($path2);

				/* return $this->respondCreated([
					'status'   => true,
					'data'    => $b
				], 200); */
			}
			else{
				return $this->respondCreated([
					'status'   => false,
					'data'    => 'Error Connection'
				], 200);
			}
		}
		catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
	}
 
}