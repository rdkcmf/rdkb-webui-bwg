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
<?php
session_start();
if (!isset($_SESSION["loginuser"])) {
	echo '<script type="text/javascript">alert("Please Login First!"); location.href="../index.php";</script>';
	exit(0);
}
$jsConfig = $_REQUEST['configInfo'];
//$jsConfig = '{"ext_mac":"10:11:22:33:44:66", "dis_mac":"20:11:22:33:44:77,00:11:22:33:44:55,10:11:22:33:44:66"}';

$arConfig = json_decode($jsConfig, true);
//print_r($arConfig);

setStr("Device.MoCA.X_CISCO_COM_WiFi_Extender.X_CISCO_COM_DISCONNECT_CLIENT", $arConfig['dis_mac'], true);

echo htmlspecialchars($jsConfig, ENT_NOQUOTES, 'UTF-8');	

?>
