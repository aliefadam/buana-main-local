<?php namespace App\Controllers\Report\SuratJalan;
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use App\Models\Suratjalan\SuratJalanModel;
use CodeIgniter\API\ResponseTrait;
use Google\Client;
use Google\Service\Drive;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;
define('STDIN',fopen("php://stdin","r"));

function DOMinnerHTML(\DOMNode $element) 
{ 
    $innerHTML = ""; 
    $children  = $element->childNodes;

    foreach ($children as $child) 
    { 
        $innerHTML .= $element->ownerDocument->saveHTML($child);
    }

    return $innerHTML; 
}
 
class Index extends Controller
{
    use ResponseTrait;

    public function index(){
        
		helper(['Auth_helper']);
		if(!checkAuth())
			return $this->respond(['status' => false, 'message' => 'You area not allowed to view this page!'], 401);
        
		$json = $_REQUEST;
		$total = 0;
		$debugs = array();

        $data = $this->data($json["sj_id"]);

		$tmp = [];
		$sj_no = "";
		$sj_date = "";
		$place = "";
		$up = "";
		$sj_notes = "";
		$jenis_kendaraan = "";
		$nopol = "";
		$nama = "";
		$no_hp = "";
		$jam_berangkat = "";
		$pengirim_name = "";
		$from_info="";
		$uniqid = uniqid();
		$last = false;
		$sjItemLast = json_decode(json_encode(["part_id"=> -1]), FALSE);
		$i = 0;
		$approved_date = null;
		$items0ok = false;
		$items1ok = false;
		$spz="";
		$trw="";
		$kdr="";

		//return $this->respondCreated($data["data"], 200); 
        if(count($data["data"])==0)
            return "Surat Jalan tidak memiliki items!";
		foreach ($data["data"] as $key => $value) {
			$value->last = true;
			$sj_no = $value->sj_no;
			$sj_date=$value->sj_date;
			setlocale(LC_ALL, 'id_ID');
			$sj_date = date('d F Y', strtotime($sj_date));
			
			$place = $value->place;
			$pengirim_name = $value->pengirim_name;
			$up = $value->up;
			$sj_notes = $value->sj_notes;
			$jenis_kendaraan = $value->jenis_kendaraan;
			$nopol =$value->nopol;
			$nama = $value->nama;
			$no_hp = $value->no_hp;
			$jam_berangkat = $value->jam_berangkat;
			$approved_date = $value->approved_date;
			
			$value->qty = (float) $value->qty;
			$value->notes = trim($value->notes);
			if($value->notes!="")
			$value->notes = $value->notes;
			$value->notes = preg_replace('/\n/i',"\n", $value->notes);
            $value->specification = "\n".$value->specification;
              $value->isnotimage=$value->attachment==null;
            $value->attachment = explode("+++", $value->attachment);
            // $value->attachment='https://internal.buanamultiteknik.com/api/test/thumbnail?url=./uploads/lp'.$value->part_no.'/'.$value->attachment[0];
            // $value->attachment='https://internal.buanamultiteknik.com/api/uploads/sj'.$value->sj_id.'/'.$value->attachment[0];
            $value->attachment='https://main.buanamultiteknik.com/api/uploads/sj'.$value->sj_id.'/'.$value->attachment[0];
            
            $from_info=$value->from_info;
            
            $spz=$value->from_info==1;
            $trw=$value->from_info==2;
            $kdr=$value->from_info==4;

			if($sjItemLast->part_id != $value->part_id){
				$sjItemLast->last = $value;
				if($last!=false && count($last->items)<2){
					for($f=count($last->items);$f<2;$f++){
						$last->items[] = ["qty"=>'-', "keterangan"=>'-'];
					}
				}
				if($last!=false && count($last->items) == 2){
					$last->desccolspan = 1;
					$last->ket0span = 1;
					foreach ($last->items as $key => $val) {
						$last->{"items".$key."qty"} = $val["qty"];
						$last->{"items".$key."keterangan"} = $val["keterangan"];
						$last->{"items".$key."ok"} = $val["keterangan"] != '-';
					}
					if($items0ok==false){
						$last->desccolspan++;
						$last->ket0span++;
					}
					if($items1ok==false){
						$last->desccolspan++;
						$last->ket0span++;
					}
				}
                $value->isjasa=$value->item_type=="Jasa";
				$value->last = true;
				$value->total_qty = $value->qty;
				$tmp[] = $value;
				$i++;
				$value->no = $i;
				$last = $value;
				$last->items = [];
				$last->items[] = ["qty"=>$value->qty, "keterangan"=>$value->notes];
			}else{
				$sjItemLast->last = false;
				$last->items[] = ["qty"=>$value->qty, "keterangan"=>$value->notes];
				/* $sjItemLast->rowspan++;
				$sjItemLast->total_qty+=$value->qty;
	
				$v = json_decode(json_encode([
					"part_id"=>$value->part_id,
					"last"=>true,
					"rowspan"=>0,
					"isrowspan"=>false,
					"qty"=> $value->qty,
                    "isjasa"=>$value->item_type == "Jasa",
					"description"=> $value->description,
				], true));
				$tmp[] = $v;
				if($last->part_id==$value->part_id)
					$last->last = false;
				$last = $v; */
			}
		}
		if($last!=false && count($last->items)<2){
			for($f=count($last->items);$f<2;$f++){
				$last->items[] = ["qty"=>'-', "keterangan"=>'-'];
			}
		}
		if($last!=false && count($last->items) == 2){
			$last->desccolspan = 1;
			$last->ket0span = 1;
			foreach ($last->items as $key => $val) {
				$last->{"items".$key."qty"} = $val["qty"];
				$last->{"items".$key."keterangan"} = $val["keterangan"];
				$last->{"items".$key."ok"} = $val["keterangan"] != '-';
			}
			if($last->items0ok==false){
				$last->desccolspan++;
			}
			if($last->items1ok==false){
				$last->desccolspan++;
				$last->ket0span++;
			}
		}

		$pengirim = $this->qrcode("https://main.buanamultiteknik.com/#/sa/info/"."sj_pengirim_".$json["sj_id"]);

		$templating = new \WMDE\VueJsTemplating\Templating;
		$vue = file_get_contents('./report/surat_jalan.vue');
		$dom = new \DOMDocument('1.0', 'utf-8');
		libxml_use_internal_errors(true);
		$dom->loadHTML($vue); 
		//echo $dom->getElementsByTagName('style')[0];
		$style = DOMinnerHTML($dom->getElementsByTagName('style')[0]);
		$date = '';
			
		$imgpath = '/home/main.buanamultiteknik.com/public_html/img/logo.png';
		$type = pathinfo($imgpath, PATHINFO_EXTENSION);
		$data = file_get_contents($imgpath);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

		$params = [
			"sj_no"=>$sj_no,
			"sj_date" =>$sj_date,
			"place"=>$place,
			"up"=>$up,
			"sj_notes" =>$sj_notes,
			"jenis_kendaraan" => $jenis_kendaraan,
			"nopol" =>$nopol,
			"nama" => $nama,
			"no_hp" =>$no_hp,
			"from_info"=>$from_info,
			"spz"=> $spz,
			"trw"=> $trw,
			"kdr"=> $kdr,
			"jam_berangkat" =>$jam_berangkat,
			"preview"=>$approved_date == null || $approved_date == null ? true : false,
			"isPengirimSetuju"=>$approved_date == null ? false : true,
			"pengirim"=>$approved_date == null ? "" : $pengirim,
			"pengirim_name"=>$pengirim_name,
			"date"=> date("Y-m-d"),
			"items"=>json_decode(json_encode($tmp), true)
		];

		if(isset($json["debug"])){
			$debugs[] = $params;
			return $this->respondCreated($debugs, 200);
		}
		//else{
		$tpl = $templating->render(DOMinnerHTML($dom->getElementsByTagName('template')[0]), $params);

		return $tpl."<style>$style</style>";
		// instantiate and use the dompdf class
		$dompdf = new \Dompdf\Dompdf();
		$dompdf->loadHtml($tpl."<style>$style</style>");

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');

		// Render the HTML as PDF
		$dompdf->render();
		$output = $dompdf->output();
		$path = "./download/sj".$uniqid.".pdf";
		file_put_contents($path, $output);
		
		//$mime = \get_mime_by_extension($path);
		$name = $sj_no." .pdf";
		//$pdf->merge('file', $path);
		// Output the generated PDF to Browser
		/* $dompdf->stream(); */
		header('Pragma: public');     // required
		header('Expires: 0');         // no cache
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($path)).' GMT');
		header('Cache-Control: private',false);
		header('Content-Type: application/pdf');  // Add the mime type from Code igniter.
		header('Content-Disposition: attachment; filename="'.basename($name).'"');  // Add the file name
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($path)); // provide file size
		header('Connection: close');
		readfile($path); // push it out

		if(file_exists($path)) // here is the problem
		{
			unlink($path);
		}
		exit();
		//}
    }

	public function info($info, $uid, $pk){
		$model = new SuratJalanModel();
		$data = $model->db->query("update info set info = '".$info."' where uid = '".$uid."'");
	}

    public function data($id){
        try {
            $session = session();
            $model = new SuratJalanModel();
            $data = $model->db->query("SELECT a.*, d.item_no, d.item_name, d.original_manufacture, d.manufacture_pn, d.specification, d.unit, d.item_type, sji.qty, sji.item_id as part_id, 
            sji.notes, sji.short_desc, p.name as pengirim_name, sji.attachment, sji.sj_id
			FROM surat_jalan a
			left join surat_jalan_item sji on sji.sj_id=a.id
			left join m_item d on d.id = sji.item_id
			left join users p on p.id = a.pengirim
			
			WHERE a.id = $id and sji.flag=1 and sji.reject=0 order by sji.id" );
			if(!$data)
				return ["status"=>false, "data"=>$model->db->error()];
			$data = $data->getResult();
			
			//$cd = [];
			//$z = 1;
			if(count($data) == 0){
				$response = [
					'status'  => true,
					'data'    => $data
				];
			}
			else{
				if(count($data) > 0){
				}
				else
					return $this->respond(['status'   => false]);
					//return $this->respond(["status"   => count($data)]);
				$max = count($data);
				
					
				$response = [
					'status'  => true,
					'data'    => $data
				];
			}
				
            return $response;
        } catch (Exception $e) {
            return ['status'   => false, 'data'    => $e];
        }
    }

	public function qrcode($url){
		$writer = new PngWriter();

		// Create QR code
		$result = Builder::create()
		->writer(new PngWriter())
		->writerOptions([])
		->data($url)
		->encoding(new Encoding('UTF-8'))
		->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
		->size(100)
		->margin(0)
		->roundBlockSizeMode(new RoundBlockSizeModeMargin())
		->validateResult(false)
		->build();
		return $result->getDataUri();
	}
 
}