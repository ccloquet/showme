<?php
	require_once('../shared/params.php');	 
	require_once('../shared/manage_keys.php');		 

	if (!isset($_POST['to']))  		returns_error();
	if (!ctype_digit($_POST['to']))		returns_error();

	if (!isset($_POST['key'])) 		returns_error();
	if (!verify_key($_POST['key'])) 	returns_error();

	if (!isset($_POST['userid'])) 		returns_error();
	if (!validate_user($_POST['userid']))	returns_error();

	$to   		= '+' . $_POST['to'];
	$key  		= $_POST['key'];	 
	$userid 	= $_POST['userid']; 
	$sms_apikey 	= $params[$userid]['sms_apikey'];

	$body 		= 'Open the link to take a picture. Ouvrez le lien pour prendre une photo. Open de link om een foto te nemen. ' . BASE_URL . '?key=' . $key;

	send_clickatell($sms_apikey, $body, $to); 

	function send_clickatell($sms_apikey, $body, $to) // only returns the result of the first one
	{
		$payload 		= json_encode( ["content"=> $body,"to"=>[$to]] );
		$url_clickatell     	= 'https://platform.clickatell.com/messages';
		$headers 		= ["Content-Type: application/json","Accept: application/json", "Authorization: " . $sms_apikey];
		$ch      		= curl_init();

		curl_setopt($ch, CURLOPT_URL, 			$url_clickatell);
		curl_setopt($ch, CURLOPT_HTTPHEADER, 		$headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	1);
		curl_setopt($ch, CURLOPT_POST, 			TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 		$payload);
	
		$ret 			= curl_exec($ch);
		curl_close($ch);

		echo $ret;
		//$ret     = json_decode($ret, true);
		//if ($ret['messages'][0]['accepted'] != true) 	return false;
	}

	function returns_error()
	{
		die('ERROR');
	}
?>