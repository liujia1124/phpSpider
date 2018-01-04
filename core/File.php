<?php
	function downloadFile($url){
		return file_get_contents($url);
	}
	
	function uploadFile($name,$data){
		
		$url   = PICTURE_CHANGE_API;;
		$time  = time();
		$token = md5($name.$time);
		$data = base64_encode($data);
		
		$post_data = ['data'=>$data, 'name'=>$name, 'time'=>$time, 'token'=>$token];
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data );
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;

		
	}
	
	
	
	
	