<?php
session_start();
$new = new Bigtoken();
$new->RefreshToken();
while(true){
$notif = $new->GetNotifications();
	if(isset($notif["data"])){
		foreach($notif["data"]["items"] as $items){
			$id = $items["metadata"]["from"]["id"];
			$confirm = $new->AcceptFriendship($id);
			if(isset($confirm["message"])){
				echo $confirm["message"]."\n";
			}
		}
	}
}
/*
$d = $new->GetDashboard();
file_put_contents("dashboard.json", json_encode($d, JSON_PRETTY_PRINT));
foreach($d["data"]["typical"] as $ty){
	switch($ty["mechanic"]){
		case "survey";
			$act = $new->GetSurveyAction($ty["option_uuid"]);
			file_put_contents("action.json", json_encode($act["data"]["content"]["questions"], JSON_PRETTY_PRINT));
			$uid = $act["data"]["option_uuid"];
			for($i=0; $i <= count($act["data"]["content"]["questions"]); $i++){
				$key = $act["data"]["content"]["questions"][$i]["data_point_key"];
				$value = $act["data"]["content"]["questions"][$i]["answers"][(0 ? 1:0)]["data_point_value"];
				$_SESSION["survey"][$i] = array("values" => array($value), "data_point_key" => $key);
				if($i <= count($act["data"]["content"]["questions"])){
					file_put_contents("survey.json", json_encode(array("data_point_values" => $_SESSION["survey"], "option_uuid" => $uid), JSON_PRETTY_PRINT));
					echo json_encode($new->PostSurveySubmit(array("data" => $_SESSION["survey"], "uid" => $uid)), JSON_PRETTY_PRINT);
					$_SESSION=array();
					session_unset();
				}else{
					$new->PostSurveyProgress(array("data" => $_SESSION["survey"], "uid" => $uid));
				}
			}
		break;
		case "question";
			$data = array("uid" => $ty["option_uuid"], "key" => $ty["content"]["data_point_key"], "value" => $ty["content"]["answers"][rand(0, count($ty["content"]["answers"]))]["data_point_value"]);
			echo json_encode($new->SubmitQuestion($data), JSON_PRETTY_PRINT);
		break;
		case "photo";
			echo json_encode($new->SubmitPhoto($ty["option_uuid"]), JSON_PRETTY_PRINT);
		break;
	}
}
*/
class Bigtoken
{
	function __construct(){
		if(file_exists("access.json")){
			$token = json_decode(file_get_contents("access.json"), true);
			$this->token = $token["token"]["access_token"];
			$this->refresh = $token["token"]["refresh_token"];
		}
	}
	public function GetDashboard(){
		return $this->Request(array("url" => "actions/dashboard", "token" => $this->token, "type" => "application/json"));
	}
	public function SubmitQuestion($data = array()){
		return $this->Request(array("url" => "actions/submit", "token" => $this->token, "post" => json_encode(array("data_point_values" => array(["values" => array($data["value"]), "data_point_key" => $data["key"]]), "option_uuid" => $data["uid"])), "type" => "application/json"));
	}
	public function SubmitPhoto($uid){
		return $this->Request(array("url" => "actions/submit", "token" => $this->token, "post" => http_build_query(array("path" => "https://s3.amazonaws.com/media.bigtoken.com/action/400x400_00CDMJgBl0p3UELv1uWPSzdBEXVeHxemu1GDSU09.jpeg", "option_uuid" => $uid)), "type" => "application/x-www-form-urlencoded"));
	}
	public function GetDynamic(){
		return $this->Request(array("url" => "actions/next?type=dynamic", "token" => $this->token, "type" => "application/json"));
	}
	public function GetBucket(){
		$bucket = $this->Request(array("url" => "users/bucket-reward?context=Individual", "token" => $this->token, "type" => "application/json"));
		return $bucket["bucket_reward"];
	}
	public function GetNotifications(){
		return $this->Request(array("url" => "users/notifications", "token" => $this->token, "type" => "application/json"));
	}
	public function AcceptFriendship($id){
		return $this->Request(array("url" => "friendship/accept", "token" => $this->token, "post" => "user_id=".$id, "type" => "application/x-www-form-urlencoded", "method" => "PATCH"));
	}
	public function GetSurveyAction($id){
		return $this->Request(array("url" => "actions?option_uuid=".$id, "token" => $this->token, "type" => "application/json"));
	}
	public function PostSurveyProgress($data=array()){
		$data = array("url" => "actions/survey-progress", "token" => $this->token, "post" => json_encode(array("data_point_values" => $data["data"], "option_uuid" => $data["uid"])), "type" => "application/json");
	}
	public function PostSurveySubmit($data=array()){
		$data = array("url" => "actions/submit", "token" => $this->token, "post" => json_encode(array("data_point_values" => $data["data"], "option_uuid" => $data["uid"])), "type" => "application/json");
	}
	public function RefreshToken(){
		$token = $this->Request(array("url" => "refresh-token", "token" => $this->refresh, "post" => "", "type" => "application/x-www-form-urlencoded"));
		if(isset($token["token"]["access_token"], $token["token"]["refresh_token"])){
			file_put_contents("access.json", json_encode($token, JSON_PRETTY_PRINT));
		}
	}
	public function Request($data=array()){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://api.bigtoken.com/".$data["url"]);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLOPT_PROXY, "159.65.128.217:3128");
    	curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
    	curl_setopt($ch, CURLOPT_USERAGENT, "okhttp/3.14.0");
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", "X-Srax-Big-Api-Version: 2", "Authorization: Bearer ".$data["token"], "Content-Type: ".$data["type"], "Connection: Keep-Alive", "Accept-Encoding: gzip"));
    	curl_setopt($ch, CURLOPT_COOKIEJAR, ".".$this->cookie);
   	 curl_setopt($ch, CURLOPT_COOKIEFILE, ".".$this->cookie);
		if(isset($data["post"]) && $data["post"] !==null){
       	curl_setopt($ch, CURLOPT_POST, 1);
       	curl_setopt($ch, CURLOPT_POSTFIELDS, $data["post"]);
 	   }
		if(isset($data["method"]) && $data["method"] !==null){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $data["method"]);
		}
    	$get["exec"] = json_decode(curl_exec($ch), true);
    	if(!curl_errno($ch)){
        	$get["status"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
   	 	$get["infourl"] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);  
   	 }
    	curl_close($ch);
    	return $get["exec"];
   }
}