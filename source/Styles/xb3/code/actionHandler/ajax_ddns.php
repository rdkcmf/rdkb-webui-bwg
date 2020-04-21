<?php
/*
 If not stated otherwise in this file or this component's Licenses.txt file the
 following copyright and licenses apply:

 Copyright 2018 RDK Management

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
*/
?>
<?php include('../includes/actionHandlerUtility.php') ?>
<?php
session_start();
if (!isset($_SESSION["loginuser"]) || $_SESSION['loginuser'] != 'mso') {
	echo '<script type="text/javascript">alert("Please Login First!"); location.href="../index.php";</script>';
	exit(0);
}
function selectTable($sp) {
	$ids=explode(",",getInstanceIDs("Device.X_CISCO_COM_DDNS.Service."));
	foreach ($ids as $key=>$j) {
		$spArr[$j]=getStr("Device.X_CISCO_COM_DDNS.Service.".$j.".ServiceName");
		if(strcasecmp($sp,$spArr[$j]) == 0)
			return $j;
	}
	return 0;
}

if (isset($_POST['set'])){
	if (isValInArray($_POST['status'], array("Enabled", "Disabled"))){
		$status=(($_POST['status']=="Enabled")?"true":"false");
		setStr("Device.X_CISCO_COM_DDNS.Enable",$status,true);
		$status=getStr("Device.X_CISCO_COM_DDNS.Enable");
		$status=($status=="true")?"Enabled":"Disabled";
		header("Content-Type: application/json");
		echo htmlspecialchars(json_encode($status), ENT_NOQUOTES, 'UTF-8');
	//	echo json_encode("Disabled");
	}
}


if (isset($_POST['add'])){

	$sp=$_POST['sp'];
	$username=$_POST['username'];
	$password=$_POST['password'];
	$hostname=$_POST['hostname'];
	
	$result="";
	
	$id = selectTable($sp);
	$validation = true;
	if($validation) $validation = (strlen($username)<=64);	
	if($validation) $validation = (strlen($password)<=64);
	if($validation) $validation = (strlen($hostname)<=64);
	if($validation) $validation = printableCharacters($_POST['username']);
	if($validation) $validation = printableCharacters($_POST['password']);
	if($validation) $validation = printableCharacters($_POST['hostname']);
	if($validation) $validation = is_allowed_string($_POST['username']);
	if($validation) $validation = is_allowed_string($_POST['password']);
	if($validation) $validation = is_allowed_string_Hostname($_POST['hostname']);
	if($validation){
		if($id!=0) {
			setStr("Device.X_CISCO_COM_DDNS.Service.".$id.".ServiceName",$sp,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$id.".Username",$username,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$id.".Password",$password,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$id.".Domain",$hostname,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$id.".Enable","true",true);
			$result = "Success!";
		} else {
			$result = "Service Provider is not exist!";
		}
		header("Content-Type: application/json");
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES, 'UTF-8');
	}
/*	
	$ids=explode(",",getInstanceIDs("Device.X_CISCO_COM_DDNS.Service."));
	if (count($ids)==0) {	//no table, need test whether it equals 0
		addTblObj("Device.X_CISCO_COM_DDNS.Service.");
		$IDs=explode(",",getInstanceIDs("Device.X_CISCO_COM_DDNS.Service."));
		$i=$IDs[count($IDs)-1];
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".ServiceName",$sp,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Username",$username,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Password",$password,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Domain",$hostname,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Enable","true",false);
		echo json_encode("Success!");
	} else {
		foreach ($ids as $key=>$j) {
			$arrayService[$j]=getStr("Device.X_CISCO_COM_DDNS.Service.".$j.".ServiceName");
		}
		$result="";
		if (in_array($sp,$arrayService)) { $result.="Service Provider Name has been used!\n";}
		if ($result=="") {
			addTblObj("Device.X_CISCO_COM_DDNS.Service.");
			$IDs=explode(",",getInstanceIDs("Device.X_CISCO_COM_DDNS.Service."));
			$i=$IDs[count($IDs)-1];
			setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".ServiceName",$sp,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Username",$username,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Password",$password,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Domain",$hostname,false);
			setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Enable","true",true);
			$result="Success!";
		}
		echo json_encode($result);
	}
*/
}

if (isset($_POST['edit'])){
	$i=$_POST['ID'];
	$sp=$_POST['sp'];
	$username=$_POST['username'];
	$password=$_POST['password'];
	$hostname=$_POST['hostname'];
	$validation = true;
	if($validation) $validation = (strlen($username)<=64);	
	if($validation) $validation = (strlen($password)<=64);
	if($validation) $validation = (strlen($hostname)<=64);
	if($validation) $validation = validId($_POST['ID']);
	if($validation) $validation = printableCharacters($_POST['username']);
	if($validation) $validation = printableCharacters($_POST['password']);
	if($validation) $validation = printableCharacters($_POST['hostname']);
	if($validation) $validation = is_allowed_string($_POST['username']);
	if($validation) $validation = is_allowed_string($_POST['password']);
	if($validation) $validation = is_allowed_string_Hostname($_POST['hostname']);
	if($validation){
		//delete entry - we can't edit on same index so delete on one index and update on other index
		if(strcasecmp($sp,getStr("Device.X_CISCO_COM_DDNS.Service.".$i.".ServiceName")) != 0){
			setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Enable","false",true);
		}

		$i = selectTable($sp);

		//setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".ServiceName",$sp,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Username",$username,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Password",$password,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Domain",$hostname,false);
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Enable","true",true);
		$result="Success!";
		
		header("Content-Type: application/json");
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES, 'UTF-8');
	}
}

if (isset($_POST['del'])){
	$validation = true;
	if($validation) $validation = validId($_POST['del']);
	if($validation){
		$i=$_POST['del'];
		setStr("Device.X_CISCO_COM_DDNS.Service.".$i.".Enable","false",true);
	}
}
?>
