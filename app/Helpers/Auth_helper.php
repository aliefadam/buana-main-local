<?php
	function checkAuth(){
        $session = session();
		$s = $session->get();
        $authorization = false;
		if(!isset($s["userid"]))
            $authorization = false;
        else
            $authorization = true;
        
        
        if(isset(apache_request_headers()["Authorization"]))
            $authorization = apache_request_headers()["Authorization"];
		if (isset(apache_request_headers()["Authorizations"]))
			$authorization = apache_request_headers()["Authorizations"];
        $host = apache_request_headers()["Host"];
        if($authorization!='19e4002f-f824-4dca-afaf-73fa6526ea33'){
            $authorization = false;
        }
        return $authorization;
	}

	function checkLogin(){
        $session = session();
		$s = $session->get();
        $authorization = false;
		if(!isset($s["userid"]))
            $authorization = false;
        else
            $authorization = true;
        
        return $authorization;
	}

?>