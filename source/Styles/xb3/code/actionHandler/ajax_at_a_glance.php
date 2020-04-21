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
if (!isset($_SESSION["loginuser"])) {
	echo '<script type="text/javascript">alert("Please Login First!"); location.href="../index.php";</script>';
	exit(0);
}
header("Content-Type: application/json");
/*
 *	bridge-static 		> Advanced Bridge Mode
 *	router 				> Bridge Mode Disabled
 *	full-bridge-static 	> Basic Bridge Mode
 */
if (isset($_POST['Bridge'])){
	$bridgeMode=$_POST['bridgeMode'];
	if(isValInArray($bridgeMode, array('router', 'bridge-static', 'full-bridge-static'))){
		setStr("Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanMode",$bridgeMode,true);		
		//20140523
		//set LanManagementEntry_ApplySettings after change LanManagementEntry table
		setStr("Device.X_CISCO_COM_DeviceControl.LanManagementEntry_ApplySettings", "true", true);
	}
	$bridgeModel=getStr("Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanMode");
	echo htmlspecialchars(json_encode($bridgeModel), ENT_NOQUOTES, 'UTF-8');
}

if (isset($_POST['IGMP'])){
	$IGMPEnable=($_POST['IGMPEnable']=="Enabled"?"true":"false");
	setStr("Device.X_CISCO_COM_DeviceControl.IGMPSnoopingEnable",$IGMPEnable,true);
	$IGMPModel=(getStr("Device.X_CISCO_COM_DeviceControl.IGMPSnoopingEnable")=="true"?"Enabled":"Disabled");
	echo htmlspecialchars(json_encode($IGMPModel), ENT_NOQUOTES, 'UTF-8');
}
?>
