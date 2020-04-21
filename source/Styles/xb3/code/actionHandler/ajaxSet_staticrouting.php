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
<?php include('../includes/utility.php') ?>
<?php include('../includes/actionHandlerUtility.php') ?>
<?php
session_start();
if (!isset($_SESSION["loginuser"])) {
	echo '<script type="text/javascript">alert("Please Login First!"); location.href="index.php";</script>';
	exit(0);
}
// require "../includes/auth.php";

// Alias	string1
// DestIPAddress	192.168.100.1
// DestSubnetMask	255.255.255.0
// GatewayIPAddress	192.168.1.1
// active	true
// idex	10
// target	active

$response = array("status" => "success");
$validation = true;
if($validation) $validation = printableCharacters($_POST['Alias']);
if($validation) $validation = is_allowed_string($_POST['Alias']);
if($validation) $validation = validIPAddr($_POST['DestIPAddress']);
if($validation) $validation = validIPAddr($_POST['GatewayIPAddress']);
switch($_POST['target'])
{
	case "add":
		addTblObj("Device.Routing.Router.1.IPv4Forwarding.");
		$id = explode(",", getInstanceIds("Device.Routing.Router.1.IPv4Forwarding."));
		$i	= $id[count($id)-1];
		if($validation) {
			setStr("Device.Routing.Router.1.IPv4Forwarding.$i.Alias",htmlspecialchars($_POST['Alias']), false);
			setStr("Device.Routing.Router.1.IPv4Forwarding.$i.DestIPAddress", $_POST['DestIPAddress'], false);
			setStr("Device.Routing.Router.1.IPv4Forwarding.$i.DestSubnetMask", $_POST['DestSubnetMask'], false);
			setStr("Device.Routing.Router.1.IPv4Forwarding.$i.GatewayIPAddress", $_POST['GatewayIPAddress'], false);
			setStr("Device.Routing.Router.1.IPv4Forwarding.$i.Enable", "true", true);
		}
		/* check if this adding successed */
		if (getStr("Device.Routing.Router.1.IPv4Forwarding.$i.StaticRoute") !== "true") {
			$response["status"] = "failed";
			$response["msg"] = "Gateway IP must be reachable. Netmask must match route address.";

			/* delete the failed entry */
			delTblObj("Device.Routing.Router.1.IPv4Forwarding.$i.");
		}
		break;
	case "active":
		$i	= $_POST['idex'];
		setStr("Device.Routing.Router.1.IPv4Forwarding.$i.Enable", $_POST['active'], true);
		break;
	case "delete":
		$i	= $_POST['idex'];
		delTblObj("Device.Routing.Router.1.IPv4Forwarding.$i.");
		break;
	default: break;
}

header("Content-Type: application/json");
echo json_encode($response);

?>
