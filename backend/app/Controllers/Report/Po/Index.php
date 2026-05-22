<?php namespace App\Controllers\Report\Po;
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use App\Models\DefaultModel;
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
use NumberToWords\NumberToWords;
define('STDIN',fopen("php://stdin","r"));

class Index extends Controller
{
    use ResponseTrait;

    public function index(){
		$parser = \Config\Services::parser();
		$model = new DefaultModel();
		$json = $_REQUEST;
		$json = (Object) $json;
		$total = 0;
		$debugs = array();
		$qr = '';
		$numberToWords = new NumberToWords();
		$numberTransformer = $numberToWords->getNumberTransformer('en');
		
		$cur = [
			'IDR'=> 'Rupiah',
			'EUR'=> 'Euro',
			'USD'=> 'USD',
			'CNY'=> 'Chinese Yuan',
            'SGD' => "Singapore Dollar",
		];

		$c = [
			'IDR'=> 'Rp',
			'EUR'=> 'EUR',
			'USD'=> 'USD',
			'CNY'=> 'CNY',
			'SGD' => 'SGD',
		];
		$uniqid = uniqid();

		$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus',
		'September', 'Oktober', 'November', 'December'];

		$po_id = $json->po_id;

		$q = "SELECT i.article_no, p.ref_offer_no, u.name as dir, ms.pic_name, ms.id as supplier_id, p.currency, p.note as keterangan, s.*, order_qty*price_per_item as total_price_per_item, i.specification from (
						
			select s.id, coalesce(s.order_no, s.id) as order_no, s.flag, s.purchase_order_id, s.item_id, i.item_no, i.item_name, 
			i.unit, i.original_manufacture, i.manufacture_pn, sum(s.order_qty) as order_qty, 
			s.price_per_item 
			from purchase_order_item s left join m_item i on i.id = s.item_id
			where i.id is not null and s.flag = 1
			group by s.item_id, i.original_manufacture, i.manufacture_pn, i.specification, i.unit, i.article_no, s.purchase_order_id, s.price_per_item
		)s 
		left join m_item i on i.id = s.item_id
		left join purchase_order p on p.id = s.purchase_order_id
		left join m_supplier ms on ms.id = p.supplier_id
		left join m_department d on d.id = p.dept_id
		left join users u on u.id = d.approval_1
		where p.id = $po_id";

		$result = $model->db->query($q);
		if(!$result)
			return ["status"=>false, "data"=>$model->db->error()]; 
		$result = $result->getResult();

		$q = "select coalesce((select sum(payment_pct) from invoice where po_id = s.id), 0) as total_payment_pct, ms.pic_name, ms.address, s.*, d.dept_code, d.dept_name, ms.name as supplier_name from purchase_order s left join m_supplier ms on ms.id = s.supplier_id left join m_department d on d.id = s.dept_id where s.id = $po_id";
	
		$po = $model->db->query($q);
		if(!$po)
			return ["status"=>false, "data"=>$model->db->error()]; 
		$po = $po->getResult();
		
		foreach ($result as $i => $val) {
			$val->no = $i+1;
			$total += $val->total_price_per_item;
		}

		if($po[0]->approved == 2){
			$qr = $this->qrcode("https://internal.buanamultiteknik.com/#/sa/info/"."po_approved_".$po_id);
		}

		$date = array_reverse(explode('-', $po[0]->po_date));
		$date[1] = $bulan[(int)$date[1]];

		$items = array_merge($result);
		$keterangan = '';
		$name = '';

		foreach ($items as $i => $val) {
			$val->price_per_item = number_format($val->price_per_item,2,',','.');
			$keterangan = $val->keterangan;
			$name = $val->dir;
			$val->total_price_per_item = number_format($val->total_price_per_item,2,',','.');
		}

		$imgpath = '/home/buanamultiteknik/internal.buanamultiteknik.com/img/logo.png';
		$type = pathinfo($imgpath, PATHINFO_EXTENSION);
		$data = file_get_contents($imgpath);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

		$array = array_merge(array(), $items);
		$items = array_merge($array, $items);
		$items = array_merge($array, $items);
		$items = array_merge($array, $items);
		$items = array_merge($array, $items);
		$items = array_merge($array, $items);
		$items = array_merge($array, $items);
		$items = array_merge($array, $items);
		$items = array_merge($array, $items);
		array_splice($items, count($items) - 1, 1);
		$res = [
			"total_payment_pct"=> $po[0]->total_payment_pct,
			"logo"=> $base64,
			"title"=> $po[0]->title,
			"pic_name"=> $po[0]->pic_name,
			"approved"=> $po[0]->approved,
			"address"=> $po[0]->address??'',
			"po_no"=> $po[0]->po_no,
			"po_date"=> implode(' ', $date),
			"order_type"=> $po[0]->order_type,
			"currency"=> $cur[$po[0]->currency],
			"c"=> $c[$po[0]->currency],
			"supplier_name"=> $po[0]->supplier_name,
			"pt"=> 'PT. BMT BUANA MULTI TEKNIK',
			"name"=> $name,
			"jabatan"=> 'Director',
			"total"=> number_format($total,2,',','.'),
			"terbilang"=> ucwords(str_replace('-', ' ', $numberTransformer->toWords($total))),
			"items"=> $items,
			"barcode"=> $qr,
			"keterangan"=> $keterangan,
			"po"=> $po
		];

		if(isset($json->debug)){
			return $this->respondCreated([$res], 200);
		}
		else{$path2 = "./download/po_".$uniqid.".pdf";
			$stylesheet = file_get_contents('./app/Views/report/po.css');
			$tpl = $parser->setData($res)->render('report/po');
			$path2 = "./download/po_".$uniqid.".pdf";
			$stylesheet = file_get_contents('./app/Views/report/po.css');
			$tpl = $parser->setData($res)->render('report/po');
			
			/* $dompdf = new \Dompdf\Dompdf();
			$dompdf->loadHtml($tpl);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'portrait'); */
			
			return $tpl;
			// Render the HTML as PDF
			/* $dompdf->render();
			$canvas = $dompdf->getCanvas();
			$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
				$pageWidth = $canvas->get_width();
				$pageHeight = $canvas->get_height();
				$size = 34.2;
				//$width = $fontMetrics->getTextWidth($text, $font, $size);
				$color = [0, 0, 0];
				$bottom = $pageHeight - ($size+4);
				if($pageNumber != $pageCount){
					$canvas->line($size, $bottom, $pageWidth - $size, $bottom, $color, 0.75);
					//line 1
					$canvas->line($size+0.2, $bottom, $size+0.2, $bottom-50, $color, 0.75);
					//line 2
					$canvas->line($size+35, $bottom, $size+35, $bottom-50, $color, 0.75);
					//line 3
					$canvas->line($size+177.35, $bottom, $size+177.35, $bottom-50, $color, 0.75);
					//line 4
					$canvas->line($size+221.45, $bottom, $size+221.45, $bottom-50, $color, 0.75);
					//line 5
					$canvas->line($size+297.45, $bottom, $size+297.45, $bottom-50, $color, 0.75);
					//line 6
					$canvas->line($size+403.25, $bottom, $size+403.25, $bottom-50, $color, 0.75);
					//line 7
					$canvas->line($pageWidth - ($size+0.2), $bottom, $pageWidth - ($size+0.2), $bottom-50, $color, 0.75);
				}
			});
			$output = $dompdf->output();
			
			$name = "Purchase order.pdf";
			file_put_contents($path2, $output);
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
			exit(); */
		}
		//https://internal.buanamultiteknik.com/api/report/po/index?po_id=8
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
}