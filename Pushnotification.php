<?php 
function deveicenotify($params=array()) 
{
	$db = getDB();
	$request_created			=	date('Y-m-d H:i:s');
	$count=0;	
	
	$alert = $params['msg'];
	$title = $params['title'];
	$notification_icon=$params['notification_icon'];
	
	if ($params['device_type'] == 'ios') {
		   $dir_path=THISPATH.'/push_ios/';
		   $apnsHost = 'gateway.sandbox.push.apple.com';
		   $apnsCert = $dir_path.'apns-cert1.pem';
		   $apnsPort = 2195;
		   $apnsPass = 'kechain access password'; /// Need to be change
		   $token = $params['device_token'];
		   

		   /* if (file_exists($apnsCert)) {
			 echo "The file $apnsCert exists";
			 } else {
			 echo "The file $apnsCert does not exist";
			 } */

		   $payload['aps'] = array('alert' => $alert, 'sound' => 'default');
		   $output = json_encode($payload);
		   $token = pack('H*', str_replace(' ', '', $token));
		   $apnsMessage = chr(0) . chr(0) . chr(32) . $token . chr(0) . chr(strlen($output)) . $output;
		   try {
			   $streamContext = stream_context_create();
			   stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
			   //stream_context_set_option($streamContext,'ssl','passphrase',$apnsPass);
			   //stream_context_set_option($streamContext,'ssl','cafile','ios_development.cer');

			   $apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
			   $status='';
			   if (!$apns) {
				   //die("Failed to connect: $error $errorString");
				   $status='Failed';
			   } else {
				   $status='Send';
			   }
			   fwrite($apns, $apnsMessage);
			   fclose($apns);
			   
			   $notification_stmt_qry=" INSERT INTO push_notification_history (device_type,device_token,notification_icon,msg,notification_created,status) 
			VALUES ('".$params['device_type']."','".$params['device_token']."','".$params['notification_icon']."','".$params['msg']."','".$request_created."','".$status."') ";
	$notification_stmt = $db->prepare($notification_stmt_qry);
	$notification_stmt->execute();
			   
		   } catch (Exception $e) {
			   echo 'Caught exception: ', $e->getMessage(), "\n";
		   }
	   } else {
		   $appID = 'GCM Key'; /// Need to be change
		   $registrationIdsArray = array($params['device_token']);

		   $headers = array("Content-Type:" . "application/json", "Authorization:" . "key=" . $appID);

		   $data = array(
			   'data' => array(
				   'payload' => array(
					   "android" => array(
						   "title" => $title,
						   "alert" => $alert,
						   "icon" => $notification_icon,
						   "sound"=> "door_bell",
						   "vibrate"=> true,
						   "win_id"=>1
					   )
				   ),
			   ),
			   'registration_ids' => $registrationIdsArray
		   );

		   $ch = curl_init();

		   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		   curl_setopt($ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send");
		   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

		   $response = curl_exec($ch);
		   curl_close($ch);
			$status='Send';
		   $notification_stmt_qry=" INSERT INTO '' () VALUES () ";
			$notification_stmt = $db->prepare($notification_stmt_qry);
			$notification_stmt->execute();
		   //return $response;
   }
   $count++;
}
?>