<?php namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\DataModel;
use CodeIgniter\API\ResponseTrait;
 
class Data extends Controller
{
    use ResponseTrait;
    public function index(){
        if($this->request->getMethod() == 'delete'){
            $session = session();
            $session->destroy();
            return $this->respond(['status'   => true]);
        }
        return '';
    }

    public function get(){
        //return 'aaa'
        try {
            $model = new DataModel();
			if($this->request->getMethod() == 'post'){
                $json = $this->request->getJSON();
				$data = $model->data($json->q);
            }
			else{
				$json = $_REQUEST;
				//$data = json_decode(file_get_contents("php://input"));
				$data = $model->data($json['q']);
			}
            if(!$data[0])
            	return $this->respond(['status'   => false, 'data'    => $data[1]]);
			return $this->respond(['status'   => true, 'data'    => $data[1]]);
        } catch (Exception $e) {
            return $this->respond(['status'   => false, 'data'    => $e]);
        }
    }

    public function podata(){
        try {
            $model = new DataModel();
			if($this->request->getMethod() == 'post'){
                $json = $this->request->getJSON();
				$data = $model->data($json->q);
            }
			else{
				$json = $_REQUEST;
				//$data = json_decode(file_get_contents("php://input"));
				$data = $model->data("SELECT p.ref_offer_no, u.name as dir, ms.pic_name, ms.id as supplier_id, p.currency, p.note as keterangan, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (
						
					select s.id, coalesce(s.order_no, s.id) as order_no, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, 
					i.unit, i.original_manufacture, i.manufacture_pn, i.article_no, sum(s.order_qty) as order_qty, 
					s.price_per_item 
					from purchase_order_item s left join m_item i on i.id = s.item_id
					where i.id is not null and s.flag = 1
					group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, s.purchase_order_id, s.price_per_item
				)s 
				left join m_item i on i.id = s.item_id
				left join purchase_order p on p.id = s.purchase_order_id
				left join m_supplier ms on ms.id = p.supplier_id
				left join m_department d on d.id = p.dept_id
				left join users u on u.id = d.approval_1
				where p.id = ".$json['id']." order by s.order_no");
			}
            if(!$data[0])
            	return json_encode(['status'   => false, 'data'    => $data[1]]);
			return json_encode(['status'   => true, 'data'    => $data[1]]);
        } catch (Exception $e) {
            return json_encode(['status'   => false, 'data'    => $e]);
        }
    }

    public function po(){
        try {
            $model = new DataModel();
			if($this->request->getMethod() == 'post'){
                $json = $this->request->getJSON();
				$data = $model->data($json->q);
            }
			else{
				$json = $_REQUEST;
				//$data = json_decode(file_get_contents("php://input"));
				$data = $model->data("SELECT p.ref_offer_no, u.name as dir, ms.pic_name, ms.id as supplier_id, p.currency, p.note as keterangan, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (
						
					select s.id, coalesce(s.order_no, s.id) as order_no, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, 
					i.unit, i.original_manufacture, i.manufacture_pn, i.article_no, sum(s.order_qty) as order_qty, 
					s.price_per_item 
					from purchase_order_item s left join m_item i on i.id = s.item_id
					where i.id is not null and s.flag = 1
					group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, s.purchase_order_id, s.price_per_item
				)s 
				left join m_item i on i.id = s.item_id
				left join purchase_order p on p.id = s.purchase_order_id
				left join m_supplier ms on ms.id = p.supplier_id
				left join m_department d on d.id = p.dept_id
				left join users u on u.id = d.approval_1
				where p.id = ".$json['id']." order by s.order_no");
				$po = $model->data("select coalesce((select sum(payment_pct) from invoice where po_id = s.id), 0) as total_payment_pct, ms.pic_name, ms.address, s.*, date(s.approval_2_date) as approved_date, d.dept_code, d.dept_name, ms.name as supplier_name from purchase_order s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.id = ".$json['id']);
			}
            if(!$data[0])
            	return $this->respond(['status'   => false, 'data'    => $data[1]]);
			return $this->respond(['status'   => true, 'data'    => $data[1], 'po'    => $po[1]]);
        } catch (Exception $e) {
            return $this->respond(['status'   => false, 'data'    => $e]);
        }
    }

    public function fakepo(){
        try {
            $model = new DataModel();
			if($this->request->getMethod() == 'post'){
                $json = $this->request->getJSON();
				$data = $model->data($json->q);
            }
			else{
				$json = $_REQUEST;
				//$data = json_decode(file_get_contents("php://input"));
				$data = $model->data("SELECT p.ref_offer_no, u.name as dir, ms.pic_name, ms.id as supplier_id, p.currency, p.note as keterangan, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (
						
					select s.id, coalesce(s.order_no, s.id) as order_no, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, 
					i.unit, i.original_manufacture, i.manufacture_pn, i.article_no, sum(s.order_qty) as order_qty, 
					s.price_per_item 
					from fake_purchase_order_item s left join m_item i on i.id = s.item_id
					where i.id is not null and s.flag = 1
					group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, s.purchase_order_id, s.price_per_item
				)s 
				left join m_item i on i.id = s.item_id
				left join fake_purchase_order p on p.id = s.purchase_order_id
				left join m_supplier ms on ms.id = p.supplier_id
				left join m_department d on d.id = p.dept_id
				left join users u on u.id = d.approval_1
				where p.id = ".$json['id']." order by s.order_no");
				$po = $model->data("select coalesce((select sum(payment_pct) from invoice where po_id = s.id), 0) as total_payment_pct, ms.pic_name, ms.address, s.*, date(s.approval_2_date) as approved_date, d.dept_code, d.dept_name, ms.name as supplier_name from fake_purchase_order s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.id = ".$json['id']);
			}
            if(!$data[0])
            	return $this->respond(['status'   => false, 'data'    => $data[1]]);
			return $this->respond(['status'   => true, 'data'    => $data[1], 'po'    => $po[1]]);
        } catch (Exception $e) {
            return $this->respond(['status'   => false, 'data'    => $e]);
        }
    }

    public function postatus(){
        //return 'aaa'
        try {
            $model = new DataModel();
			if($this->request->getMethod() == 'post'){
                $json = $this->request->getJSON();
				$data = $model->data($json->q);
            }
			else{
				$json = $_REQUEST;
				//$data = json_decode(file_get_contents("php://input"));
				$data = $model->data("SELECT approved from purchase_order p 
				where p.id = ".$json['id']);
			}
            if(!$data[0])
            	return $this->respond(['status'   => false, 'data'    => $data[1]]);
			return $this->respond(['status'   => true, 'data'    => $data[1]]);
        } catch (Exception $e) {
            return $this->respond(['status'   => false, 'data'    => $e]);
        }
    }

	public function reportpo(){
		$json = $_REQUEST;
		//http://127.0.0.1:3030/api/report?type=pdf&file=po&filename=po&engine=easytemplate&po_id=1974&idx=123fwef235324
		$url = "http://127.0.0.1:3030/api/report?type=pdf&file=po&filename=".$json['filename']."&engine=easytemplate&po_id=".$json['id']."&fake=".($json['fake'] ?? 0)."&idx=".uniqid();
		$file = "./download/".$json['filename'].".pdf";
		//Get content as a string. You can get local content or download it from the web.
		/* $downloadedFile = file_get_contents($url);
		file_put_contents($file, $downloadedFile); */
		$ch = curl_init(); 
		//Save content from string to .html file.
		$fp = fopen($file, 'wb'); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  		//curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch); 
		// Closes a cURL session and frees all resources 
		curl_close($ch); 
		// Close file 
		fclose($fp); 
		
		if (file_exists($file)) {
			//header('Content-Description: File Transfer');
			header('Content-Type: application/pdf');
			header('Content-Disposition: inline; filename='.basename($file));
			//header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			exit;
		}
	}

    public function reportpo2(){
			
        helper(['Auth_helper']);
// 		if(!checkAuth())
// 			return $this->respond(['status' => false, 'message' => 'You area not allowed to view this page!'], 401);
			
		$json = $_REQUEST;
		$url = "http://127.0.0.1:3030/api/report?type=pdf&file=po2&filename=".$json['filename']."&engine=easytemplate&po_id=".$json['id']."&fake=".($json['fake'] ?? 0)."&idx=".uniqid();
		$file = "./download/".$json['filename'].".pdf";
		//Get content as a string. You can get local content or download it from the web.
		/* $downloadedFile = file_get_contents($url);
		file_put_contents($file, $downloadedFile); */
		$ch = curl_init(); 
		//Save content from string to .html file.
		$fp = fopen($file, 'wb'); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  		//curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch); 
		// Closes a cURL session and frees all resources 
		curl_close($ch); 
		// Close file 
		fclose($fp); 
		
		if (file_exists($file)) {
			//header('Content-Description: File Transfer');
			header('Content-Type: application/pdf');
			header('Content-Disposition: inline; filename='.basename($file));
			//header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			exit;
		}
	}

    public function reportpo3(){
		$json = $_REQUEST;
		$url = "http://127.0.0.1:3030/api/report?type=pdf&file=po3&filename=".$json['filename']."&engine=easytemplate&po_id=".$json['id']."&fake=".($json['fake'] ?? 0)."&idx=".uniqid();
		$file = "./download/".$json['filename'].".pdf";
		//Get content as a string. You can get local content or download it from the web.
		/* $downloadedFile = file_get_contents($url);
		file_put_contents($file, $downloadedFile); */
		$ch = curl_init(); 
		//Save content from string to .html file.
		$fp = fopen($file, 'wb'); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  		//curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch); 
		// Closes a cURL session and frees all resources 
		curl_close($ch); 
		// Close file 
		fclose($fp); 
		
		if (file_exists($file)) {
			//header('Content-Description: File Transfer');
			header('Content-Type: application/pdf');
			header('Content-Disposition: inline; filename='.basename($file));
			//header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			exit;
		}
	}

    public function reportponoprice(){
        
        // helper(['Auth_helper']);
// 		if(!checkAuth())
// 			return $this->respond(['status' => false, 'message' => 'You area not allowed to view this page!'], 401);
			
		$json = $_REQUEST;
		$url = "http://127.0.0.1:3030/api/report?noprice=true&type=pdf&file=po2&filename=".$json['filename']."&engine=easytemplate&po_id=".$json['id']."&fake=".($json['fake'] ?? 0)."&idx=".uniqid();
		$file = "./download/".$json['filename'].".pdf";
		//Get content as a string. You can get local content or download it from the web.
		/* $downloadedFile = file_get_contents($url);
		file_put_contents($file, $downloadedFile); */
		$ch = curl_init(); 
		//Save content from string to .html file.
		$fp = fopen($file, 'wb'); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  		//curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch); 
		// Closes a cURL session and frees all resources 
		curl_close($ch); 
		// Close file 
		fclose($fp); 
		
		if (file_exists($file)) {
			//header('Content-Description: File Transfer');
			header('Content-Type: application/pdf');
			header('Content-Disposition: inline; filename='.basename($file));
			//header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			exit;
		}
	}
 
}