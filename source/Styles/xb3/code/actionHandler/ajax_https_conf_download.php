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
/**********************************************************************
   Copyright [2014] [Cisco Systems, Inc.]
 
   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at
 
       http://www.apache.org/licenses/LICENSE-2.0
 
   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
**********************************************************************/
?>
<?php
session_start();
if (!isset($_SESSION["loginuser"])) {
	echo '<script type="text/javascript">alert("Please Login First!"); location.href="index.php";</script>';
	exit(0);
}
/**
 * ajax_tftp_conf_download.php
 *
 * Action handler via AJAX to tftp download parameters and trigger the download
 * process, finally this would apply the downloaded configuration to device as
 * well. JSON response back to client would indicate the action result.
 *
 * Author:	Nobel Huang
 * Date:	Sep 6, 2013
 */

$httpsFilename = $_POST['https_filename'];

if (!empty($httpsFilename))
{
	if(preg_match('/^[0-9A-Za-z]+\.[0-9A-Za-z]+$/', $httpsFilename) != 1) {
		$response = array('status'=>'failed', 'msg'=>'InvalidFileName');
		goto l_output;
	}

	$dStatus = getStr('Device.X_CISCO_COM_FileTransfer.Status');
	/* allow only one download process */
	if (strcmp($dStatus, 'InProgress') == 0) {
		$response = array('status'=>'failed', 'msg'=>'InProgress');
		goto l_output;
	}

	/* set download parameters and initiate the download */
	setStr('Device.X_CISCO_COM_FileTransfer.Protocol', 'HTTPS', false);
	//setStr('Device.X_CISCO_COM_FileTransfer.Server', $httpsServer, false);
	setStr('Device.X_CISCO_COM_FileTransfer.FileName', $httpsFilename, false);
	setStr('Device.X_CISCO_COM_FileTransfer.Action', 'Download', true);		// trigger download

	$sleepCount = 60; // wait for 60 seconds and break
	do {
		sleep(1);
		$dStatus = getStr('Device.X_CISCO_COM_FileTransfer.Status');
	}
	while (strcmp($dStatus, 'InProgress') == 0 && (--$sleepCount) > 0);

	if ($dStatus === 'Complete')
	{
		/* Try to apply the downloaded configuration */
		$ret = setStr('Device.X_CISCO_COM_TrueStaticIP.ConfigApply', 'true', true);
		if ($ret) $response = array('status'=>'success','msg'=>'ConfigurationSuccess');
		else $response = array('status'=>'failed: apply config');
	}
	else if ($dStatus === 'InProgress')
	{
		$response = array('status'=>'failed','msg'=>'InProgressFailed');
	}
	else
	{
		$response = array('status'=>'failed','msg'=>$dStatus);
	}
}
else
{
	$response = array('status'=>'failed');
}

l_output:
header("Content-Type: application/json");
echo json_encode($response);

?>
