<?php namespace App\Controllers\Report\Payment;
set_time_limit(0);
ini_set('memory_limit', '400M');
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

// 	public function test2(){
// 		$a = "https://main.buanamultiteknik.com/api/uploads/rfq3981/1775728251_d0db8f76f0188dd0891d.pdf";
		
		



// 		try {
				
// 			$ch = curl_init($a);
// 			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 			curl_setopt($ch, CURLOPT_REFERER, $a);

// 			$file = curl_exec($ch);

// 			curl_close($ch);
//             $download_path = __DIR__ . '/download/';
// // 			$result = file_put_contents('./download/a.pdf', $file);
// $result = file_put_contents($download_path . 'a.pdf', $file);
// 			var_dump($result);
// 			return $this->respond("Test!");
// 		} catch (Throwable $e) {
// 			return $this->respond($e->getMessage());
// 		}
// 	}

public function test2(){
    // Gunakan folder /api/download/
	var_dump(realpath('./download'));
    $download_path = $_SERVER['DOCUMENT_ROOT'] . '/api/download/';
    
    // Atau bisa juga dengan path absolut langsung:
    // $download_path = '/home/main.buanamultiteknik.com/public_html/api/download/';
    
    // Pastikan folder ada
    if (!is_dir($download_path)) {
        mkdir($download_path, 0777, true);
    }
    
    // Cek writable
    if (!is_writable($download_path)) {
        return $this->respond("Folder tidak writable: " . $download_path);
    }
    
    // URL PDF (pastikan URL ini benar dan bisa diakses)
    $a = "https://main.buanamultiteknik.com/api/download/0002_EL_PO_01_2024.pdf";
    
    try {
        $ch = curl_init($a);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_REFERER, $a);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $file = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Debug info
        $debug = [
            'download_path' => $download_path,
            'http_code' => $http_code,
            'file_size' => strlen($file),
            'curl_error' => $curl_error
        ];
        
        if ($file === false || $http_code !== 200) {
            return $this->respond("Download gagal: " . json_encode($debug));
        }
        
        // Simpan ke folder /api/download/
        $file_path = $download_path . 'a.pdf';
        $result = file_put_contents($file_path, $file);
        
        if ($result !== false) {
            return $this->respond("BERHASIL! File tersimpan di: " . $file_path . " (Ukuran: " . $result . " bytes)");
        } else {
            return $this->respond("Gagal simpan file. Error: " . error_get_last()['message']);
        }
        
    } catch (Throwable $e) {
        return $this->respond("Exception: " . $e->getMessage());
    }
}

    public function index(){
        
		helper(['Auth_helper']);
		
		if(!checkAuth())
			return $this->respond(['status' => false, 'message' => 'You area not allowed to view this page!'], 401);
        
		$json = $_REQUEST;
		$total = 0;
		$debugs = array();
		$approvedStatus = 0;

		// return $this->respond("Payment tidak mempunyai invoice b!");
        $data = $this->data($json["id"]);
		// return $this->respond("Payment tidak mempunyai invoice tesccb!");	
        $detail = $this->detail($json["id"]);
        // return $data;
      
        if(isset($data["status"]) && $data["status"] === false){
			return $this->respond($data);
		}
		
//   var_dump($detail); 
		$tmp = [];

		$cur = [
			'IDR'=> 'Rupiah',
			'-'=> 'Rupiah',
			'EUR'=> 'Euro',
			'USD'=> 'USD',
			'CNY'=> 'Chinese Yuan',
            'BDT'=> 'BDT',
            'SGD' => "Singapore Dollar",
		];

		$c = [
			'IDR'=> 'Rp',
			'-'=> 'Rp',
			'EUR'=> 'EUR',
			'USD'=> 'USD',
			'CNY'=> 'CNY',
			'BDT'=> 'BDT',
			'SGD' => "SGD",
		];
		$uniqid = uniqid();

// 		if($json["id"] == 109 && !isset($json["nocache"]))
		if($json["id"] == 174 && !isset($json["nocache"]))	{  
			return "<a href='https://main.buanamultiteknik.com/api/download/full".$json["id"].".pdf'>report</a><script>
			window.location = 'https://main.buanamultiteknik.com/api/download/full".$json["id"].".pdf';
			</script>";
			exit();
		}
		else
		{	//qr code

			//'https://main.buanamultiteknik.com/#/sa/info?id='+po_id

			$tmpPrice = [];

			$pb = $json["pagebreak"] ?? -1;
			$pettyCash = false;
			$requestPayment = false;

			foreach ($detail["data"] as $key => $value) {
				if(!isset($tmp[$value->invoice_id])){
					$tmp[$value->invoice_id] = ["part"=>[], "data"=>[]];
				}
				if(!isset($tmpPrice[$value->item_id."-".$value->invoice_id."-".$value->po_id]))
					$tmpPrice[$value->item_id."-".$value->invoice_id."-".$value->po_id] = 0;
				if($value->is_request_payment == 1)
					$requestPayment = true;
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
				if($value->petty_cash==1){
					$value->ispettycash =true;
				}
				else{
					$value->ispettycash =false;
				}
				if($value->no==1)
					$value->show = true;
				$tmp[$value->invoice_id]["part"][] = $value->item_name;
				$tmp[$value->invoice_id]["data"][] = $value;
			}

			$mengetahui = $this->qrcode("https://main.buanamultiteknik.com/#/sa/info/"."payment_validated_".$json["id"]);
			$menyetujui = $this->qrcode("https://main.buanamultiteknik.com/#/sa/info/"."payment_approved_".$json["id"]);

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
            $payno = '';
			$credit_note = '';
			$creditNoteInvoices = [];
			$creditNoteMap = [];
			$creditNoteTotalAsing = 0;
			$approved1= '';
			$approved2='';
			// return $this->respond(["a"=>$data["data"]]);
			$dataList = $data["data"] ?? [];
			if (is_object($dataList)) {
				$dataList = (array)$dataList;
			}
			if (is_array($dataList)) {
				$dataList = array_values($dataList);
			} else {
				$dataList = [];
			}
			
			if (is_array($dataList)) {
				$dataList = array_values(array_filter($dataList, function($row){
					return is_object($row);
				}));
			}
			
			$data["data"] = $dataList;
			if(isset($data["data"][0])){
				$date = $data["data"][0]->payment_date ?? '';
                $payno = $data["data"][0]->payment_no ?? '';
				$approved1 = $data["data"][0]->approved1 ?? '';
				$approved2 = $data["data"][0]->approved2 ?? '';
				$credit_note = $data["data"][0]->credit_note ?? '';
            }

			/* $fmt = new \NumberFormatter('in_ID', \NumberFormatter::CURRENCY); */
			//$c = 'Rp.';
			/* $fmt->setTextAttribute(\NumberFormatter::CURRENCY_CODE, $c);
			$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0); */
			
			$validated = '';
			$approved = '';
			$invoice = [];
			$totalCurrencyAsing = 0;
			$currencyAsing = "";
			$items = [];
			$notPetty = [];
			$petty = [];
			$pettyCashCurrencyAsing = 0;
			$totalPettyCash = 0;
			$totalNonPettyCash = 0;
			$nonPettyCashCurrencyAsing = 0;
			try {
				foreach ($data["data"] as $key => $value) {
				    if (!is_object($value)) {
						continue;
					}
					$approvedStatus = $value->approved;
					$validated = $value->approved1_date;
					$approved = $value->approved2_date;
					$invoice[] = $value->invoice_no;
					
					if (!isset($creditNoteMap[$value->invoice_id])) {
                        $cnAmountRaw = (float)($value->credit_note_amount ?? 0);
                        $cnAmountIdr = $cnAmountRaw;
                        $curCode = strtoupper($value->currency ?? 'IDR');
                        $rate = (float)($value->exchange_rate ?? 0);
                        if ($cnAmountRaw != 0 && $curCode !== 'IDR' && $rate > 0) {
                            $cnAmountIdr = $cnAmountRaw * $rate;
                            $creditNoteTotalAsing += $cnAmountRaw;
                        }
                        $creditNoteMap[$value->invoice_id] = [
                            "invoice_no" => $value->invoice_no,
                            "credit_note_no" => $value->credit_note_no ?? "",
                            "credit_note_amount" => $cnAmountIdr,
                        ];
                    }


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
						$value->grand_total_price_original = '';
					}else{
						//$value->grand_total_price_original =  number_format($data["data"][$key]->grand_total_price,2,',','.');
						// $value->$value->grand_total_price_original_c =  number_format($data["data"][$key]->invoice_total_price,2,',','.');
						$value->grand_total_price_original =  number_format($data["data"][$key]->invoice_total_price,2,',','.');
						$totalCurrencyAsing += $data["data"][$key]->invoice_total_price;
						$value->grand_total_price = ($data["data"][$key]->invoice_total_price * $value->exchange_rate);
						$total+=$value->grand_total_price;
					}
					if($value->petty_cash == 1){
						$value->ispettycash =true;
						$value->petty_cash = true;
						$pettyCash = true;
						$totalPettyCash += $value->grand_total_price;
						$pettyCashCurrencyAsing += $data["data"][$key]->invoice_total_price;
					}
					else{
						$value->ispettycash =false;
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
						$data["data"][$key]->parts = implode('|| ', array_unique($tmp[$value->invoice_id]["part"]));
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
					if (!is_array($c)) { $c = []; }
					if (!is_array($cur)) { $cur = []; }
					$curKey = $value->currency ?? '-';
					$currencyAsing = $c[$curKey] ?? ($c['-'] ?? 'Rp');
					$data["data"][$key]->c = $c[$curKey] ?? ($c['-'] ?? 'Rp');
					$data["data"][$key]->cur = $cur[$curKey] ?? ($cur['-'] ?? 'Rupiah');
					$data["currency"] = $curKey != 'IDR';
					$data["data"][$key]->payment_pct = $value->payment_pct + 0;
					$cnAmountRaw = (float)($value->credit_note_amount ?? 0);
				    $cnAmountIdr = $cnAmountRaw;
					$curCode = strtoupper($value->currency ?? 'IDR');
					$rate = (float)($value->exchange_rate ?? 0);
					if ($cnAmountRaw != 0 && $curCode !== 'IDR' && $rate > 0) {
						$cnAmountIdr = $cnAmountRaw * $rate;
					}
					$data["data"][$key]->has_credit_note = $cnAmountRaw != 0;
					$data["data"][$key]->credit_note_amount_src_fmt = number_format($cnAmountRaw,2,',','.');
					$data["data"][$key]->credit_note_amount_idr_fmt = number_format($cnAmountIdr,2,',','.');
					$data["data"][$key]->credit_note_no = $value->credit_note_no ?? '';
					
    				$data["data"][$key]->credit_note_label = $data["data"][$key]->credit_note_no !== '' ? "Reference : ".$data["data"][$key]->credit_note_reference.", ": "";
						
					$data["data"][$key]->credit_note_currency = $curCode;
					
				    $data["data"][$key]->credit_note_display = "Credit Note. ".$data["data"][$key]->credit_note_label."Amount: ".$curCode." ".$data["data"][$key]->credit_note_amount_src_fmt." | Rp. ".$data["data"][$key]->credit_note_amount_idr_fmt;

						
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
			
// 			return $this->respond($data);
			

			$this->info("<b>Validated:</b> $validated <br/>Invoice: ".implode(', ', $invoice), "payment_validated_".$json["id"], $json["id"]);
			$this->info("<b>Approved:</b> $approved <br/>Invoice: ".implode(', ', $invoice), "payment_approved_".$json["id"], $json["id"]);
			if($pettyCash && !$data["currency"]){
				$totalPettyCash = (int)$totalPettyCash;
				$pettyCashCurrencyAsing = (int)$pettyCashCurrencyAsing;
			}
			$creditNoteInvoices = array_values(array_filter($creditNoteMap, function($val){
				return (float)($val["credit_note_amount"] ?? 0) != 0;
			}));
			$creditNoteTotal = 0;
			foreach ($creditNoteInvoices as $val) {
				$creditNoteTotal += (float)($val["credit_note_amount"] ?? 0);
			}
			$hasCurrencyAsing = $data["currency"] ?? false;
			$noCurrencyAsing = !$hasCurrencyAsing;
			$creditNoteTotalIdr = $creditNoteTotal;
			$hasCreditNote = ((float)$creditNoteTotalIdr) != 0;
            $hasCreditNoteCurrency = $hasCreditNote && $hasCurrencyAsing;
            $hasCreditNoteIdr = $hasCreditNote && !$hasCurrencyAsing;
            $grandTotalAsing = $totalCurrencyAsing - $creditNoteTotalAsing;

			$params = [
				'is_request_payment' => $requestPayment,
				'approved1' => $approved1,
				'approved2' => $approved2,
								// 'credit_note' => number_format($creditNoteTotal,2,',','.'),
				'credit_note' => number_format($creditNoteTotalIdr,2,',','.'),
				'credit_note_asing' => number_format($creditNoteTotalAsing,2,',','.'),
				
				'has_credit_note' => $hasCreditNote,
                'has_credit_note_currency' => $hasCreditNoteCurrency,
                'has_credit_note_idr' => $hasCreditNoteIdr,


                'payno' => $payno,
				'date' => $date,
				'grand_total' => number_format($total - $creditNoteTotalIdr,2,',','.'),
				'grand_total_asing' => number_format($grandTotalAsing,2,',','.'),
								// 'grand_total' => number_format($total - $creditNoteTotal,2,',','.'),
				'total' => number_format($total,2,',','.'),
				'totalCurrencyAsing' => number_format($totalCurrencyAsing,2,',','.'),
				'mengetahui' => $mengetahui,
				'validated' => $approvedStatus > 1,
				'approved' => $approvedStatus > 3,
				//'mengetahui2' => "https://main.buanamultiteknik.com/#/sa/info/"."payment_validated".$uniqid,
				'menyetujui' => $menyetujui,
				// 'currency' => $data["currency"],
					'currency' => $hasCurrencyAsing,
					'no_currency' => $noCurrencyAsing,
				'items' => json_decode(json_encode($data["data"]), true),
				'petty' => json_decode(json_encode($petty), true),
				'notPetty' => json_decode(json_encode($notPetty), true),
				
				'currencyAsing'=>$currencyAsing,
				// 'petty_cash'=>$pettyCash && !$data["currency"],
				// 'petty_cash_currency'=>$pettyCash && $data["currency"],
				'petty_cash'=>$pettyCash && !$hasCurrencyAsing,
				'petty_cash_currency'=>$pettyCash && $hasCurrencyAsing,
				'total_petty_cash'=>number_format($totalPettyCash,2,',','.'),
				'pettyCashCurrencyAsing'=>number_format($pettyCashCurrencyAsing,2,',','.'),
				'totalNonPettyCash'=>number_format($totalNonPettyCash,2,',','.'),
						'nonPettyCashCurrencyAsing'=>number_format($nonPettyCashCurrencyAsing,2,',','.'),
				'credit_note_invoices' => json_decode(json_encode($creditNoteInvoices), true),
			];

			$items = json_decode(json_encode($data["data"]), true);

			if(isset($json["debug"]))
				$debugs[] = $params;
			//else{
			$tpl = $templating->render(DOMinnerHTML($dom->getElementsByTagName('template')[0]), $params);
			//pdf image

			$tpls = "
			<script>
			function showSpinner() {
				//document.getElementById('loading').style.visibility = 'visible';
			}

			function hideSpinner() {
				//document.getElementById('loading').style.visibility = 'hidden';
			}
			
			showSpinner()

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
			}</script>
			<style>

				/* Absolute Center Spinner */
				.loading {
					position: fixed;
					z-index: 999;
					height: 2em;
					width: 2em;
					overflow: show;
					margin: auto;
					top: 0;
					left: 0;
					bottom: 0;
					right: 0;
					visibility: hidden;
					position: fixed;
					top: 0;
					left: 0;
					height: 100%;
					width: 100%;
				}

				/* Transparent Overlay */
				.loading:before {
					content: '';
					display: block;
					position: fixed;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					background-color: rgba(0,0,0,0.3);
				}

				/* :not(:required) hides these rules from IE9 and below */
				.loading:not(:required) {
					text-shadow: none;
					background-color: transparent;
					border: 0;
				}

				.pagebreak {
					break-before: page;
				}

				@media print {
					.pagebreak {
						break-before: page;
					}
				}

				@page {
					size: A4;
					margin: 5mm 5mm 10mm 5mm;
					counter-increment: page;
					@bottom-right {
						content: \"Page \" counter(page) \" of \" counter(pages);
					}
				}
			</style>
			
			";

			//end of pdf image
			$tpls .= $tpl."<div class=\"pagebreak\"> </div>";
            //return $tpl."<style>$style</style>";
			// instantiate and use the dompdf class
			$options = new \Dompdf\Options();
			$options->set('isPhpEnabled', TRUE);
			$dompdf = new \Dompdf\Dompdf($options);
			$tpls .= "<style>$style</style>";
			$dompdf->loadHtml($tpl."<style>
			$style
			</style>".'<script type="text/php">

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
			$targetPDF = [];
			// Render the HTML as PDF
			$dompdf->render();
			$output = $dompdf->output();
			$path = "./download/payment".$uniqid.".pdf";
			$path2 = "./download/full".$json["id"].".pdf";
			file_put_contents($path, $output);
			$paths = [];
			if(isset($json["debug"])){
				$debugs[] = 'tmp';
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
					}else{
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
					
				// $debugs;
				
				// 	$response = [
    //     				'status'  => count($data) > 0 ? true : false,
    //     				'data'    => $debugs
    //     			];
        				
    //                 return $response;
            
				
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
				$tpls = $tpls.$tpl;

				//draw pdf as image
				$atth = "https://main.buanamultiteknik.com/api/"."download/invoice_".$key."_".$uniqid.".pdf";
				$targetPDF[] = [$value["data"][0]->invoice_doc_url, "#pdf$key"];
				$tpls .= "
					<div id=\"pdf".$key."\"></div>	
				";
				//end of draw pdf as image
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
					//var_dump($url);
					$tmp = explode("api", $url);
					$tmp = ".".$tmp[1];
					if (file_exists($tmp)){
						$paths[$key."url"] = $tmp;
					}
				}
				file_put_contents($tpath, $output);
			}
			
			  
    
        
			if(isset($json["noattachment"]))
				$targetPDF = [];
			$tpls .= "
				<script type='module' src='../../../library/pdf.mjs'></script>
				<script type='module' src='../../../library/pdfviewer.mjs' onload='renderALLPDF()'></script>
				<script>

					var targetPDF = ".json_encode($targetPDF).";
					function insertAfter(referenceNode, newNode) {
						referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
					}
					async function renderALLPDF(){
						var proms = []
						targetPDF.map(val=>{
							proms.push(renderPDF(val[0], val[1]));
						})

						await Promise.all(proms)
						hideSpinner()
						setTimeout(()=>{
							var script = document.createElement('script');

							//script.setAttribute('src','https://unpkg.com/pagedjs@0.4.3/dist/paged.polyfill.js');

							//document.head.appendChild(script);
						}, 500)
					}
					function renderPDF(attachment, sel) {
						return new Promise((resolve, reject) => {
							var temp = attachment;
							var table = `<table style='border: 0; border-collapse: collapse;'>`
							pdfjsLib.getDocument(temp).promise.then(async function (doc) {
								var pages = []; while (pages.length < doc._pdfInfo.numPages) pages.push(pages.length + 1);
								var images = []
								await Promise.all(pages.map(function (num, i) {
									// create a div for each page and build a small canvas for it
									var div = document.createElement('div');
									return doc.getPage(num).then(makeThumb)
									.then(function (canvas) {
										var ctx = canvas.getContext('2d');
										var img = new Image();
										if(canvas.width < canvas.height)
											img.classList.add('pdf-p');//img.style = 'display: block; height: 100%; max-width: 100%; object-fit: cover;'
										else{
											/* ctx.rotate(Math.PI/2);
											ctx.translate(0,90);
											ctx.drawImage(canvas, 0,0); */
											img.classList.add('pdf-l');//img.style = 'display: block; max-height: 100%; width: 100%; object-fit: cover;'
										}
										img.src = canvas.toDataURL();
										images[i] = img
									});
								}));
								images.map(img=>{
									insertAfter(document.querySelector(sel), img)
									//document.querySelector(sel).appendChild(img)
								})
								resolve(true)
							}).catch(c=>{
								resolve(true)
							});
						});
						
					}
				</script>
			";
			/* $pdf = new \Jurosh\PDFMerge\PDFMerger;
			$pdf->addPDF($path, 'all'); */
			if(isset($json["debug"])){
				$debugs[] = $tmp;
				$debugs[] = $data["data"];
				$debugs[] = $paths;
				return $this->respond($debugs);
			}
			if(isset($json["debughtml"])){
				/* $tpls .= "
				<div id=\"content\">
					<div id=\"pageFooter\">Page </div>
					multi-page content here...
				</div>
				"; */
				return $this->respond($tpls);
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
				if(isset($paths[$value->invoice_id."url"])){
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
				}
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
			//return "https://main.buanamultiteknik.com/api/download/".$path2;
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
			return "<a href='https://main.buanamultiteknik.com/api/download/full".$json["id"].".pdf'>report</a><script>
			window.location = 'https://main.buanamultiteknik.com/api/download/full".$json["id"].".pdf';
			</script>";

			if(file_exists($path2)) // here is the problem
			{
				//unlink($path2);
			}
			exit();
		}
		//}
    }

	public function info($info, $uid, $pk){
		$model = new PaymentModel();
		$data = $model->db->query("update info set info = '".$info."' where uid = '".$uid."'");
	}

    public function data($id){
        try {
			//return ["status"=>false, "data"=>'a'];
            $session = session();
            $model = new PaymentModel(); 
            $data = $model->db->query("select ROW_NUMBER() OVER() AS no, a.*
			from (
				select distinct
					i.ref_invoice_no, i.ref_comm_no, i.kode_pembayaran, po.po_no, s.approved, i.flag, i.etd, s.flag as sflag, po.flag as poflag, mi.flag as miflag, ms.flag as msflag, p.flag as pflag, 
					case when i.payment_pct_fiat != 0 then (i.payment_pct_fiat + i.invoice_charge) - invoice_reduction 
					when as_reference = 0 then ((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - invoice_reduction 
					else
						i.payment_amount + i.invoice_charge - i.invoice_reduction
					end
					as invoice_total_price, i.uraian, as_reference, i.total_price, i.payment_pct_fiat
					, coalesce(po.exchange_rate, i.exchange_rate) as exchange_rate, s.approved1_date, s.approved1,s.credit_note, s.approved2, s.approved2_date, ms.name, 
					coalesce(po.currency, ms.currency, 'IDR') as currency, ms.bank, ms.bank_account_no, ms.bank_account_name, i.id as invoice_id, i.notes, i.invoice_no, s.id, s.payment_no, 
					s.payment_date, mi.item_no, mi.item_name, trim(i.invoice_doc_url) as invoice_doc_url, i.payment_pct, i.grand_total_price, 
					i.credit_note_amount, cn.credit_no as credit_note_no, cn.reference_no as credit_note_reference,
					i.invoice_charge, i.invoice_reduction, i.petty_cash
					from payment s 
					left join payment_item p on p.payment_id = s.id
					left join invoice i on i.id = p.invoice_id 
					left join purchase_order po on po.id = i.po_id 
					left join m_supplier ms on ms.id = COALESCE(i.reimburse_id, i.supplier_id) 
					left join m_item mi on mi.id = i.item_id 
					left join credit_note cn on cn.id = i.credit_note_id
					where s.id = $id and i.flag = 1 and p.flag = 1 
					order by i.petty_cash desc, p.id asc
				)a
			");
			if(!$data)
				return ["status"=>false, "data"=>$model->db->error()]; 
			$data = $data->getResult();
			//var_dump($data);
			//return ["status"=>false, "data"=>'a'];
			
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
				$val->invoice_doc_url = str_replace("internal.", "main.", $val->invoice_doc_url);
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
            $data = $model->db->query("select ROW_NUMBER() OVER() AS no, b.*
			from (
				select distinct a.* from
			(select
			case when i.payment_pct_fiat != 0 then (i.payment_pct_fiat + i.invoice_charge) - invoice_reduction 
			when as_reference = 0 then ((i.grand_total_price * (i.payment_pct/100)) + i.invoice_charge) - invoice_reduction 
			else
				i.payment_amount + i.invoice_charge - i.invoice_reduction
			end
			as invoice_total_price, i.uraian, i.payment_pct_fiat, as_reference, i.total_price, coalesce(po.exchange_rate, i.exchange_rate) as exchange_rate, po.rate_date, s.approved1_date, s.approved2_date, mi.original_manufacture, mi.manufacture_pn, mi.specification, mi.article_no, ms.bank_account_no, ms.bank, coalesce(po.currency, ms.currency, 'IDR') as currency, ms.bank_account_name, i.id as invoice_id, i.invoice_no, s.id, s.payment_no, s.payment_date, mi.item_no, mi.item_name, trim(i.invoice_doc_url) as invoice_doc_url, i.payment_pct, 
			i.grand_total_price, poi.item_id, poi.price_per_item, poi.order_qty, po.id as po_id, i.invoice_charge_note, i.invoice_reduction_note,
			i.invoice_charge, i.invoice_reduction, i.petty_cash, s.is_request_payment
			from payment s 
			left join payment_item p on p.payment_id = s.id
			left join invoice i on i.id = p.invoice_id 
			left join purchase_order po on po.id = i.po_id 
			left join purchase_order_item poi on poi.purchase_order_id = po.id
			left join bom_header bh on bh.id = poi.bom_id 
			left join m_supplier ms on ms.id = i.supplier_id 
			left join m_item mi on mi.id = poi.item_id 
			where s.id = $id and i.flag = 1 and p.flag = 1 and (poi.flag=1 or poi.flag is null) order by i.petty_cash desc, p.id asc
			)a
			)b");
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
		$client->setRedirectUri("https://main.buanamultiteknik.com/api/report/payment/gdrive");
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
		/* $client->setRedirectUri("https://main.buanamultiteknik.com/api/report/payment/gdrive");
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