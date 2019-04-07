<?php
if(isset($argv[1], $argv[2])){
	$user = $argv[1];
	$pass = $argv[2];
    if (! function_exists('imap_open')) {
        echo "IMAP is not configured.";
        exit();
    } else {
    	while(true){
        $connection = imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $user, $pass) or die('Cannot connect to Gmail: ' . imap_last_error());
        $emailData = imap_search($connection, 'SUBJECT "Confirmation needed: Your BIGtoken email address"');
        if (! empty($emailData)) {
        	rsort($emailData);
        	$i=0;
            foreach ($emailData as $emailIdent) {
            	$i++;
                $overview = imap_fetch_overview($connection, $emailIdent, 0);
                $message = imap_qprint(imap_body($connection, $emailIdent));
                preg_match('/text-decoration:none;" href="(.*?)">Confirm now/', $message, $url);
                $date = date("d F, Y", strtotime($overview[0]->date));
            	$ul = GetCode($url[1]);
            	preg_match('/code=(.*?)&/', $ul, $cod);
            	$email = explode('email=', $ul);
            	if(isset($cod[1])){
            		$code = $cod[1];
            	}
            	if(isset($email[1])){
            		$mail = $email[1];
            	}
            	if(isset($code, $mail)){
            		@exec('curl -s -L --interface "eth0" -A "okhttp/3.14.0" --tlsv1.2 -X "POST" --url "https://api.bigtoken.com/signup/email-verification" --data "email='.$mail.'&verification_code='.$code.'" -H "Accept: application/json" -H "Content-Type: application/x-www-form-urlencoded" -H "Accept-Encoding: gzip" --compressed');
            		imap_delete($connection, $emailIdent);
            		imap_expunge($connection);
            		echo "[".$i."] Success Verified Email [".$mail."]\n";
            	}
            } 
        } 
        imap_close($connection);
        }
    }
}
function GetCode($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLOPT_USERAGENT, "okhttp/3.14.0");
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "Accept-Encoding: gzip", "application/x-www-form-urlencoded"));
    	curl_exec($ch);
    	if(!curl_errno($ch)){
   	 	$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);  
   	 }
    	curl_close($ch);
    	return $url;
   }