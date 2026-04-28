<?php namespace App\Controllers\Report\Npb;
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use App\Models\Transaction\NpbModel;
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
		// helper(['Auth_helper']);
		// if(!checkAuth())
		// 	return $this->respond(['status' => false, 'message' => 'You area not allowed to view this page!'], 401);

		$json = $_REQUEST;
		$total = 0;
		$debugs = array();

        $data = $this->data($json["npb_id"]);

		$tmp = [];
		$npb_no = "";
		$npb_date = "";
		$notes = "";
		$allocation = "";
		$peminta = "";
		$kabag = "";
		$uniqid = uniqid();
		$last = false;
		$i = 0;
		$approved_date = null;
		$approved3_date = null;

		//return $this->respondCreated($data["data"], 200); 
        if(count($data["data"])==0)
            return "NPB tidak memiliki parts!";
		foreach ($data["data"] as $key => $value) {
			$value->last = true;
			$npb_no = $value->npb__no;
			$notes = $value->notes;
			$npb_date = $value->created_date;
			$peminta = $value->peminta_name;
			$kabag = $value->kabag_name;
			$peminta_setuju = $value->approved_date;
			$kabag_setuju = $value->approved3_date;
			$allocation=$value->project_name;


			$value->qty = (float) $value->qty;
			$i++;
			$value->no = $i;
			$tmp[] = $value;
			$last = $value;
			$last->items = [];

			$mengetahui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."npb_approved_".$json["npb_id"]);
			$menyetujui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."npb_approved3_".$json["npb_id"]);
		}
		$templating = new \WMDE\VueJsTemplating\Templating;
		$vue = file_get_contents('./report/npb.vue');
		$dom = new \DOMDocument('1.0', 'utf-8');
		libxml_use_internal_errors(true);
		$dom->loadHTML($vue); 
		//echo $dom->getElementsByTagName('style')[0];
		$style = DOMinnerHTML($dom->getElementsByTagName('style')[0]);
		$date = '';
			
		$imgpath = '/home/buanamultiteknik/internal.buanamultiteknik.com/img/logo.png';
		$type = pathinfo($imgpath, PATHINFO_EXTENSION);
		$data = file_get_contents($imgpath);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

		$params = [
			"logo"=>$base64,
			"npb_no"=>$npb_no,
			"npb_date"=>$npb_date,
			"notes"=>$notes,
			"isPemintaSetuju"=>$peminta_setuju == null ? false : true,
			"isKabagSetuju"=>$kabag_setuju == null ? false : true,
			"preview"=>$peminta_setuju == null || $kabag_setuju == null ? true : false,
			"kabag"=>$kabag,
			"peminta"=>$peminta,
			"mengetahui"=>$mengetahui,
			"menyetujui"=>$kabag_setuju == null ? "" : $menyetujui,
			"allocation"=>$allocation,
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
		$path = "./download/npb".$uniqid.".pdf";
		$path2 = "./download/npball".$uniqid.".pdf";
		file_put_contents($path, $output);

		//start of pdf
		$first = true;
			//return $this->respond($paths, 200);
		foreach ($data["data"] as $key => $value) {
			$tpdf = new \Jurosh\PDFMerge\PDFMerger;
			if(!$first){
				$tpdf->addPDF($path2, 'all');
				//unlink($last);
			}
			else{
				$tpdf->addPDF($path, 'all');
			}
			$oPDF = new \setasign\Fpdi\Fpdi();
			if($data["data"][$key]->pdfInfo!='' && $data["data"][$key]->pdfInfo!='invalid'){
				$iPageCount = $oPDF->setSourceFile($data["data"][$key]->attachment);
				$mTemplateId = $oPDF->importPage($iPageCount);
				$aTemplateSize = $oPDF->getTemplateSize($mTemplateId);
				$tpdf->addPDF($data["data"][$key]->attachment, 'all', $aTemplateSize["orientation"] == "L" ? "horizontal" : "vertical");
				$tpdf->merge('file', $path2);
				$first = false;
			}
		}
		//end of pdf

		//$mime = \get_mime_by_extension($path);
		$name = "NPB ".$npb_no." .pdf";
		//$pdf->merge('file', $path);
		// Output the generated PDF to Browser
		/* $dompdf->stream(); */
		header('Pragma: public');     // required
		header('Expires: 0');         // no cache
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Last-Modified: '.gmdate ('D, d M Y H:i:s', filemtime ($path2)).' GMT');
		header('Cache-Control: private',false);
		header('Content-Type: application/pdf');  // Add the mime type from Code igniter.
		header('Content-Disposition: attachment; filename="'.basename($name).'"');  // Add the file name
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($path2)); // provide file size
		header('Connection: close');
		readfile($path2); // push it out

		if(file_exists($path2)) // here is the problem
		{
			unlink($path2);
		}
		exit();
    }


    public function data($id){
        try {
            $session = session();
            $model = new NpbModel();
            $data = $model->db->query("SELECT npb.*, d.item_no, d.item_name, d.original_manufacture, d.manufacture_pn, d.specification, d.unit, d.item_type, d.article_no, npi.qty, npi.item_id as part_id, npi.allocation, npi.po_id,  p.name as peminta_name, k.name as kabag_name, npi.npb_id, npi.notes as npi_notes, pp.project_no as project_no, pp.project_name, po.po_no
			FROM npb 
			left join npb_item npi on npi.npb_id=npb.id
			left join m_item d on d.id = npi.item_id
			left join users p on p.id = npb.peminta_id
            left join users k on k.id=npb.kabag_id
			left join m_project pp on pp.id = npb.project_id
			Left join purchase_order po on po.id = npi.po_id
			WHERE npb.id = $id order by npb.id");
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