<?php namespace App\Controllers\Report\Container;
set_time_limit(0);
ini_set('memory_limit', '550M');
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use App\Models\Transaction\SubkolipartModel;
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
use CodeIgniter\RESTful\ResourceController;
define('STDIN',fopen("php://stdin","r"));
date_default_timezone_set("Asia/Jakarta");

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

        $session = session();
		$s = $session->get();
		$json = $_REQUEST;
		$total = 0;
		$debugs = array();
        $id=$json["id"];

        $data = $this->data($json["id"]);

		$tmp = [];
		$uniqid = uniqid();
		$last = false;

        if(count($data["data"])==0)
            return "Ups No Data Found!";
		foreach ($data["data"] as $key => $value) {
// 			$value->last = true;
//             $value->isnotimage=$value->attachment==null;
//             $value->attachment = explode("+++", $value->attachment);
//             $value->attachment='https://internal.buanamultiteknik.com/api/test/thumbnail?url=./uploads/rm'.$value->parent_id.'/'.$value->attachment[0];
            $tmp[]=$value;
		}

		$templating = new \WMDE\VueJsTemplating\Templating;
		$vue = file_get_contents('./report/container.vue');
		$dom = new \DOMDocument('1.0', 'utf-8');
		libxml_use_internal_errors(true);
		$dom->loadHTML($vue); 

		$style = DOMinnerHTML($dom->getElementsByTagName('style')[0]);
		$date=new \DateTime('yesterday');
		$date=$date->format('Y-m-d');
			
		$imgpath = '/home/buanamultiteknik/internal.buanamultiteknik.com/img/logo.png';
		$type = pathinfo($imgpath, PATHINFO_EXTENSION);
		$data = file_get_contents($imgpath);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

		$params = [
			"logo"=>$base64,
            "items"=>json_decode(json_encode($tmp), true)
		];

		if(isset($json["debug"])){
			$debugs[] = $params;
			return $this->respondCreated($debugs, 200);
		}
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
		$path = "./download/report_rebuild".$uniqid.".pdf";
		file_put_contents($path, $output);
		

		$name = "Delivery Note Shipment .pdf";

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

        return "<a href='https://internal.buanamultiteknik.com/api/download/dailyrebuild.pdf'>report</a><script>
		window.location = 'https://internal.buanamultiteknik.com/api/download/dailyrebuild.pdf';
		</script>";
    }

    public function data($id){
        try {

            $session = session();
            $model = new SubkolipartModel();
            $data = $model->db->query("
            select ROW_NUMBER() OVER() AS no, s.* from (select i.item_name, i.specification, substring_index(i.specification, '\n', 1) as descriptionn, SUBSTRING_INDEX(SUBSTRING_INDEX(i.specification,' \n',3),'\n',-2) as material, concat(bh.description, '-', br.machine_no) as description, s.photo, s.qty, s.container_id, s.koli_id, s.subkoli_id, s.flag, po.po_no
			from subkoli_part s
			left join m_item i on i.id = s.item_id
			left join bom_receive_header bh on bh.id = s.bom_header_id
			left join bom_receive br on br.id=s.bom_receive_id
			left join purchase_order po on po.id=br.po_id
			left join subkoli si on si.id=s.subkoli_id where s.flag=1 order by s.koli_id asc) s WHERE s.container_id='$id' ");
			if(!$data)
				return ["status"=>false, "data"=>$model->db->error()];
			$data = $data->getResult();
			
			if(count($data) == 0){
				$response = [
					'status'  => true,
					'data'    => $data
				];
			}else{
                $response = [
					'status'  => false,
					'data'    => $data
				];
            }
            return $response;
        } catch (Exception $e) {
            return ['status'   => false, 'data'    => $e];
        }
    }
 
}