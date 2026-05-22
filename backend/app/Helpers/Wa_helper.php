<?php
	function sendWa($number, $message){
		try {
	
			/* $token = "8L5AU1dEucZQf6d36DFuAbmmNq6CpJc9Lkh8vR3jhYVa6a1KaH";
			$message = $message."\r\nMSG Ref: ".uniqid();
			
			$phone= $number;
		
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://app.ruangwa.id/api/send_message',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => 'token='.$token.'&number='.$phone.'&message='.$message,
			));
			$response = curl_exec($curl);
			curl_close($curl);
			return $response; */
			
			$token = "ut@xFGbs#NTkJfs#JMUF";
			$message = $message."\r\nMSG Ref: ".uniqid();
			
			$phone= $number;
		
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://api.fonnte.com/send',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => array(
					'target' => $phone,
					'message' => $message
					)//'token='.$token.'&number='.$phone.'&message='.$message,
				,
				CURLOPT_HTTPHEADER => array(
					'Authorization: ut@xFGbs#NTkJfs#JMUF'
				)
			));
			$response = curl_exec($curl);
			curl_close($curl);
			return $response;
		} catch (Exception $th) {
			return [
				"result"=> false,
				"message"=> $th
			];
		}
	}

?>