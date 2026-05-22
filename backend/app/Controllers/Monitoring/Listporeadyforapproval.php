<?php namespace App\Controllers\Monitoring;
 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\Monitoring\ListporeadyforapprovalModel;
 
class Listporeadyforapproval extends ResourceController
{
    use ResponseTrait;
	
    public function index()
    {
        try {
			$uri = service('uri');
			$segment = [];
			$segment = $uri->getSegments();
            $model = new ListporeadyforapprovalModel();
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
                $data = $model->readyforapprovalPo($json);
				$ex = explode('.', end($segment));
				if(end($ex)=='xlsx'){
					$exporter = new \Xerobase\ExcelReporter\Export();
					$exporter->export($data[1]);
				}
				else
                    return $this->respond(['status' => true, 'data'=>$data[0], 'total' => $data[1]], 200);
            }
        } catch (Exception $e) {
            return $this->respond(['status' => false, 'data'=>$e->getMessage()], 200);
        }
    }

    
	public function summary(){
    $db = db_connect();
    $query = $db->query('SELECT 
      ROW_NUMBER() OVER (
        ORDER BY 
          s.id DESC
      ) AS no, 
      s.id, 
      s.po_no, 
      s.approved, 
      s.title, 
      s.flag, 
      s.created_by, 
      s.created_by_name, 
      s.grand_total_price, 
      s.currency 
    FROM 
      (
        SELECT 
          s.id, 
          s.po_no, 
          s.approved, 
          s.title, 
          s.flag, 
          s.created_by, 
          u.name AS created_by_name, 
          i.grand_total_price, 
          s.currency 
        FROM 
          purchase_order s 
          LEFT JOIN users u ON u.id = s.created_by 
          LEFT JOIN (
            SELECT 
              SUM(order_qty * price_per_item) AS grand_total_price, 
              purchase_order_id 
            FROM 
              purchase_order_item 
            WHERE 
              flag = 1 
            GROUP BY 
              purchase_order_id
          ) i ON i.purchase_order_id = s.id 
        WHERE 
          s.flag = 1 
          AND (
            s.approved = 1 
            OR s.approved = 2
          )
      ) s;')->getResult();

    $array = [];
    $cny = 0;
    $eur = 0;
    $idr = 0;
    $usd = 0;
    $gbp = 0;
    foreach ($query as $key => $val) {
      if ($val->currency == 'CNY') {
        $cny = $cny + $val->grand_total_price;
        // array_push($array, ['currency' => $val->currency, 'grand_total_price' => $val->grand_total_price]);
      }
      elseif ($val->currency == 'EUR') {
        $eur = $eur + $val->grand_total_price;
        // array_push($array, ['currency' => $val->currency, 'grand_total_price' => $val->grand_total_price]);
      }
      elseif ($val->currency == 'IDR') {
        $idr = $idr + $val->grand_total_price;
        // array_push($array, ['currency' => $val->currency, 'grand_total_price' => $val->grand_total_price]);
      }
      elseif ($val->currency == 'USD') {
        $usd = $usd + $val->grand_total_price;
        // array_push($array, ['currency' => $val->currency, 'grand_total_price' => $val->grand_total_price]);
      }
      elseif ($val->currency == 'GBP') {
        $gbp = $gbp + $val->grand_total_price;
        // array_push($array, ['currency' => $val->currency, 'grand_total_price' => $val->grand_total_price]);
      }
    }
    array_push($array, ['CNY' => $cny]);    
    array_push($array, ['EUR' => $eur]);
    array_push($array, ['IDR' => $idr]);
    array_push($array, ['USD' => $usd]); 
    array_push($array, ['GBP' => $gbp]); 
    
		$response = [
			'status' => true,
			'data' => $array
		];
		return $this->respond($response);
    }
 
}