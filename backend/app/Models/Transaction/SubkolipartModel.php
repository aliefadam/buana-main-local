<?php namespace App\Models\Transaction;
 
ini_set('memory_limit', '200M');
use CodeIgniter\Model;
 
class SubkolipartModel extends Model
{
    protected $table = 'subkoli_part';
    protected $returnType     = 'object';
    protected $primaryKey = 'id';
    protected $allowedFields = ["id","subkoli_id", "koli_id", "item_id", "bom_header_id","bom_receive_item_id", "photo", "photo2", "photo3", "created_date","created_by","modified_date","modified_by", "flag", "qty","bom_receive_id","container_id"];
	
	protected function initialize()
    {
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
        $db = $this->db;
		$where = where($json->filter ?? [], $json->filterType ?? [], $json->filterCondition ?? []);
        if(trim($where)=='')
            $where = 'where s.flag = 1';
        else
            $where .= ' and s.flag = 1';
		$q = "select ROW_NUMBER() OVER() AS no, s.* from (select c.name as created_by_name, st.bom_spb_id, m.name as modified_by_name, date(s.created_date) as crea_date, date(s.modified_date) as mod_date, i.item_name, i.specification,
			".join(',', array_map(array($this, 'addPrefix'), $this->allowedFields)).", bh.description as bom_desc
			from `$this->table` s
			left join m_item i on i.id = s.item_id
			left join bom_receive_header bh on bh.id = s.bom_header_id
			left join users c on c.id = s.created_by
			left join users m on m.id = s.modified_by
			left join subkoli si on si.id=s.subkoli_id
			left join stock st on st.subkoli_part_id = s.id
            where s.item_id is not null) s";
        $query = $db->query("select * from
		($q)s $where $order ". ($limit==-1 ? '' : "limit $offset, $limit"));
        
		if(!$query)
			return [false, $this->db->error(), 0]; 
	
		$totalQuery = $db->query("select count(*) as total from ($q)s $where");
		if(!$totalQuery)
			return [false, $this->db->error()]; 
		$totalQuery = $totalQuery->getResult();
		return [true, $query->getResult(), $totalQuery[0]->total];
    }

	function image($id){
		if($id=='null')
			return [true, ''];
		$q = "select photo3 from subkoli_part where id = $id and flag=1";
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		$data =  $query->getResult();
		$img = $data[0]->photo3;
		$tmp = array();
		$type = 'image/jpeg';
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
			if(str_contains($tmp[0], 'png')){
				imagejpeg($thumb);
				$type = 'image/png';
			}
			else
				imagepng($thumb);
			$img = ob_get_clean();
			ob_end_clean();
			imagedestroy($thumb);
		
		}
		return [true, $img, $type];//$tmp[0].'base64,'.base64_encode($img)];
	}

	function photo($id){
		if($id=='null')
			return [true, ''];
		$q = "select photo from subkoli_part where subkoli_id = $id and photo is not null and photo != '' and flag=1 order by coalesce(modified_date, created_date) desc limit 1";
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		$data =  $query->getResult();
		$img = $data[0]->photo;
		$tmp = array();
		$type = 'image/jpeg';
		if ($img) {
			// Content type
			$tmp = explode('base64,', $img);
			//print($tmp[1]);
			$data = base64_decode($tmp[1]);
			$im = imagecreatefromstring($data);
			$width = imagesx($im);
			$height = imagesy($im);
			$newwidth = 150;
			$percent = $newwidth/$width*100;
			$newheight = $height * $percent/100;
		
			$thumb = imagecreatetruecolor($newwidth, $newheight);
		
			// Resize
			imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
			// Output
			ob_start();
			if(str_contains($tmp[0], 'png')){
				imagejpeg($thumb);
				$type = 'image/png';
			}
			else
				imagepng($thumb);
			$img = ob_get_clean();
			ob_end_clean();
			imagedestroy($thumb);
		
		}
		return [true, $img, $type];//$tmp[0].'base64,'.base64_encode($img)];
	}

	function photo2($id){
		if($id=='null')
			return [true, ''];
		$q = "select photo2 from subkoli_part where koli_id = $id and photo2 is not null and photo2 != '' and flag=1 order by coalesce(modified_date, created_date) desc limit 1";
		$query = $this->db->query($q);
		if(!$query)
			return [false, $this->db->error()];
		$data =  $query->getResult();
		$img = $data[0]->photo2;
		$tmp = array();
		$type = 'image/jpeg';
		if ($img) {
			// Content type
			$tmp = explode('base64,', $img);
			//print($tmp[1]);
			$data = base64_decode($tmp[1]);
			$im = imagecreatefromstring($data);
			$width = imagesx($im);
			$height = imagesy($im);
			$newwidth = 150;
			$percent = $newwidth/$width*100;
			$newheight = $height * $percent/100;
		
			$thumb = imagecreatetruecolor($newwidth, $newheight);
		
			// Resize
			imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		
			// Output
			ob_start();
			if(str_contains($tmp[0], 'png')){
				imagejpeg($thumb);
				$type = 'image/png';
			}
			else
				imagepng($thumb);
			$img = ob_get_clean();
			ob_end_clean();
			imagedestroy($thumb);
		
		}
		return [true, $img, $type];//$tmp[0].'base64,'.base64_encode($img)];
	}
}