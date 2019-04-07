<?php
$ua = file(__DIR__."/email.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$i=0;
foreach($ua as $email){
	$i++;
	$f = array("email" => $email, "password" => "TUyulcrypto23@*123#", "referral_id" => "CKNNNAUIL");
	$data = new Bigtoken($f);
	$signup = $data->Signup();
	if(isset($signup["status"]) && $signup["status"] == 200){
		echo "[".$i."] Signup Success ~ User ID[".$signup["exec"]["data"]["user_id"]."]\n";
		sleep(5);
	}else{
		echo $signup["exec"]["error"]["errors"]["email"]["message"]."\n";
	}
}
class Bigtoken
{
	protected $email;
	protected $password;
	protected $referral_id;
	function __construct($data=array()){
		$this->email = $data["email"];
		$this->password = $data["password"];
		$this->referral_id = $data["referral_id"];
	}
	public function Signup(){
		$data = http_build_query(array("email" => $this->email, "password" => $this->password, "referral_id" => $this->referral_id, "monetize" => 1));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.bigtoken.com/signup");
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLOPT_USERAGENT, "okhttp/3.14.0");
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "Content-Type: application/x-www-form-urlencoded", "Accept-Encoding: gzip", "Content-Length: ".strlen($data)));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    	$get["exec"] = json_decode(curl_exec($ch), true);
    	if(!curl_errno($ch)){
        	$get["status"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   	 	$get["infourl"] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);  
		}
		curl_close($ch);
    	return $get;
	}
}