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
 
class Listsubassemlydashboard extends Controller
{
    use ResponseTrait;

    public function index(){
		$json = $_REQUEST;
		$total = 0;
		$debugs = array();

        $data = $this->data($json["type_subassy"]);

		$tmp = [];
		$uniqid = uniqid();
		$last = false;

        if(count($data["data"])==0)
            return "Ups No Data Found!";
		foreach ($data["data"] as $key => $value) {
			$value->last = true;
			$value->iscomplete=$value->status=="COMPLETE";
            $tmp[]=$value;
		}

		$templating = new \WMDE\VueJsTemplating\Templating;
		$vue = file_get_contents('./report/list_subassembly_dashboard.vue');
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

    public function data($type_subassy){
        try {
            $session = session();
            $model = new ReportItemGroupModel();

            $data = $model->db->query("select type_subassy , sub_assembly, sub_register_id, sub_assembly_desc, CASE WHEN unreported = 0 THEN 'COMPLETE' ELSE 'INCOMPLETE' END AS status
		  FROM 
			(
			  SELECT 
				sum(
				  CASE WHEN n2 IS NOT NULL THEN 1 ELSE 0 END
				) AS reported, 
				sum(CASE WHEN n2 IS NULL THEN 1 ELSE 0 END) AS unreported, 
				a.type_subassy, 
				a.sub_assembly, a.sub_register_id, a.sub_assembly_desc
			  FROM 
				(
				  SELECT 
					row_number() OVER (
					  PARTITION BY s.type_subassy, s.sub_assembly, 
					  s.part_number
					) AS n, 
					s.type_subassy, 
					s.sub_assembly, 
					s.part_number, s.sub_register_id, s.sub_assembly_desc 
				  FROM 
					subassembly s where s.flag=1
				) a 
				LEFT JOIN (
				  SELECT 
					row_number() OVER (
					  PARTITION BY s.section_id, s.subassy_id, 
					  s.partno_id
					) AS n2, 
					s.section_id, 
					s.subassy_id, 
					s.partno_id
				  FROM 
					report_item_rebuild s where s.flag=1 and s.type!=2
				) b ON b.section_id = a.type_subassy 
				AND b.subassy_id = a.sub_assembly 
				AND SUBSTRING_INDEX(b.partno_id, ' - ', 1) = a.part_number 
				AND b.n2 = a.n 
			  GROUP BY 
				a.type_subassy, 
				a.sub_assembly
			) s 	where type_subassy='$type_subassy'  GROUP BY 
			sub_assembly order by sub_register_id asc 
		 ");

                  
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