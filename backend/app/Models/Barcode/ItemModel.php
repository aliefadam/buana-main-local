<?php namespace App\Models\Barcode;
 
use CodeIgniter\Model;
 
class ItemModel extends Model
{
    protected $table = 'tracking_item';
    protected $returnType     = 'object';
    protected $primaryKey = 'barcode';
    protected $allowedFields = ["id", "barcode","created_date","ln","lt","process", "img", "parent_id"];
	
	protected function initialize()
    {	
		$this->dev = false;
		if(isset($_REQUEST['_app_root']))
			if(preg_match("/dev$/i", $_REQUEST['_app_root']))
				$this->dev = true;
		if($this->dev)
			$this->db = db_connect('dev');
        else
			$this->db = db_connect();
		$this->db->query("SET time_zone = '+07:00'");
    }

    function addPrefix($field){
        return 's.'.$field;
    }

    function read($json){
        helper(['Query_helper']);
        $limit = $json->limit ?? 10;
        $offset = $json->offset ?? 0;
        $sortBy = $json->sortBy ?? array();
        $sortDesc = $json->sortDesc ?? array();
		$order = order($sortBy, $sortDesc);
        $db = db_connect();
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        /* if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1'; */
		$allowedFields = ["id", "barcode","created_date","ln","lt","process", "parent_id"];
		$q = "select 
			".join(',', array_map(array($this, 'addPrefix'), $allowedFields))."
			from `$this->table` s
			";
		
		if(isset($json->debug))
			return [true, "select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"), 0];
        $query = $db->query("select s.*,
		p.part_name, p.type, p.qty, p.uom, p.application, (select a.barcode from tracking_item a where a.id = s.parent_id limit 1) as box, p.group_name, p.area
		 from
		($q)s left join tracking_m_part p on p.barcode = s.barcode $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s left join tracking_m_part p on p.barcode = s.barcode $where");
		if(!$totalQuery)
			return [false, $this->db->error(), 0]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }

	function report($json){
		$q = "select t.barcode, td.created_date as dismantling_date, tp.created_date as packing_date, ts.created_date as storage_date,
		tb.lt, tb.ln,
		mp.part_name, mp.type, mp.qty, mp.uom, mp.application, mp.group_name, mp.area,
		tbb.barcode as box_no, td.id as id_dismantling, tp.id as id_packing, tp.parent_id as id_box, ts.parent_id as id_storage,
		(ROW_NUMBER() OVER(Partition by barcode)) as cnt
		
		from (select distinct barcode from tracking_item) t
		
		left join
		
		(
			select * from (
				select *, ROW_NUMBER() OVER(Partition by barcode ORDER BY created_date desc) as cnt from tracking_item where process = 'dismantle'
			)s where s.cnt = 1
		)td on td.barcode = t.barcode
		
		left join
		
		(
			select * from (
				select *, ROW_NUMBER() OVER(Partition by barcode ORDER BY created_date desc) as cnt from tracking_item_all where process = 'packing_part'
			)s where s.cnt = 1
		)tp on tp.barcode = t.barcode
		
		
		left join
		
		(
			select * from (
				select *, ROW_NUMBER() OVER(Partition by barcode ORDER BY created_date desc) as cnt from tracking_item_all where process = 'storage_part'
			)s where s.cnt = 1
		)ts on ts.barcode = t.barcode
		
		left join 
		
		(
			select * from (
				select *, ROW_NUMBER() OVER(Partition by barcode ORDER BY created_date desc) as cnt from tracking_item where process = 'packing'
			)s
		)tbb on tbb.id = tp.parent_id
		
		left join 
		
		(
			select * from (
				select *, ROW_NUMBER() OVER(Partition by barcode ORDER BY created_date desc) as cnt from tracking_item where process = 'storage'
			)s where s.cnt = 1
		)tb on tb.barcode = tbb.barcode
		
		left join (select distinct barcode, part_name, type, qty, uom, application, p.group_name, p.area from tracking_m_part p) mp on mp.barcode = t.barcode
		where 1 = 1 and mp.part_name is not null";

		if(isset($json->group))
			$q .= " and mp.group_name = '".$json->group."'";
		if(isset($json->area))
			$q .= " and mp.area = '".$json->area."'";
		$query = $this->db->query('select * from ('.$q.')s where cnt = 1');
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

	function infopercent($json){
		$q = "select count(i.barcode) as c, process, mp.group_name, (select count(*) from tracking_m_part where group_name = mp.group_name) as part_count from (select distinct barcode, process from tracking_item_all)i left join tracking_m_part mp on mp.barcode = i.barcode group by mp.group_name, process";

		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
	
	function infoarea($json){
		$q = "select count(i.barcode) as c, i.process, mp.group_name, mp.area, 
		(
			select count(*) from (
				select group_name, barcode, area from tracking_m_part group by group_name, barcode, area
			)s where area = mp.area and group_name = mp.group_name
		) as part_count 
		from (select distinct barcode, process from tracking_item_all)i left join tracking_m_part mp on mp.barcode = i.barcode group by mp.group_name, i.process, mp.area
		";

		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}

	function imagebarcode($barcode){
		if($barcode=='null')
			return [true, ''];
		$q = "select img from tracking_item where barcode = '$barcode' limit 1";
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		$data =  $query->getResult();
		$img = $data[0]->img;
		$tmp = array();
		if ($img) {
			if($img == '0'){
				$im = imagecreatetruecolor(50, 50);
				$text_color = imagecolorallocate($im, 233, 14, 91);
				imagestring($im, 1, 5, 5,  'Photo\nUnavailable', $text_color);
				
				ob_start();
				
				// Output the image
				imagejpeg($im);
				
				$img = ob_get_clean();
				ob_end_clean();
				
				// Free up memory
				imagedestroy($im);
			}
			else{
			// Content type
				$tmp = explode('base64,', $img);
				$data = base64_decode($tmp[1]);
				$im = imagecreatefromstring($data);
				$width = imagesx($im);
				$height = imagesy($im);
				$newwidth = 50;
				$percent = $newwidth/$width*100;
				$newheight = $height * $percent/100;
			
				$thumb = imagecreatetruecolor($newwidth, $newheight);
			
				// Resize
				imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			
				// Output
				ob_start();
				imagejpeg($thumb);
				$img = ob_get_clean();
				ob_end_clean();
				imagedestroy($thumb);
			}
		}
		return [true, $img];//$tmp[0].'base64,'.base64_encode($img)];
	}

	function image($id){
		if($id=='null')
			return [true, ''];
		$q = "select img from tracking_item where id = $id";
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		$data =  $query->getResult();
		$img = $data[0]->img;
		$tmp = array();
		if ($img) {
		
			// Content type
			$tmp = explode('base64,', $img);
			$data = base64_decode($tmp[1]);
			$im = imagecreatefromstring($data);
			$width = imagesx($im);
			$height = imagesy($im);
			$newwidth = 50;
			$percent = $newwidth/$width*100;
			$newheight = $height * $percent/100;
		
			$thumb = imagecreatetruecolor($newwidth, $newheight);
		
			// Resize
			imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
			// Output
			ob_start();
			imagejpeg($thumb);
			$img = ob_get_clean();
			ob_end_clean();
			imagedestroy($thumb);
		}
		return [true, $img];//$tmp[0].'base64,'.base64_encode($img)];
	}

	function imagefull($id){
		if($id=='null')
			return [true, ''];
		$q = "select img from tracking_item where id = $id";
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		$data =  $query->getResult();
		$img = $data[0]->img;
		/* $tmp = array();
		if ($img) {
		
			// Content type
			$tmp = explode('base64,', $img);
			$data = base64_decode($tmp[1]);
			$im = imagecreatefromstring($data);
			$width = imagesx($im);
			$height = imagesy($im);
			$newwidth = 150;
			$percent = $newwidth/$width*100;
			$newheight = $height * $percent/100;
		
			$thumb = imagecreatetruecolor($width, $height);
		
			// Resize
			imagecopyresized($thumb, $im, 0, 0, 0, 0, $width, $height, $width, $height);
		
			// Output
			ob_start();
			imagejpeg($thumb);
			$img = ob_get_clean();
			ob_end_clean();
			imagedestroy($thumb);
		} */
		return [true, $img];//$tmp[0].'base64,'.base64_encode($img)];
	}

	function partnotregistered($json){
		/* $q = "select s.barcode, p.part_name from (select distinct i.barcode from tracking_item i left join tracking_m_part mp on mp.barcode = i.barcode where mp.group_name is null and process = 'dismantle')s
			left join tracking_m_part p on p.barcode = s.barcode
			left join tracking_item i on i.barcode = s.barcode
		"; */
		$q = "select distinct i.barcode from tracking_item i where process = 'dismantle' and barcode not in (select distinct barcode from tracking_m_part)";

		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		return [true, $query->getResult()];
	}
}