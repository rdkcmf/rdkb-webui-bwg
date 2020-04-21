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
<?php include('includes/header.php'); ?>

<!-- $Id: firewall_settings.php 3158 2010-01-08 23:32:05Z slemoine $ -->

<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?><script type="text/javascript">
$(document).ready(function() {
    comcast.page.init("Gateway > Connection > MTA > Status", "nav-line-status");
});
</script>
<?php
	$LineRegister = getStr('Device.X_RDKCENTRAL-COM_MTA.LineRegisterStatus');
	//example "Start,Start,Start,Start,Complete,Complete,Start,Start"
	$LineRegisterStatus = explode(',', $LineRegister);
?>
<div id="content">
	<h1>Gateway > Connection > MTA > Status</h1>
	<div id="educational-tip">
			<p class="tip">Information related to the MTA Line Status and MTA Status.</p>
	</div>
	<div class="module forms">
		<h2>MTA Line Status</h2>
		<div class="form-row">
			<span class="readonlyLabel">Line 1 Status:</span>
			<span class="value"><?php echo $LineRegisterStatus[0];?></span>
		</div>
		<div class="form-row odd">
			<span class="readonlyLabel">Line 2 Status:</span>
			<span class="value"><?php echo $LineRegisterStatus[1];?></span>
		</div>
	</div>
	<div class="module forms">
		<h2>eMTA Status</h2>
		<div class="form-row">
			<span class="readonlyLabel">Telephony-DHCPv4 Status:</span>
			<span class="value"><?php echo getStr('Device.X_RDKCENTRAL-COM_MTA.Ipv4DhcpStatus');?></span>
		</div>
		<div class="form-row odd">
			<span class="readonlyLabel">Telephony-DHCPv6 Status:</span>
			<span class="value"><?php echo getStr('Device.X_RDKCENTRAL-COM_MTA.Ipv6DhcpStatus');?></span>
		</div>
		<div class="form-row">
			<span class="readonlyLabel">Telephony-Reg Config File Status:</span>
			<span class="value"><?php echo getStr('Device.X_RDKCENTRAL-COM_MTA.ConfigFileStatus');?></span>
		</div>
	</div>
</div> <!-- end .module -->

<!-- Page Specific Script -->
<?php include('includes/footer.php'); ?>
