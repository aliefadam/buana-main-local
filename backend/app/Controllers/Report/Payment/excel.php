<?php namespace App\Controllers\Report\Payment;
ini_set('memory_limit', '100M');
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use App\Models\PaymentModel;
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
use Symfony\Component\Filesystem\Filesystem,
    Xthiago\PDFVersionConverter\Converter\GhostscriptConverterCommand,
    Xthiago\PDFVersionConverter\Converter\GhostscriptConverter;
use Xthiago\PDFVersionConverter\Guesser\RegexGuesser;
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
		$json = $_REQUEST;
		$total = 0;
		$debugs = array();
		$approvedStatus = 0;

        $data = $this->data($json["id"]);
        $detail = $this->detail($json["id"]);

		$tmp = [];

		$cur = [
			'IDR'=> 'Rupiah',
			'EUR'=> 'Euro',
			'USD'=> 'USD',
			'CNY'=> 'Chinese Yuan',

		];

		$c = [
			'IDR'=> 'Rp',
			'EUR'=> 'EUR',
			'USD'=> 'USD',
			'CNY'=> 'CNY'
		];
		$uniqid = uniqid();

		//qr code

		//'https://internal.buanamultiteknik.com/#/sa/info?id='+po_id

		$tmpPrice = [];

		$pb = $json["pagebreak"] ?? -1;
		$pettyCash = false;
		
		foreach ($detail["data"] as $key => $value) {
			if(!isset($tmp[$value->invoice_id])){
				$tmp[$value->invoice_id] = ["part"=>[], "data"=>[]];
			}
			if(!isset($tmpPrice[$value->item_id."-".$value->invoice_id."-".$value->po_id]))
				$tmpPrice[$value->item_id."-".$value->invoice_id."-".$value->po_id] = 0;
			$tmpPrice[$value->item_id."-".$value->invoice_id."-".$value->po_id] += ($value->price_per_item * $value->order_qty);
			$value->c = $c[$value->currency];
			$value->cur = $cur[$value->currency];
			$value->no = count($tmp[$value->invoice_id]["data"])+1;
			$value->show = false;

			if(!str_contains($value->manufacture_pn, ' ') && strlen($value->manufacture_pn) > 25)
				$value->manufacture_pn = chunk_split($value->manufacture_pn,25," ");
			// $tmppn = chunk_split($value->manufacture_pn, 20);
			// $value->manufacture_pn = implode("\n", $tmppn);
			
			if($value->as_reference == 0){
				$value->isuraian = false;
			}
			else{
				$value->isuraian = true;
					
				if($value->payment_pct == 0)
					$value->payment_pct = $value->payment_pct_fiat*(100/($value->total_price));
			}
			if($value->no==1)
				$value->show = true;
			$tmp[$value->invoice_id]["part"][] = $value->item_name;
			$tmp[$value->invoice_id]["data"][] = $value;
		}

		$mengetahui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."payment_validated_".$json["id"]);
		$menyetujui = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."payment_approved_".$json["id"]);

		//return $this->respond(["a"=>$tmp]);

		$templating = new \WMDE\VueJsTemplating\Templating;
		if(isset($json["debug"]))
			$vue = file_get_contents('./report/payment_debug.vue');
		else
			$vue = file_get_contents('./report/payment.vue');
		$dom = new \DOMDocument('1.0', 'utf-8');
		libxml_use_internal_errors(true);
		$dom->loadHTML($vue); 
		//echo $dom->getElementsByTagName('style')[0];
		$style = DOMinnerHTML($dom->getElementsByTagName('style')[0]);
		$date = '';
		//return $this->respond(["a"=>$data["data"]]);
		if(count($data["data"])>0)
			$date = $data["data"][0]->payment_date;
		/* $fmt = new \NumberFormatter('in_ID', \NumberFormatter::CURRENCY); */
		//$c = 'Rp.';
		/* $fmt->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $c);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0); */
		
		$validated = '';
		$approved = '';
		$invoice = [];
		$totalCurrencyAsing = 0;
		$currencyAsing = "";
		$notPetty = [];
		$petty = [];
		$pettyCashCurrencyAsing = 0;
		$totalPettyCash = 0;
		$totalNonPettyCash = 0;
		$nonPettyCashCurrencyAsing = 0;
		try {
			foreach ($data["data"] as $key => $value) {
				$approvedStatus = $value->approved;
				$validated = $value->approved1_date;
				$approved = $value->approved2_date;
				$invoice[] = $value->invoice_no;
				$value->po_no = str_replace("PO/", "PO/\n", $value->po_no);
				if($value->po_no == null){
				    $value->po_no = array("", "");
					$value->po_no1 = '';
					$value->po_no2 = '';
				}
				else{
					$value->po_no = explode("\n", $value->po_no);
					$value->po_no1 = $value->po_no[0];
					$value->po_no2 = $value->po_no[1];
				}
				$value->cls = $value->no == $pb ? "page-break":"";
				$data["data"][$key]->grand_total_price = $data["data"][$key]->grand_total_price - ($data["data"][$key]->invoice_reduction ?? 0) + ($data["data"][$key]->invoice_charge ?? 0);
				if($value->currency=='IDR'){
					$total+=$data["data"][$key]->invoice_total_price;
					$value->grand_total_price = $data["data"][$key]->invoice_total_price;
				}else{
					//$value->grand_total_price_original =  number_format($data["data"][$key]->grand_total_price,2,',','.');
					// $value->$value->grand_total_price_original_c =  number_format($data["data"][$key]->invoice_total_price,2,',','.');
					$value->grand_total_price_original =  number_format($data["data"][$key]->invoice_total_price,2,',','.');
					$totalCurrencyAsing += $data["data"][$key]->invoice_total_price;
					$value->grand_total_price = ($data["data"][$key]->invoice_total_price * $value->exchange_rate);
					$total+=$value->grand_total_price;
				}
				if($value->petty_cash == 1){
					$value->petty_cash = true;
					$pettyCash = true;
					$totalPettyCash += $value->grand_total_price;
					$pettyCashCurrencyAsing += $data["data"][$key]->invoice_total_price;
				}
				else{
					$value->petty_cash = false;
					$totalNonPettyCash += $value->grand_total_price;
					$nonPettyCashCurrencyAsing += $data["data"][$key]->invoice_total_price;
				}
				if($value->as_reference == 0){
					$value->isuraian = false;
				}
				else{
					$value->isuraian = true;
					
					if($value->payment_pct == 0)
						$value->payment_pct = $value->payment_pct_fiat*(100/($value->total_price));
				}
				if(isset($tmp[$value->invoice_id]))
				    $data["data"][$key]->parts = implode(', ', $tmp[$value->invoice_id]["part"]);
				else if($value->as_reference == 1)
				    $data["data"][$key]->parts = $value->uraian;
				else
					$data["data"][$key]->parts = "";
				if($data["data"][$key]->path!=""){
					try {
						$guesser = new RegexGuesser();

						$data["data"][$key]->pdfInfo = number_format($guesser->guess(trim($data["data"][$key]->path)));
						/* if($data["data"][$key]->pdfInfo!=1.4){
							$command = new GhostscriptConverterCommand();
							$filesystem = new Filesystem();

							$converter = new GhostscriptConverter($command, $filesystem);
							$converter->convert($data["data"][$key]->path, '1.4');
							//$data["data"][$key]->pdfInfo = number_format($guesser->guess($data["data"][$key]->path));
						} */
						$oPDF = new \setasign\Fpdi\Fpdi();
						$iPageCount = $oPDF->setSourceFile(trim($data["data"][$key]->path));
						$mTemplateId = $oPDF->importPage($iPageCount);
						$r = $oPDF->getTemplateSize($mTemplateId);
						$data["data"][$key]->pdfInfo = $r["orientation"] == "L" ? "horizontal" : "vertical";
						//$data["data"][$key]->pdfInfo = $iPageCount;
					} catch (\Throwable $th) {
						/* $command = new GhostscriptConverterCommand();
						$filesystem = new Filesystem();

						$converter = new GhostscriptConverter($command, $filesystem);
						$converter->convert('/path/to/my/file.pdf', '1.4'); */
						$data["data"][$key]->pdfInfo = 'invalid';
					}
					
					
				}
				$currencyAsing = $c[$value->currency];
				$data["data"][$key]->c = $c[$value->currency];
				$data["data"][$key]->cur = $cur[$value->currency];
				$data["currency"] = $value->currency != 'IDR';
				$data["data"][$key]->payment_pct = $value->payment_pct + 0;
				$data["data"][$key]->grand_total_price = number_format($value->grand_total_price,2,',','.');//formater($value->grand_total_price, $c);// \money_format($value->grand_total_price);
				//$data["data"][$key]->grand_total_price = number_format($value->invoice_total_price,2,',','.');//formater($value->grand_total_price, $c);// \money_format($value->grand_total_price);
			
				if($value->petty_cash){
					$petty[] = $data["data"][$key];
				}
				else{
					$notPetty[] = $data["data"][$key];
				}
			}

			$idx = 1;
			foreach ($petty as $key => $value) {
				$value->no_urut = $idx;
				$idx++;
			}
			foreach ($notPetty as $key => $value) {
				$value->no_urut = $idx;
				$idx++;
			}
		} catch (Exception $e) {
			return $this->respond("Payment tidak mempunyai invoice!");	
		}
		
		//return $this->respond($data);
		

		$this->info("<b>Validated:</b> $validated <br/>Invoice: ".implode(', ', $invoice), "payment_validated_".$json["id"], $json["id"]);
		$this->info("<b>Approved:</b> $approved <br/>Invoice: ".implode(', ', $invoice), "payment_approved_".$json["id"], $json["id"]);
		$params = [
			'date' => $date,
			'total' => number_format($total,2,',','.'),
			'totalCurrencyAsing' => number_format($totalCurrencyAsing,2,',','.'),
			'mengetahui' => $mengetahui,
			'validated' => $approvedStatus > 1,
			'approved' => $approvedStatus > 3,
			//'mengetahui2' => "https://internal.buanamultiteknik.com/#/sa/info/"."payment_validated".$uniqid,
			'menyetujui' => $menyetujui,
			'currency' => $data["currency"],
			'items' => json_decode(json_encode($data["data"]), true),
			'petty' => json_decode(json_encode($petty), true),
			'notPetty' => json_decode(json_encode($notPetty), true),
			'currencyAsing'=>$currencyAsing,
			'petty_cash'=>$pettyCash && !$data["currency"],
			'petty_cash_currency'=>$pettyCash && $data["currency"],
			'total_petty_cash'=>number_format($totalPettyCash,2,',','.'),
			'pettyCashCurrencyAsing'=>number_format($pettyCashCurrencyAsing,2,',','.'),
			'totalNonPettyCash'=>number_format($totalNonPettyCash,2,',','.'),
			'nonPettyCashCurrencyAsing'=>number_format($nonPettyCashCurrencyAsing,2,',','.')
		];

		if(isset($json["debug"]))
			$debugs[] = $params;
		//else{
		$tpl = $templating->render(DOMinnerHTML($dom->getElementsByTagName('template')[0]), $params);
		$tpls = $tpl;

		// instantiate and use the dompdf class
		$options = new \Dompdf\Options();
		$options->set('isPhpEnabled', TRUE);
		$dompdf = new \Dompdf\Dompdf($options);
		$dompdf->loadHtml($tpl."<style>$style</style>".'<script type="text/php">

		if ( isset($pdf) ) {
		
		  $size = 6;
		  $color = array(0,0,0);
		  if (class_exists("Font_Metrics")) {
			$font = Font_Metrics::get_font("helvetica");
			$text_height = Font_Metrics::get_font_height($font, $size);
			$width = Font_Metrics::get_text_width("Page 1 of 2", $font, $size);
		  } elseif (class_exists("Dompdf\\FontMetrics")) {
			$font = $fontMetrics->getFont("helvetica");
			$text_height = $fontMetrics->getFontHeight($font, $size);
			$width = $fontMetrics->getTextWidth("Page 1 of 2", $font, $size);
		  }
		
		  $foot = $pdf->open_object();
		  
		  $w = $pdf->get_width();
		  $h = $pdf->get_height();
		
		  $y = $h - $text_height - 24;
		
		  $pdf->close_object();
		  $pdf->add_object($foot, "all");
		
		  $text = "Page {PAGE_NUM} of {PAGE_COUNT}";  
		
		  $pdf->page_text($w - ($width+20), $h-24, $text, $font, $size, $color);
		  
		}
	</script>');

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');

		// Render the HTML as PDF
		$dompdf->render();
		$output = $dompdf->output();
		$path = "./download/payment".$uniqid.".pdf";
		$path2 = "./download/full".$json["id"].".pdf";
		file_put_contents($path, $output);
		$paths = [];
		if(isset($json["debug"])){
			$debugs[] = $tmp;
		}
		foreach($tmp as $key =>  $value){
			$vue = file_get_contents('./report/invoice.vue');
			$dom = new \DOMDocument('1.0', 'utf-8');
			libxml_use_internal_errors(true);
			$dom->loadHTML($vue); 
			//echo $dom->getElementsByTagName('style')[0];
			$style = DOMinnerHTML($dom->getElementsByTagName('style')[0]);
			$totalAll = 0;
			foreach($value["data"] as $val){
				$val->total_price = $tmpPrice[$val->item_id."-".$val->invoice_id."-".$val->po_id];
				$totalAll += $val->total_price;
				if($val->isuraian)
				    $val->total_price = $val->grand_total_price;
				if($val->currency!="IDR"){
					$val->rp = number_format($val->exchange_rate * $val->total_price,2,',','.');
				}
				else{
				    if($val->isuraian)
				        $val->rp = number_format($val->total_price,2,',','.');
				}

				$val->total_price = number_format($val->total_price,2,',','.');
				$val->exchange_rate = number_format($val->exchange_rate,2,',','.');
			}
			/* if($value->currency!="IDR"){
				$value->rp = $value->exchange_rate * $value->total_price;
			} */
			if(isset($json["debug"]))
			    $debugs[] = $value["data"];
			$tpl = $templating->render(DOMinnerHTML($dom->getElementsByTagName('template')[0]), [
				'date' => $date,
				'items' => json_decode(json_encode($value["data"]), true),
				'totalAll' => number_format($totalAll,2,',','.'),
				'payment_pct' => $value["data"][0]->payment_pct,
				'totalPayment'=>number_format($value["data"][0]->grand_total_price/100*$value["data"][0]->payment_pct,2,',','.'),
				//'grandTotal' => number_format($totalAll-($value["data"][0]->invoice_reduction ?? 0)+($value["data"][0]->invoice_charge ?? 0),2,',','.'),
				//'grandTotal' => number_format($totalAll-($value["data"][0]->invoice_reduction ?? 0)+($value["data"][0]->invoice_total_price ?? 0),2,',','.'),
				'length'=> count($value["data"]),
				'currency'=>$value["data"][0]->currency!="IDR",
				'invoice_charge'=>number_format($value["data"][0]->invoice_charge,2,',','.'),
				//'invoice_charge'=>number_format($value["data"][0]->invoice_total_price,2,',','.'),
				//'invoice_chargea'=>number_format($value["data"][0]->invoice_total_price,2,',','.'),
				'invoice_charge_note'=>$value["data"][0]->invoice_charge_note,
				'invoice_reduction'=>number_format($value["data"][0]->invoice_reduction,2,',','.'),
				'grandTotal'=>number_format($value["data"][0]->invoice_total_price,2,',','.'),
				'invoice_reduction_note'=>$value["data"][0]->invoice_reduction_note,
				'c'=>$value["data"][0]->currency,
				'exchange_rate'=>$value["data"][0]->exchange_rate,
				'rate_date'=>$value["data"][0]->rate_date
			]);
			//$tpls = $tpls.$tpl;
			// invoice
			$dompdf = new \Dompdf\Dompdf();
			$dompdf->loadHtml($tpl."<style>$style</style>");

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'portrait');

			// Render the HTML as PDF
			$dompdf->render();
			$output = $dompdf->output();
			$tpath = "./download/invoice_".$key."_".$uniqid.".pdf";
			$paths[$key] = $tpath;
			$url = trim($value["data"][0]->invoice_doc_url ?? "");
			if(trim($url)!=""){
				$tmp = explode("api", $url);
				$tmp = ".".$tmp[1];
				if (file_exists($tmp)){
					$paths[$key."url"] = $tmp;
				}
			}
			file_put_contents($tpath, $output);
		}

		/* $pdf = new \Jurosh\PDFMerge\PDFMerger;
		$pdf->addPDF($path, 'all'); */
		if(isset($json["debug"])){
		    $debugs[] = $tmp;
			$debugs[] = $data["data"];
			$debugs[] = $paths;
			return $this->respond($debugs);
		}

		//$tmpPath = "./download/invoice_".$uniqid."-.pdf";

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
			//$tpdf->addPDF($paths[$value->invoice_id], 'all');
			$oPDF = new \setasign\Fpdi\Fpdi();
			//if(isset($paths[$value->invoice_id."url"])){
				$iPageCount = $oPDF->setSourceFile($paths[$value->invoice_id]);
				$mTemplateId = $oPDF->importPage($iPageCount);
				$aTemplateSize = $oPDF->getTemplateSize($mTemplateId);
				$tpdf->addPDF($paths[$value->invoice_id], 'all', $aTemplateSize["orientation"] == "L" ? "horizontal" : "vertical");

				//$tpdf->addPDF($paths[$value->invoice_id."url"], 'all');
				$oPDF = new \setasign\Fpdi\Fpdi();
				$iPageCount = $oPDF->setSourceFile($paths[$value->invoice_id."url"]);
				$mTemplateId = $oPDF->importPage($iPageCount);
				$aTemplateSize = $oPDF->getTemplateSize($mTemplateId);
				$tpdf->addPDF($paths[$value->invoice_id."url"], 'all', $aTemplateSize["orientation"] == "L" ? "horizontal" : "vertical");
				
				//$tmpPath = "./download/invoice_".$uniqid."-".$value->invoice_id.".pdf";
				$tpdf->merge('file', $path2);
				$first = false;
			//}
		}

		//return $tmpPath."asdadasd";
		//$mime = \get_mime_by_extension($path);
		/* $pdf = new \Jurosh\PDFMerge\PDFMerger;
		$pdf->addPDF($tmpPath, 'all'); */
		//$pdf->addPDF($tmpPath, 'all');
		/* $oPDF = new \setasign\Fpdi\Fpdi();
		$iPageCount = $oPDF->setSourceFile($tmpPath);
		$mTemplateId = $oPDF->importPage($iPageCount);
		$aTemplateSize = $oPDF->getTemplateSize($mTemplateId);
		$pdf->addPDF($tmpPath, 'all', $aTemplateSize["orientation"] == "L" ? "horizontal" : "vertical"); */
		$name = "Payment Report.pdf";
		//$pdf->addPDF($tmpPath, 'all');
		//$pdf->merge('file', $path2);
		// Output the generated PDF to Browser
		/* $dompdf->stream(); */
		//$path2 = $tmpPath;
		//return $path2."aaa";
		//return "https://internal.buanamultiteknik.com/api/download/".$path2;
		/* header('Pragma: public');     // required
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
		foreach ($data["data"] as $key => $value) {
			if(file_exists($paths[$value->invoice_id])) // here is the problem
			{
				unlink($paths[$value->invoice_id]);
				
			}
			if(file_exists($value->path)) // here is the problem
			{
				unlink($value->path);
			}
		} */
		return "<a href='https://internal.buanamultiteknik.com/api/download/full".$json["id"].".pdf'>report</a><script>
		window.location = 'https://internal.buanamultiteknik.com/api/download/full".$json["id"].".pdf';
		</script>";

		if(file_exists($path2)) // here is the problem
		{
			//unlink($path2);
		}
		exit();
		//}
    }

	public function info($info, $uid, $pk){
		$model = new PaymentModel();
		$data = $model->db->query("update info set info = '".$info."' where uid = '".$uid."'");
	}

    public function data($id){
        try {
            $session = session();
            $model = new PaymentModel();
            $data = $model->db->query("select ROW_NUMBER() OVER() AS no, i.ref_invoice_no, i.kode_pembayaran, po.po_no, s.approved, i.flag, s.flag as sflag, po.flag as poflag, mi.flag as miflag, ms.flag as msflag, p.flag as pflag, 
			case when i.payment_pct_fiat != 0 then (i.payment_pct_fiat + i.invoice_charge) - invoice_reduction 
			when as_reference = 0 then ((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - invoice_reduction 
			else
				i.payment_amount + i.invoice_charge - i.invoice_reduction
			end
			as invoice_total_price, i.uraian, as_reference, i.total_price, i.payment_pct_fiat
			, s.notes, coalesce(po.exchange_rate, i.exchange_rate) as exchange_rate, s.approved1_date, s.approved2_date, ms.name, 
			coalesce(po.currency, ms.currency, 'IDR') as currency, i.id as invoice_id, i.notes, i.invoice_no, s.id, s.payment_no, 
			s.payment_date, mi.item_no, mi.item_name, trim(i.invoice_doc_url) as invoice_doc_url, i.payment_pct, i.grand_total_price, 
			i.invoice_charge, i.invoice_reduction, i.petty_cash
			from payment s 
			left join payment_item p on p.payment_id = s.id
			left join invoice i on i.id = p.invoice_id 
			left join purchase_order po on po.id = i.po_id 
			left join m_supplier ms on ms.id = i.supplier_id 
			left join m_item mi on mi.id = i.item_id 
			where s.id = $id and i.flag = 1 and p.flag = 1 order by i.petty_cash desc, p.id asc");
			if(!$data)
				return ["status"=>false, "data"=>$model->db->error()]; 
			$data = $data->getResult();
			
            /* if(count($data) > 0){
			}
			else
				return $this->respond(['status'   => false]); */
				//return $this->respond(["status"   => count($data)]);
			$max = count($data);
			//return $data[0];
			for ($i=0; $i < $max; $i++) { 
				$val = $data[$i];
				$path = "./download/invoice_".$val->invoice_id.".pdf";
				if(str_contains($val->invoice_doc_url, 'drive.google.com')){
					$fileId = explode('/', $val->invoice_doc_url)[5];
					$this->gdrive($fileId, $path);
				}
				//google
				/* $client = new Client();
				$client->setAuthConfig('./google.json');
				$client->addScope(Drive::DRIVE);
      			$driveService = new Drive($client);
				$realFileId = readline("Enter File Id: ");
				$fileId = explode('/', $val->invoice_doc_url)[5];
				$fileId = $realFileId;
				$response = $driveService->files->get($fileId, array(
					'alt' => 'media'));
				$content = $response->getBody()->getContents(); */
				else{
					$ch = curl_init($val->invoice_doc_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_REFERER, $val->invoice_doc_url);

					$file = curl_exec($ch);

					curl_close($ch);

					$result = file_put_contents($path, $file);
				}
				$val->path = $path;
			}
				
			$response = [
				'status'  => count($data) > 0 ? true : false,
				'data'    => $data
			];
				
            return $response;
        } catch (Exception $e) {
            return ['status'   => false, 'data'    => $e];
        }
    }

    public function detail($id){
        try {
            $session = session();
            $model = new PaymentModel();
            $data = $model->db->query("select ROW_NUMBER() OVER() AS no, 
			case when i.payment_pct_fiat != 0 then (i.payment_pct_fiat + i.invoice_charge) - invoice_reduction 
			when as_reference = 0 then ((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - invoice_reduction 
			else
				i.payment_amount + i.invoice_charge - i.invoice_reduction
			end
			as invoice_total_price, i.uraian, i.payment_pct_fiat, as_reference, i.total_price, coalesce(po.exchange_rate, i.exchange_rate) as exchange_rate, po.rate_date, s.approved1_date, s.approved2_date, mi.original_manufacture, mi.manufacture_pn, mi.specification, ms.bank_account_no, ms.bank, coalesce(po.currency, ms.currency, 'IDR') as currency, ms.bank_account_name, i.id as invoice_id, i.invoice_no, s.id, s.payment_no, s.payment_date, mi.item_no, mi.item_name, trim(i.invoice_doc_url) as invoice_doc_url, i.payment_pct, 
			i.grand_total_price, poi.item_id, poi.price_per_item, poi.order_qty, po.id as po_id, i.invoice_charge_note, i.invoice_reduction_note,
			i.invoice_charge, i.invoice_reduction, i.petty_cash
			from payment s 
			left join payment_item p on p.payment_id = s.id
			left join invoice i on i.id = p.invoice_id 
			left join purchase_order po on po.id = i.po_id 
			left join purchase_order_item poi on poi.purchase_order_id = po.id
			left join bom_header bh on bh.id = poi.bom_id 
			left join m_supplier ms on ms.id = i.supplier_id 
			left join m_item mi on mi.id = poi.item_id 
			where s.id = $id and i.flag = 1 and p.flag = 1 order by i.petty_cash desc, p.id asc");
			if(!$data)
				return ["status"=>false, "data"=>$model->db->error()]; 
			$data = $data->getResult();
				
			$response = [
				'status'  => count($data) > 0 ? true : false,
				'data'    => $data
			];
				
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
		->margin(1)
		->roundBlockSizeMode(new RoundBlockSizeModeMargin())
		->validateResult(false)
		->build();
		return $result->getDataUri();
	}

	public function test(){
		/* $a = 'https://drive.google.com/file/d/1YnFmL3I0z7KI7fGxp26dRjDObV9Jf7DC/view?usp=share_link';
		var_dump(explode('/', $a)[5]); */
		/* $pdf = new \FPDI('P','mm'); // change the snd parameter to change the units
		$pdf->setSourceFile('../test/55Dinamika.pdf');
		$pageId = $pdf->importPage(1);
		$size = $pdf->getTemplateSize($pageId);
		var_dump($size); */
		/* $pdf = new Fpdi();
		$pdf->AddPage();
		$pdf->setSourceFile('../test/55Dinamika.pdf'); 
		$tplIdx = $pdf->importPage(1);  */
		//$output = shell_exec("pdfinfo '../test/55Dinamika.pdf'");
		$oPDF = new \setasign\Fpdi\Fpdi();
        $iPageCount = $oPDF->setSourceFile('../test/55Dinamika.pdf');
        $mTemplateId = $oPDF->importPage($iPageCount);
        $aTemplateSize = $oPDF->getTemplateSize($mTemplateId);
		var_dump([$aTemplateSize["orientation"]]);

		/* $result = Builder::create()
		->writer(new PngWriter())
		->writerOptions([])
		->data('Custom QR code contents')
		->encoding(new Encoding('UTF-8'))
		->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
		->size(100)
		->margin(1)
		->roundBlockSizeMode(new RoundBlockSizeModeMargin())
		->labelText('This is the label')
		->labelFont(new NotoSans(20))
		->labelAlignment(new LabelAlignmentCenter())
		->validateResult(false)
		->build();
		return $result->getDataUri(); */
	}

	function getClient()
	{
		$client = new Client();
		$client->setApplicationName('Google Drive API PHP Quickstart');
		$client->setScopes(Drive::DRIVE);
		$client->setAuthConfig('./google.json');
		$client->setAccessType('offline');
		$client->setRedirectUri("https://internal.buanamultiteknik.com/api/report/payment/gdrive");
		$client->setPrompt('select_account consent');

		// Load previously authorized token from a file, if it exists.
		// The file token.json stores the user's access and refresh tokens, and is
		// created automatically when the authorization flow completes for the first
		// time.
		$tokenPath = './token.json';
		if (file_exists($tokenPath)) {
			$accessToken = json_decode(file_get_contents($tokenPath), true);
			$client->setAccessToken($accessToken);
		}

		// If there is no previous token or it's expired.
		if ($client->isAccessTokenExpired()) {
			// Refresh the token if possible, else fetch a new one.
			if ($client->getRefreshToken()) {
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			} else {
				// Request authorization from the user.
				/* $authUrl = $client->createAuthUrl();
				printf("Open the following link in your browser:\n%s\n", $authUrl);
				print 'Enter verification code: '; */
				$authCode = '4/0AfgeXvvAv4tUWVeIm70foogbeZHWbMZJEsaM98tN6nwFtTKIfcUSMHm36gcfq0HgSMConw';

				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

				$client->setAccessToken($accessToken);

				// Check to see if there was an error.
				if (array_key_exists('error', $accessToken)) {
					throw new Exception(join(', ', $accessToken));
				}
			}
			// Save the token to a file.
			if (!file_exists(dirname($tokenPath))) {
				mkdir(dirname($tokenPath), 0700, true);
			}
			file_put_contents($tokenPath, json_encode($client->getAccessToken()));
		}
		return $client;
	}

	public function t(){
		//echo $this->gdrive('https://drive.google.com/file/d/1YnFmL3I0z7KI7fGxp26dRjDObV9Jf7DC/view');
	}
	//4/0AfgeXvvAv4tUWVeIm70foogbeZHWbMZJEsaM98tN6nwFtTKIfcUSMHm36gcfq0HgSMConw
	public function gdrive($fileId, $path){
		/* if(!isset(fileId))
			return false; */
		$client = $this->getClient();
		//var_dump($client);
		$service = new Drive($client);
		//$realFileId = readline("Enter File Id: ");
		//$fileId = "1YnFmL3I0z7KI7fGxp26dRjDObV9Jf7DC";
		$response = $service->files->get($fileId, array(
			'alt' => 'media'));
		$content = $response->getBody()->getContents();
		file_put_contents($path, $content);
		return true;
		/* putenv('GOOGLE_APPLICATION_CREDENTIALS=./google.json');
		$a = 'https://drive.google.com/file/d/1YnFmL3I0z7KI7fGxp26dRjDObV9Jf7DC/view?usp=share_link';
		$client = new Client();
		$client->setApprovalPrompt('force'); */
		//$client->setAuthConfig('./google.json');
		/* $client->addScope(Drive::DRIVE);
		$client->useApplicationDefaultCredentials(); */
		/* $client->setRedirectUri("https://internal.buanamultiteknik.com/api/report/payment/gdrive");
		$client->setAccessType('offline');
		$client->authenticate($_GET['code']);
		$token = $client->getAccessToken();
		$client->setAccessToken($token);
		$client->setPrompt('select_account consent'); */
		/* $driveService = new Drive($client);
		$realFileId = readline("Enter File Id: ");
		$fileId = "1YnFmL3I0z7KI7fGxp26dRjDObV9Jf7DC";
		$response = $driveService->files->get($fileId, array(
			'alt' => 'media'));
		$content = $response->getBody()->getContents();
		var_dump($client); */
		/* $pdf = new Fpdi();
		$pdf->AddPage();
		$pdf->setSourceFile('../test/55Dinamika.pdf'); 
		$tplIdx = $pdf->importPage(1);  */
		//$output = shell_exec("pdfinfo '../test/55Dinamika.pdf'");
		
	}
 
}