<?php

function uploadFile($doc, $error){
	if(!empty($doc)){
		try {
			if (!$doc->hasMoved()) {
				$name = $doc->getRandomName();
				$realName = $doc->getName();
				$filepath = "./uploads/".date("Ym/d/");
				//$data["filepath"] = $name."+++".$realName;
				//$data['name'] = $realName;
				$doc->move($filepath, $name);
				return [
					"filepath"=>$filepath.$name,
					"filename"=>$realName
				];
			}
			else{
			
				return false;
			}
		} catch (\Throwable $err) {
			return false;
		}
	}
}