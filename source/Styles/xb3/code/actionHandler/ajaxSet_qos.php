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
$jsConfig = $_REQUEST['configInfo'];
//$jsConfig = '{"ssid_number":"1", "ft":[["1","2"],["c","d"]], "target":"save_filter"}';

$arConfig = json_decode($jsConfig, true);
//print_r($arConfig);

if ("switch_callsignallog" == $arConfig['target'])
{
	setStr("Device.X_CISCO_COM_MTA.CallSignallingLogEnable", $arConfig['value'], true);
}
else if ("clear_callsignallog" == $arConfig['target'])
{
	setStr("Device.X_CISCO_COM_MTA.ClearCallSignallingLog", "true", true);
}
else if ("switch_DSXlog" == $arConfig['target'])
{
	setStr("Device.X_CISCO_COM_MTA.DSXLogEnable", $arConfig['value'], true);
}
else if ("clear_DSXlog" == $arConfig['target'])
{
	setStr("Device.X_CISCO_COM_MTA.ClearDSXLog", "true", true);
}

echo htmlspecialchars($jsConfig, ENT_NOQUOTES, 'UTF-8');

?>
