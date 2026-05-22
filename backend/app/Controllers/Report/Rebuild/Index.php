<?php namespace App\Controllers\Report\Rebuild;
require 'vendor/autoload.php';
use CodeIgniter\Controller;
use App\Models\Rebuildmachine\ReportItemGroupModel;
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

        $data = $this->data();

		$tmp = [];
		$uniqid = uniqid();
		$last = false;

        if(count($data["data"])==0)
            return "Ups No Data Found!";
		foreach ($data["data"] as $key => $value) {
			$value->last = true;
            $value->isnotimage=$value->attachment==null;
            $value->attachment = explode("+++", $value->attachment);
            $value->attachment='https://internal.buanamultiteknik.com/api/test/thumbnail?url=./uploads/rm'.$value->parent_id.'/'.$value->attachment[0];
            $tmp[]=$value;
		}

		$templating = new \WMDE\VueJsTemplating\Templating;
		$vue = file_get_contents('./report/daily_rebuild.vue');
		$dom = new \DOMDocument('1.0', 'utf-8');
		libxml_use_internal_errors(true);
		$dom->loadHTML($vue); 

		$style = DOMinnerHTML($dom->getElementsByTagName('style')[0]);
		$date = '';
			
		$imgpath = '/home/buanamultiteknik/internal.buanamultiteknik.com/img/logo.png';
		$type = pathinfo($imgpath, PATHINFO_EXTENSION);
		$data = file_get_contents($imgpath);
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

		$params = [
			"logo"=>$base64,
            "items"=>json_decode(json_encode($tmp), true),
            "parent_id"=>164,
			"date"=> date("Y-m-d")
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
		

		$name = "Report Rebuild .pdf";

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

    public function data(){
        try {
            $session = session();
            $model = new ReportItemGroupModel();

			helper(['Query_helper']);
			$json = $_REQUEST;
			$json =  json_decode(base64_decode($json["parameter"]));
			if(!isset($json->filter))
				$json->filter = json_decode('{}', true);
			if(!isset($json->filterType))
				$json->filterType = json_decode('{}', true);
			if(!isset($json->filterCondition))
				$json->filterCondition = json_decode('{}', true);
			$json = (Object) $json;

			// $limit = $json->limit ?? 10;
			// $offset = $json->offset ?? 0;
			// $sortBy = $json->sortBy ?? array();
			// $sortDesc = $json->sortDesc ?? array();
			// $order = order($sortBy, $sortDesc);
			$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);

            $data = $model->db->query("select ROW_NUMBER() OVER() AS no, a.*
			FROM (select b.date, a.*, a.subassy_id as s_subassy, case when a.type=1 then 'Replacement' when a.type=2 then 'Repair' when a.type=3 then 'Installation' when a.type=4 then 'Pre-assembled' end as type_report, u.name, i.manufacture_pn from report_item_rebuild a left join report_parent_rebuild b on b.id = a.parent_id  left join m_item i on i.id =a.item_replacement_id left join users u on u.id=b.created_by where a.flag=1 and b.flag=1 ) a $where");
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