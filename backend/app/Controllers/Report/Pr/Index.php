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
 
class Index extends Controller
{
    use ResponseTrait;

    private function normalizeOperationalPreviewLabels(string $html): string
    {
        $html = preg_replace('/(?<!SUB )TYPE SUB\s*:/', 'OPERATIONAL SUB TYPE:', $html);
        $html = preg_replace('/(?<!SUB )TYPE\s*:/', 'OPERATIONAL TYPE:', $html);

        return $html;
    }

    public function index(){
        
        
        // return 'test';
// 		helper(['Auth_helper']);
// 		if(!checkAuth())
// 			return $this->respond(['status' => false, 'message' => 'You area not allowed to view this page!'], 401);
        // var_dump($value);

		$json = $_REQUEST;
		$total = 0;
		$debugs = array();

        $data = $this->data($json["pr_id"]);
        $dataPerubahan = $this->dataPerubahan($json["pr_id"]);
        $dataPerubahanStatus = $this->dataPerubahanStatus($json["pr_id"]);
		if(isset($json['perubahan_status'])){
			return $this->respondCreated($dataPerubahanStatus, 200);
		}
		
		if(isset($json['perubahan'])){
			return $this->respondCreated($dataPerubahan, 200);
		}

		$tmp = [];
		$pr_no = "";
		$attachment = "";
		$pr_date = "";
		$pr_subject = "";
		$pr_notes = "";
		$peminta = "";
		$penyetuju = "";
        $status="";
		$uniqid = uniqid();
		$last = false;
		$prItemLast = json_decode(json_encode(["part_id"=> -1]), FALSE);
		$i = 0;
		$peminta_setuju = null;
		$penyetuju_setuju = null;
		$canceled_date = null;
		$canceled_2_date= null;

		//return $this->respondCreated($data["data"], 200); 
        if(count($data["data"])==0)
            return "PR tidak memiliki parts!";
		$notDeleted = [];

        //special case
        if ($json["pr_id"] == 1652) {
            $data["data"] = array_filter($data["data"], function($item) {
                return $item->item_no != 19052;
            });
            $data["data"] = array_values($data["data"]);
        }
        
        // return $data;

		//return $this->respondCreated([$data, $dataPerubahan], 200);

		foreach ($data["data"] as $key => $value) {
		  //  var_dump($value);
			$value->is_operational = false;
			if($value->pr_part_flag == 1  || in_array($value->part_id, $dataPerubahanStatus["data"])){
				$value->last = true;
				$value->cls = "";
				$pr_no = $value->pr_no;
				$pr_date = $value->pr_date;
				$test_var = "tes";
				$pr_subject = $value->pr_subject;
				$pr_notes = $value->pr_notes;
				$peminta = $value->peminta;
				$isAn = $value->isAn;
				$attachment = $value->attachment;
				$penyetuju = $value->penyetuju;
				$peminta_setuju = $value->peminta_setuju;
				$penyetuju_setuju = $value->penyetuju_setuju;
				$canceled_date = $value->canceled_date;
				$canceled_2_date=$value->canceled_2_date;
				$status=$value->status;
				$value->stock = 0;
				$value->is_project = $value->project_type == 'Project' || is_null($value->project_type) || $value->project_type == '';
				$value->is_persediaan = $value->project_type == 'Persediaan';
				$value->is_asset = $value->project_type == 'Asset';
				$value->is_operational = $value->project_type == 'Operational';
				$value->is_rnd = $value->project_type == 'R&D';
				$value->type_subledger = $value->project_type;
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
				    //var_dump('b');
					$value->rowspan = 1;
					$value->isrowspan = true;
					$value->isjasa=$value->item_type=="Jasa";
					$value->isaset=$value->category=="Aset";
					$value->isnew=$value->status_item=="New";
					$value->last = true;
					$value->is_project = $value->project_type == 'Project' || is_null($value->project_type) || $value->project_type == '';
					$value->is_persediaan = $value->project_type == 'Persediaan';
    				$value->is_asset = $value->project_type == 'Asset';
    				$value->is_operational = $value->project_type == 'Operational';
    				$value->is_rnd = $value->project_type == 'R&D';
					$value->type_subledger = $value->project_type;
					$value->total_qty = $value->qty;
					$value->ordering_number = $value->ordering_number;
					$value->haveStatus = false;
					$i++;
					$value->no = $i;
					$notDeleted[] = $value->part_id;
					// if(isset($json["debug2"]) || isset($json["debug"] )){
						//$value->aa = isset($dataPerubahan["data"][$value->part_id]);
						if(isset($dataPerubahan["data"][$value->part_id]) && $value->rev == 1){
							// if(count($dataPerubahan["data"])!=0){
							
							//$i++;
							$rev = false;
							foreach ($dataPerubahan["data"][$value->part_id] as $keyP => $valueP) {
								if(!isset($valueP->used)){
									$tmp[] = $valueP;
									$valueP->no = $i;
									$valueP->used = true;
									$valueP->cls = "no-bottom";
									$rev = true;
								}
							}
							if($rev==true){
								$value->status = "DIREVISI MENJADI";
								$value->haveStatus = true;
								$value->no = '';
								$value->cls = "no-top";
							}
						}
                        
					if($value->pr_part_flag == 0){
						$value->haveStatus = true;
						$value->status = "DIBATALKAN";
					}
					// }
					//if($value->rev == null || $value->part_rev < 1  || $value->haveStatus){
					if(!in_array($value->part_id, $dataPerubahanStatus["data"]) || $value->haveStatus){
						$tmp[] = $value;
						$prItemLast = $value;
					}
					else{
						$i--;
					}
					$last = $value;
				}else{
					//var_dump('a');
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
						"isnew"=>$value->status_item=="New",
						"description"=> $value->description,
						"supplier"=> $value->supplier,
						"rfq_no"=> $value->rfq_no,
						"ragic_no"=> $value->ragic_no,
						"supplier"=> $value->supplier,
						"project_no"=> $value->project_no,
						"is_project"=> $value->project_type == 'Project' || is_null($value->project_type) || $value->project_type == '',
						"is_persediaan"=> $value->project_type == 'Persediaan' || is_null($value->project_type) || $value->project_type == '',
						"is_asset"=> $value->project_type == 'Asset' || is_null($value->project_type) || $value->project_type == '',
						"is_operational"=> $value->project_type == 'Operational' || is_null($value->project_type) || $value->project_type == '',
						"is_rnd"=> $value->project_type == 'R&D' || is_null($value->project_type) || $value->project_type == '',
						"type_subledger"=>$value->project_type,
						"type_operationals"=> $value->type_operationals,
						"type_sub_operationals"=> $value->type_sub_operationals,
						"dept_name"=> $value->dept_name,
						"project_name"=> $value->project_name,
						"sort_id"=> $value->sort_id,
						"order_no"=> $value->order_no,
						"ordering_number"=> $value->ordering_number,
						"allocation"=> $value->allocation,
						"quotation_no"=> $value->quotation_no,
						"quotation_date"=> $value->quotation_date,
						"notes"=> $value->notes,
						"haveStatus"=> false,
						"requirement" => $value->requirement,
						
						
						"cls"=> '',
					], true));
					/* if(isset($json["debug2"]) || isset($json["debug"] )){
						//$value->aa = isset($dataPerubahan["data"][$value->part_id]);
						if(isset($dataPerubahan["data"][$value->part_id])){
							foreach ($dataPerubahan["data"][$value->part_id] as $keyP => $valueP) {
								$tmp[] = $valueP;
								$valueP->no = $i;
								$i++;
							}
							//$tmp[] = $dataPerubahan["data"][$value->part_id];
							$v->status = "Berubah menjadi";
							$v->haveStatus = true;
							//$dataPerubahan["data"][$value->part_id]->no = $i;
							//$i++;
						}
					} */
					$tmp[] = $v;
					if($last->part_id==$value->part_id)
						$last->last = false;
					$last = $v;
				}
			}
		}
		
// 		return var_dump($last);

		$deleted = [];
		foreach ($dataPerubahan["data"] as $key => $value) {
			if (!in_array($key, $notDeleted)) {
				$deleted[] = $value;
			}
		}

		$mengetahui = $this->qrcode("https://main.buanamultiteknik.com/#/sa/info/"."pr_peminta_".$json["pr_id"]);
		$menyetujui = $this->qrcode("https://main.buanamultiteknik.com/#/sa/info/"."pr_penyetuju_".$json["pr_id"]);
		
// 		$mengetahui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."pr_peminta_".$json["pr_id"]);
// 		$menyetujui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."pr_penyetuju_".$json["pr_id"]);

		//return $this->respond(["a"=>$tmp]);

		$templating = new \WMDE\VueJsTemplating\Templating;
		// if(isset($json["debug2"])){
			$vue = file_get_contents('./report/pr3.vue');
		// }
		// else
		// 	$vue = file_get_contents('./report/pr.vue');
		
// 		return var_dump($vue);
		$dom = new \DOMDocument('1.0', 'utf-8');
		libxml_use_internal_errors(true);
		$dom->loadHTML($vue); 
		//echo $dom->getElementsByTagName('style')[0];
		$style = DOMinnerHTML($dom->getElementsByTagName('style')[0]);
		$date = '';
			
// 		$imgpath = '/home/buanamultiteknik/internal.buanamultiteknik.com/img/logo.png';
		$imgpath = '/home/main.buanamultiteknik.com/public_html/img/logo.png';
		$type = pathinfo($imgpath, PATHINFO_EXTENSION);
		$data = file_get_contents($imgpath);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);


		//return $this->respondCreated($tmp, 200);

		//special
		foreach ($tmp as $key => $value) {
			//return print_r($value);
			if(!isset($value->is_operational))
				$value->is_operational = false;
			if(!isset($value->is_persediaan))
				$value->is_persediaan = false;
			if(!isset($value->is_asset))
				$value->is_asset = false;
			if(!isset($value->is_rnd))
				$value->is_rnd = false;
			if(isset($value->id) && $value->id == 1777 && isset($value->penyetuju_setuju) && $value->penyetuju_setuju == "2025-09-12 02:57:41"){
				$value->rowspan = 1;
			}
		}
		
// 		return print_r(json_decode(json_encode($tmp), true));
 
		$params = [
			"logo"=>$base64,
			"pr_no"=>$pr_no,
			"pr_date"=>$pr_date,
			"pr_subject"=>$pr_subject,
			"pr_notes"=>$pr_notes,
            "isAn"=>$isAn==0 ? false: true,
			"isPemintaSetuju"=>$peminta_setuju == null ||  $status < 0 ? false : false,
			"isPenyetujuSetuju"=>$penyetuju_setuju == null  ||  $status < 0 ? false : false,
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
			//$debugs[] = $dataPerubahan;
			$debugs[] = $tmp;
			//$debugs[] = $notDeleted;
			//$debugs[] = $deleted;
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
					var scalesize = 2;
					canvas.width = vp.width * scalesize;
					canvas.height = vp.height * scalesize;
					var scale = Math.min(canvas.width / vp.width, canvas.height / vp.height);
					//console.log(vp.width, vp.height, scale);
					return page.render({ canvasContext: canvas.getContext('2d'), viewport: page.getViewport({ scale: scale }) }).promise.then(function () {
						return canvas; 
					});
				}
			function renderPDF() {
			var temp = '".$attachment."';//'https://raw.githubusercontent.com/mozilla/pdf.js/ba2edeae/web/compressed.tracemonkey-pldi-09.pdf';
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
										img.src = canvas.toDataURL();
										if(canvas.width < canvas.height)
											img.classList.add('pdf-p');//img.style = 'display: block; height: 100%; max-width: 100%; object-fit: cover;'
										else
											img.classList.add('pdf-l');//img.style = 'display: block; max-height: 100%; width: 100%; object-fit: cover;'
										images[i] = img
									});
								}));
								images.map(img=>{
									document.body.appendChild(img)
								})
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
            $data = $model->db->query("SELECT a.*, c.requirement, b.rev as part_rev, b.flag as pr_part_flag, d.item_no, b.id as part_id, b.notes, c.qty, c.description, d.item_name, d.original_manufacture, d.manufacture_pn, d.specification, d.unit, d.item_type, d.article_no,
			f.name as peminta, g.name as penyetuju, e.name as supplier, q.rfq_no, b.id as sort_id, mp.category, mp.project_no, mp.project_name, b.rev, t.name as type_operationals, ts.name as type_sub_operationals, coalesce(project_type, 'Project') as project_type, md.dept_name,
			(select distinct r.order_id from ragic_data r where r.ragic_id = a.ragic_id limit 1) as ragic_no, c.allocation, c.id as subledger_id, b.quotation_date, b.quotation_no, b.order_no as order_no,b.order_no as ordering_number, coalesce(a.attachment, '') as attachment, case when b.created_date>=a.rev_date then 'New' else 'Old'  end as status_item
			FROM pr a
			inner join pr_part b on b.pr_id = a.id
			inner join pr_subledger c on c.pr_part_id = b.id
			left join m_project mp on mp.id = c.project_id
			left join m_item d on d.id = b.item_id
			left join m_supplier e on e.id = b.supplier_id
			left join users f on f.id = a.pr_peminta
			left join users g on g.id = a.pr_menyetujui
			left join rfq_header q on q.id = a.rfq_id
			left join m_department md on md.id = c.dept_id
			left join type_operationals t on t.id = c.type_operational_id
			left join type_sub_operationals ts on ts.id = c.sub_type_operational_id
			
			WHERE a.id = $id and c.flag=1 order by b.order_no, c.pr_part_id, c.id");
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
				
				// usort($data, function($a, $b) {
				// // 	//return strcmp($a->sort_id, $b->sort_id);
				// 	return (((float)($a->sort_id.'.'.$a->order_no) + (float)("0.".str_pad($a->subledger_id, 5, '0', STR_PAD_LEFT)) < (float)($b->sort_id.'.'.$b->order_no) + (float)("0.".str_pad($b->subledger_id, 5, '0', STR_PAD_LEFT)) ) ? -1 : 1);
				// });
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
	
	/* 

    public function data($id){
        try {
            $session = session();
            $model = new PrModel();
            $data = $model->db->query("SELECT a.*, d.item_no, b.id as part_id, b.notes, c.qty, c.description, d.item_name, d.original_manufacture, d.manufacture_pn, d.specification, d.unit, d.item_type, d.article_no,
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
    } */

    public function dataPerubahan($id){
        try {
            $session = session();
            $model = new PrModel();
			//, c.idx
            $data = $model->db->query("select * from (SELECT row_number() over(PARTITION by b.id, c.id ORDER by b.idx desc) as rn, row_number() over(PARTITION by b.id ORDER by b.idx desc) as rnrev, a.*, d.item_no, b.id as part_id, b.notes, c.qty, c.description, d.item_name, d.original_manufacture, d.manufacture_pn, d.specification, d.unit, d.item_type as mpitemtype, d.article_no,
			f.name as peminta, g.name as penyetuju, e.name as supplier, q.rfq_no, b.id as sort_id, mp.category as mpcategory, mp.project_no as mpproject_no, mp.project_name as mpproject_name, t.name as type_operationals, ts.name as type_sub_operationals, coalesce(project_type, 'Project') as project_type, md.dept_name,
			(select distinct r.order_id from ragic_data r where r.ragic_id = a.ragic_id limit 1) as ragic_no, c.allocation as callocation, c.id as subledger_id, b.quotation_date, b.quotation_no, b.order_no as order_no,b.order_no as ordering_number, coalesce(a.attachment, '') as realattachment, c.id as cid,  case when pp.created_date>=a.rev_date then 'New' else 'Old'  end as status_item,
			c.pr_part_id as cpr_part_id
			FROM pr_history a
			inner join pr_part_history b on b.pr_id = a.idx
			inner join pr_subledger_history c on c.pr_part_id = b.idx
			inner join pr_part pp on pp.id=b.id and pp.rev=1
			left join m_project mp on mp.id = c.project_id
			left join m_item d on d.id = b.item_id
			left join m_supplier e on e.id = b.supplier_id
			left join users f on f.id = a.pr_peminta
			left join users g on g.id = a.pr_menyetujui
			left join rfq_header q on q.id = a.rfq_id
			left join m_department md on md.id = c.dept_id
			left join type_operationals t on t.id = c.type_operational_id
			left join type_sub_operationals ts on ts.id = c.sub_type_operational_id

			
			WHERE a.id = $id and a.idx in (
					select idx from (select row_number() over(partition by id order by idx desc) as rn, idx from pr_history where id = $id)s where s.rn = 1
				) 
			)s where rn = 1 order by order_no, cpr_part_id, cid;");
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
					if($value->callocation!="NI")
						$value->order_no = number_format($value->part_id.'.5005'.$value->subledger_id, 20);
					else
						$value->order_no = number_format($value->part_id.'.0001'.$value->subledger_id, 20);
					
					//$z++;
				}
				
				// usort($data, function($a, $b) {
				// // 	//return strcmp($a->sort_id, $b->sort_id);
				// 	return (((float)($a->sort_id.'.'.$a->order_no) + (float)("0.".str_pad($a->subledger_id, 5, '0', STR_PAD_LEFT)) < (float)($b->sort_id.'.'.$b->order_no) + (float)("0.".str_pad($b->subledger_id, 5, '0', STR_PAD_LEFT)) ) ? -1 : 1);
				// });
				if(count($data) > 0){
				}
				else
					return $this->respond(['status'   => false]);
					//return $this->respond(["status"   => count($data)]);
				$max = count($data);
			
			$tmp = [];
			$pr_no = "";
			$attachment = "";
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
		
				
			foreach ($data as $key => $value) {
				$value->last = true;
				$pr_no = $value->pr_no;
				$pr_date = $value->pr_date;
				$pr_subject = $value->pr_subject;
				$pr_notes = $value->pr_notes;
				$peminta = $value->peminta;
				$isAn = $value->isAn;
				$attachment = $value->attachment;
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
				/* if($data["data"][$key]->attachment!=""){
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
					
				} */
				$data[$key]->pdfInfo = '';
				//end of pdf

				if($prItemLast->part_id != $value->part_id){
					$prItemLast = $value;
					$value->rowspan = 1;
					$value->isrowspan = true;
					$value->isjasa=$value->mpitemtype=="Jasa";
					
					$value->is_project= $value->project_type == 'Project' || is_null($value->project_type) || $value->project_type == '' ;
					$value->isaset=$value->mpcategory=="Aset";
                    $value->isnew=$value->status_item=="New";
                    $value->project_name=$value->mpproject_name;
					$value->last = true;
					$value->total_qty = $value->qty;
					$value->haveStatus = true;
					$value->status = "SEBELUMNYA";
					
					if($value->rn==1){
						if(!isset($tmp[$value->part_id]))
							$tmp[$value->part_id] = [];
						$tmp[$value->part_id][] = $value;
					}
					//$tmp[] = $value;
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
						"isjasa"=>$value->mpitemtype == "Jasa",
						"isaset"=>$value->mpcategory=="Aset",
                        "isnew"=>$value->status_item=="New",
						"description"=> $value->description,
						"supplier"=> $value->supplier,
						"rfq_no"=> $value->rfq_no,
						"ragic_no"=> $value->ragic_no,
						"supplier"=> $value->supplier,
						"project_no"=> $value->mpproject_no,
						"is_project"=> $value->project_type == 'Project' || is_null($value->project_type) || $value->project_type == '',
						"type_operationals"=> $value->type_operationals,
						"type_sub_operationals"=> $value->type_sub_operationals,
						"dept_name"=> $value->dept_name,
						"project_name"=> $value->mpproject_name,
						"sort_id"=> $value->sort_id,
						"order_no"=> $value->order_no,
						"ordering_number"=> $value->ordering_number,
						"allocation"=> $value->callocation,
						"quotation_no"=> $value->quotation_no,
						"quotation_date"=> $value->quotation_date,
						"notes"=> $value->notes,
						"haveStatus"=>false
					], true));
					if($value->rn==1){
						if(!isset($tmp[$value->part_id]))
							$tmp[$value->part_id] = [];
						$tmp[$value->part_id][] = $v;
					}
					if($last->part_id==$value->part_id)
						$last->last = false;
					$last = $v;
				}
			}	

				$response = [
					'status'  => true,
					'data'    => $tmp
				];
			}
				
            return $response;
        } catch (Exception $e) {
            return ['status'   => false, 'data'    => $e];
        }
    }

	public function dataPerubahanStatus($id){
        try {
            $session = session();
            $model = new PrModel();
			//, c.idx
            $data = $model->db->query("select * from (SELECT row_number() over(PARTITION by b.id, c.id ORDER by b.idx desc) as rn, row_number() over(PARTITION by b.id ORDER by b.idx desc) as rnrev, a.*, d.item_no, b.id as part_id, b.notes, c.qty, c.description, d.item_name, d.original_manufacture, d.manufacture_pn, d.specification, d.unit, d.item_type as mpitemtype, d.article_no,
			f.name as peminta, g.name as penyetuju, e.name as supplier, q.rfq_no, b.id as sort_id, mp.category as mpcategory, mp.project_no as mpproject_no, mp.project_name as mpproject_name, t.name as type_operationals, ts.name as type_sub_operationals, coalesce(project_type, 'Project') as project_type, md.dept_name,
			(select distinct r.order_id from ragic_data r where r.ragic_id = a.ragic_id limit 1) as ragic_no, c.allocation as callocation, c.id as subledger_id, b.quotation_date, b.quotation_no, b.order_no as order_no,b.order_no as ordering_number, coalesce(a.attachment, '') as realattachment, c.id as cid,  case when pp.created_date>=a.rev_date then 'New' else 'Old'  end as status_item,
			c.pr_part_id as cpr_part_id, b.flag as pr_part_flag
			FROM pr_history a
			inner join pr_part_history b on b.pr_id = a.idx
			inner join pr_subledger_history c on c.pr_part_id = b.idx
			inner join pr_part pp on pp.id=b.id
			left join m_project mp on mp.id = c.project_id
			left join m_item d on d.id = b.item_id
			left join m_supplier e on e.id = b.supplier_id
			left join users f on f.id = a.pr_peminta
			left join users g on g.id = a.pr_menyetujui
			left join rfq_header q on q.id = a.rfq_id
			left join m_department md on md.id = c.dept_id
			left join type_operationals t on t.id = c.type_operational_id
			left join type_sub_operationals ts on ts.id = c.sub_type_operational_id

			
			WHERE a.id = $id)s where rnrev = 1 order by order_no, cpr_part_id, cid;");
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
					if($value->callocation!="NI")
						$value->order_no = number_format($value->part_id.'.5005'.$value->subledger_id, 20);
					else
						$value->order_no = number_format($value->part_id.'.0001'.$value->subledger_id, 20);
					
					//$z++;
				}
				
				// usort($data, function($a, $b) {
				// // 	//return strcmp($a->sort_id, $b->sort_id);
				// 	return (((float)($a->sort_id.'.'.$a->order_no) + (float)("0.".str_pad($a->subledger_id, 5, '0', STR_PAD_LEFT)) < (float)($b->sort_id.'.'.$b->order_no) + (float)("0.".str_pad($b->subledger_id, 5, '0', STR_PAD_LEFT)) ) ? -1 : 1);
				// });
				if(count($data) > 0){
				}
				else
					return $this->respond(['status'   => false]);
					//return $this->respond(["status"   => count($data)]);
				$max = count($data);
			
			$tmp = [];
			$pr_no = "";
			$attachment = "";
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
			$notDeletedInHistory = [];
				
			foreach ($data as $key => $value) {
				if($value->pr_part_flag == 1)
					$notDeletedInHistory[] = $value->part_id;
				$value->last = true;
				$pr_no = $value->pr_no;
				$pr_date = $value->pr_date;
				$pr_subject = $value->pr_subject;
				$pr_notes = $value->pr_notes;
				$peminta = $value->peminta;
				$isAn = $value->isAn;
				$attachment = $value->attachment;
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
				/* if($data["data"][$key]->attachment!=""){
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
					
				} */
				$data[$key]->pdfInfo = '';
				//end of pdf

				if($prItemLast->part_id != $value->part_id){
					$prItemLast = $value;
					$value->rowspan = 1;
					$value->isrowspan = true;
					$value->isjasa=$value->mpitemtype=="Jasa";
					$value->isaset=$value->mpcategory=="Aset";
                    $value->isnew=$value->status_item=="New";
                    $value->project_name=$value->mpproject_name;
					$value->last = true;
					$value->total_qty = $value->qty;
					$value->haveStatus = true;
					$value->status = "SEBELUMNYA";
					
					if($value->rn==1){
						if(!isset($tmp[$value->part_id]))
							$tmp[$value->part_id] = [];
						$tmp[$value->part_id][] = $value;
					}
					//$tmp[] = $value;
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
						"isjasa"=>$value->mpitemtype == "Jasa",
						"isaset"=>$value->mpcategory=="Aset",
                        "isnew"=>$value->status_item=="New",
						"description"=> $value->description,
						"supplier"=> $value->supplier,
						"rfq_no"=> $value->rfq_no,
						"ragic_no"=> $value->ragic_no,
						"supplier"=> $value->supplier,
						"project_no"=> $value->mpproject_no,
						"is_project"=> $value->project_type == 'Project' || is_null($value->project_type) || $value->project_type == '',
						"type_operationals"=> $value->type_operationals,
						"type_sub_operationals"=> $value->type_sub_operationals,
						"dept_name"=> $value->dept_name,
						"project_name"=> $value->mpproject_name,
						"sort_id"=> $value->sort_id,
						"order_no"=> $value->order_no,
						"ordering_number"=> $value->ordering_number,
						"allocation"=> $value->callocation,
						"quotation_no"=> $value->quotation_no,
						"quotation_date"=> $value->quotation_date,
						"notes"=> $value->notes,
						// "status"=>"Dari",
						"haveStatus"=>false
					], true));
					if($value->rn==1){
						if(!isset($tmp[$value->part_id]))
							$tmp[$value->part_id] = [];
						$tmp[$value->part_id][] = $v;
					}
					if($last->part_id==$value->part_id)
						$last->last = false;
					$last = $v;
				}
			}	

				$response = [
					'status'  => true,
					'data'    => $notDeletedInHistory
				];
			}
				
            return $response;
        } catch (Exception $e) {
            return ['status'   => false, 'data'    => $e];
        }
    }

	/* public function qrcode($url){
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
	} */

	public function test(){
		
		$oPDF = new \setasign\Fpdi\Fpdi();
        $iPageCount = $oPDF->setSourceFile('../test/55Dinamika.pdf');
        $mTemplateId = $oPDF->importPage($iPageCount);
        $aTemplateSize = $oPDF->getTemplateSize($mTemplateId);
		var_dump([$aTemplateSize["orientation"]]);

	}
 
}
