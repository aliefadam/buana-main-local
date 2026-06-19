<?php namespace App\Controllers\Report\Pr;
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use App\Models\Bom\PrModel;
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
 
class Index2 extends Controller
{
    use ResponseTrait;

    private function normalizeOperationalPreviewLabels(string $html): string
    {
        $html = preg_replace('/(?<!SUB )TYPE SUB\s*:/', 'OPERATIONAL SUB TYPE:', $html);
        $html = preg_replace('/(?<!SUB )TYPE\s*:/', 'OPERATIONAL TYPE:', $html);

        return $html;
    }

    public function index(){
		$json = $_REQUEST;
		$total = 0;
		$debugs = array();

        $data = $this->data($json["pr_id"]);

		$tmp = [];
		$pr_no = "";
		$pr_date = "";
		$pr_subject = "";
		$pr_notes = "";
		$peminta = "";
		$penyetuju = "";
		$uniqid = uniqid();
		$last = false;
		$prItemLast = json_decode(json_encode(["part_id"=> -1]), FALSE);
		$i = 0;
		$peminta_setuju = null;
		$penyetuju_setuju = null;

		//return $this->respondCreated($data["data"], 200); 
        if(count($data["data"])==0)
            return "PR tidak memiliki parts!";
		foreach ($data["data"] as $key => $value) {
			$value->last = true;
			$pr_no = $value->pr_no;
			$pr_date = $value->pr_date;
			$pr_subject = $value->pr_subject;
			$pr_notes = $value->pr_notes;
			$peminta = $value->peminta;
			$penyetuju = $value->penyetuju;
			$peminta_setuju = $value->peminta_setuju;
			$penyetuju_setuju = $value->penyetuju_setuju;
			$value->stock = 0;
			/* $value->description = nl2br($value->description);
			$value->notes = nl2br($value->notes); */ 
			
			$value->qty = (float) $value->qty;
			$value->description = trim($value->description);
			if($value->description!="")
			$value->description = $value->description;
			$value->description = preg_replace('/\n/i',"\n", $value->description);
            $value->specification = "\n".$value->specification;
			
			//start of pdf
			if($data["data"][$key]->attachment!=""){
				try {
					$guesser = new RegexGuesser();

					$data["data"][$key]->pdfInfo = number_format($guesser->guess(trim($data["data"][$key]->attachment)));
					
					$oPDF = new \setasign\Fpdi\Fpdi();
					$iPageCount = $oPDF->setSourceFile(trim($data["data"][$key]->attachment));
					$mTemplateId = $oPDF->importPage($iPageCount);
					$r = $oPDF->getTemplateSize($mTemplateId);
					$data["data"][$key]->pdfInfo = $r["orientation"] == "L" ? "horizontal" : "vertical";
					//$data["data"][$key]->pdfInfo = $iPageCount;
				} catch (\Throwable $th) {
					$data["data"][$key]->pdfInfo = 'invalid';
				}
				
				
			}
			else{
				$data["data"][$key]->pdfInfo = '';
				
			}
			//end of pdf

			if($prItemLast->part_id != $value->part_id){
				$prItemLast = $value;
				$value->rowspan = 1;
				$value->isrowspan = true;
                $value->isjasa=$value->item_type=="Jasa";
                $value->isaset=$value->category=="Aset";
				$value->last = true;
				$value->total_qty = $value->qty;
				$tmp[] = $value;
				$i++;
				$value->no = $i;
				$last = $value;
			}else{
				$prItemLast->rowspan++;
				$prItemLast->total_qty+=$value->qty;
				$prItemLast->last = false;
				$v = json_decode(json_encode([
					"part_id"=>$value->part_id,
					"last"=>true,
					"rowspan"=>0,
					"isrowspan"=>false,
					"qty"=> $value->qty,
                    "isjasa"=>$value->item_type == "Jasa",
                    "isaset"=>$value->category=="Aset",
					"description"=> $value->description,
					"supplier"=> $value->supplier,
					"rfq_no"=> $value->rfq_no,
					"ragic_no"=> $value->ragic_no,
					"supplier"=> $value->supplier,
					"project_no"=> $value->project_no,
					"project_name"=> $value->project_name,
					"sort_id"=> $value->sort_id,
                    "order_no"=> $value->order_no,
					"allocation"=> $value->allocation,
					"quotation_no"=> $value->quotation_no,
					"quotation_date"=> $value->quotation_date,
					"notes"=> $value->notes
				], true));
				$tmp[] = $v;
				if($last->part_id==$value->part_id)
					$last->last = false;
				$last = $v;
			}
		}

		$mengetahui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."pr_peminta_".$json["pr_id"]);
		$menyetujui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."pr_penyetuju_".$json["pr_id"]);

		//return $this->respond(["a"=>$tmp]);

		$templating = new \WMDE\VueJsTemplating\Templating;
		$vue = file_get_contents('./report/pr2.vue');
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
			"pr_no"=>$pr_no,
			"pr_date"=>$pr_date,
			"pr_subject"=>$pr_subject,
			"pr_notes"=>$pr_notes,
			"isPemintaSetuju"=>$peminta_setuju == null ? false : true,
			"isPenyetujuSetuju"=>$penyetuju_setuju == null ? false : true,
			"preview"=>$penyetuju_setuju == null || $peminta_setuju == null ? true : false,
			"penyetuju"=>$penyetuju,
			"peminta"=>$peminta,
			"penyetuju"=>$penyetuju,
			"mengetahui"=>$mengetahui,
			"menyetujui"=>$penyetuju_setuju == null ? "" : $menyetujui,
			"isBuana"=>$penyetuju == "Buana Susilo" ? true : false,
			"title"=>$title ?? 'Bpk. ',
			"date"=> date("Y-m-d"),
			"items"=>json_decode(json_encode($tmp), true)
		];

		if(isset($json["debug"])){
			$debugs[] = $params;
			return $this->respondCreated($debugs, 200);
		}
		//else{
		$tpl = $templating->render(DOMinnerHTML($dom->getElementsByTagName('template')[0]), $params);
		$tpl = $this->normalizeOperationalPreviewLabels($tpl);

		return $tpl."<style>$style</style><script type='module' src='../../../library/pdf.mjs'></script><script type='module' src='../../../library/pdfviewer.mjs' onload='renderPDF()'></script>
		<script>
		function makeThumb(page) {
					// draw page to fit into 96x96 canvas
					var vp = page.getViewport({ scale: 1, });
					var canvas = document.createElement('canvas');
					var scalesize = 1;
					canvas.width = vp.width * scalesize;
					canvas.height = vp.height * scalesize;
					var scale = Math.min(canvas.width / vp.width, canvas.height / vp.height);
					console.log(vp.width, vp.height, scale);
					return page.render({ canvasContext: canvas.getContext('2d'), viewport: page.getViewport({ scale: scale }) }).promise.then(function () {
						return canvas; 
					});
				}
			function renderPDF() {
			var temp = 'https://raw.githubusercontent.com/mozilla/pdf.js/ba2edeae/web/compressed.tracemonkey-pldi-09.pdf';
				var table = `<table style='border: 0; border-collapse: collapse;'>`
							pdfjsLib.getDocument(temp).promise.then(async function (doc) {
								var pages = []; while (pages.length < doc._pdfInfo.numPages) pages.push(pages.length + 1);
								var images = []
								await Promise.all(pages.map(function (num, i) {
									// create a div for each page and build a small canvas for it
									var div = document.createElement('div');
									return doc.getPage(num).then(makeThumb)
									.then(function (canvas) {
										var img = new Image();
										//console.log('asd')
										//images[i] = `<tr><td>3434235<img style='width:`+canvas.width+`px; height:`+canvas.height+`px' src='`+canvas.toDataURL()+`'></img></td></tr>`
										img.src = canvas.toDataURL();
										document.body.appendChild(img)
										//div.appendChild(img);
										//console.log('aaa')
										return true
									});
								}));
								//self.reportImages = images
								//console.log(images)
								table += images.join('')+'</table>'
								//console.log(table)
								//var doc = new DOMParser().parseFromString(table, 'text/xml');
								//document.body.appendChild(doc.children[0])
							}).catch(console.error);
				}
			
			</script>
		";
		// instantiate and use the dompdf class
		$dompdf = new \Dompdf\Dompdf();
		$dompdf->loadHtml($tpl."<style>$style</style>");

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');

		// Render the HTML as PDF
		$dompdf->render();
		$output = $dompdf->output();
		$path = "./download/pr".$uniqid.".pdf";
		$path2 = "./download/prall".$uniqid.".pdf";
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
		$name = "PR ".$pr_no." .pdf";
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
		//}
    }

	public function info($info, $uid, $pk){
		$model = new PrModel();
		$data = $model->db->query("update info set info = '".$info."' where uid = '".$uid."'");
	}

    public function data($id){
        try {
            $session = session();
            $model = new PrModel();
            $data = $model->db->query("SELECT a.*, d.item_no, b.id as part_id, b.notes, c.qty, c.description, d.item_name, d.original_manufacture, d.manufacture_pn, d.specification, d.unit, d.item_type,
			f.name as peminta, g.name as penyetuju, e.name as supplier, q.rfq_no, b.id as sort_id, mp.category, mp.project_no, mp.project_name,
			(select distinct r.order_id from ragic_data r where r.ragic_id = a.ragic_id limit 1) as ragic_no, c.allocation, c.id as subledger_id, b.quotation_date, b.quotation_no, b.order_no as order_no, coalesce(a.attachment, '') as attachment
			FROM pr a
			inner join pr_part b on b.pr_id = a.id
			inner join pr_subledger c on c.pr_part_id = b.id
			left join m_project mp on mp.id = c.project_id
			left join m_item d on d.id = b.item_id
			left join m_supplier e on e.id = b.supplier_id
			left join users f on f.id = a.pr_peminta
			left join users g on g.id = a.pr_menyetujui
			left join rfq_header q on q.id = a.rfq_id
			
			WHERE a.id = $id order by b.order_no, c.id");
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
				foreach ($data as $key => $value) {
					if(!isset($cd[$value->part_id]))
						$cd[$value->part_id] = $value->order_no;
					
					$value->order_no = $cd[$value->part_id];
					if($value->allocation!="NI")
						$value->order_no = number_format($value->part_id.'.5005'.$value->subledger_id, 20);
					else
						$value->order_no = number_format($value->part_id.'.0001'.$value->subledger_id, 20);
					
					//$z++;
				}
				
				usort($data, function($a, $b) {
				// 	//return strcmp($a->sort_id, $b->sort_id);
					return (((float)($a->sort_id.'.'.$a->order_no) + (float)("0.".str_pad($a->subledger_id, 5, '0', STR_PAD_LEFT)) < (float)($b->sort_id.'.'.$b->order_no) + (float)("0.".str_pad($b->subledger_id, 5, '0', STR_PAD_LEFT)) ) ? -1 : 1);
				});
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

	public function test(){
		
		$oPDF = new \setasign\Fpdi\Fpdi();
        $iPageCount = $oPDF->setSourceFile('../test/55Dinamika.pdf');
        $mTemplateId = $oPDF->importPage($iPageCount);
        $aTemplateSize = $oPDF->getTemplateSize($mTemplateId);
		var_dump([$aTemplateSize["orientation"]]);

	}
 
}
