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
<?php include('includes/utility.php'); ?>
<!-- $Id: wifi.php 3159 2010-01-11 20:10:58Z slemoine $ -->

<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?>

<script type="text/javascript">
$(document).ready(function() {
    comcast.page.init("Gateway > Hardware > WiFi", "nav-wifi");
});
</script>
<?php

$wifi_param = array(
	//php_getstr
	"1_Enable"	=> "Device.WiFi.Radio.1.Enable",
	"1_BSSID"	=> "Device.WiFi.SSID.1.BSSID",
	"2_Enable"	=> "Device.WiFi.Radio.2.Enable",
	"2_BSSID"	=> "Device.WiFi.SSID.2.BSSID",
	"1_Status"      => "Device.WiFi.Radio.1.Status",
        "2_Status"      => "Device.WiFi.Radio.2.Status",
	"1_SSID"	=> "Device.WiFi.SSID.1.Enable",
	"2_SSID"	=> "Device.WiFi.SSID.2.Enable",
	//getStr
	"1_RadioUpTime"	=> "Device.WiFi.Radio.1.X_COMCAST_COM_RadioUpTime",
	"2_RadioUpTime"	=> "Device.WiFi.Radio.2.X_COMCAST_COM_RadioUpTime",
	);
$wifi_value = KeyExtGet("Device.WiFi.", $wifi_param);

//wrap for PSM mode
if ("Enabled" == $_SESSION["psmMode"])
{
	$wifi_value['1_Enable']	="";
	$wifi_value['1_BSSID']	="";
	$wifi_value['2_Enable']	="";
	$wifi_value['2_BSSID']	="";
}

$wifi_status1 = ($wifi_value['1_Status'] == 'Up') ? true : false ;
$wifi_status2 = ($wifi_value['2_Status'] == 'Up') ? true : false ;

function div_mod($n, $m)
{
	if (!is_numeric($n) || !is_numeric($m) || (0==$m)){
		return array(0, 0);
	}	
	for($i=0; $n >= $m; $i++){
		$n = $n - $m;
	}	
	return array($i, $n);
}
?>
<div id="content">
	<h1>Gateway > Hardware > Wireless</h1>

	<div id="educational-tip">
		<p class="tip">View information about the Gateway's wireless components.</p>
		<p class="hidden"><strong>Wi-Fi:</strong> The Gateway provides concurrent 2.4 GHz and 5 GHz for Wi-Fi connections.</p>
		<!--<p class="hidden"><strong>DECT:</strong> Provides details of the cordless phone base built into the Gateway.</p>-->
	</div>

	<div class="module forms block">
		<h2>Wi-Fi LAN port (2.4 GHZ)</h2>
		<div class="form-row">
			<span class="readonlyLabel">Wi-Fi link status:</span>
			<span class="value"><?php echo ($wifi_status1)?"Active":"Inactive";?></span>
		</div>
		<div class="form-row odd">
			<span class="readonlyLabel">MAC Address:</span>
			<span class="value"><?php echo $wifi_value['1_BSSID'];?></span>
		</div>
		<div class="form-row">
			<span class="readonlyLabel">System Uptime:</span>
			<span class="value">
			<?php
			$sec = ($wifi_status1)?$wifi_value['1_RadioUpTime']:0;
			$tmp = div_mod($sec, 24*60*60);
			$day = $tmp[0];
			$tmp = div_mod($tmp[1], 60*60);
			$hor = $tmp[0];
			$tmp = div_mod($tmp[1],    60);
			$min = $tmp[0];
			echo $day." days ".$hor."h: ".$min."m: ".$tmp[1]."s";
		?>
		</span>	</div>
	</div> <!-- end .module -->

	<div class="module forms block">
		<h2>Wi-Fi LAN port (5 GHZ)</h2>
		<div class="form-row">
			<span class="readonlyLabel">Wi-Fi link status:</span>
			<span class="value"><?php echo ($wifi_status2)?"Active":"Inactive";?></span>
		</div>
		<div class="form-row odd">
			<span class="readonlyLabel">MAC Address:</span>
			<span class="value"><?php echo $wifi_value['2_BSSID'];?></span>
		</div>
		<div class="form-row">
			<span class="readonlyLabel">System Uptime:</span>
			<span class="value">
			<?php
			$sec = ($wifi_status2)?$wifi_value['2_RadioUpTime']:0;
			$tmp = div_mod($sec, 24*60*60);
			$day = $tmp[0];
			$tmp = div_mod($tmp[1], 60*60);
			$hor = $tmp[0];
			$tmp = div_mod($tmp[1],    60);
			$min = $tmp[0];
			echo $day." days ".$hor."h: ".$min."m: ".$tmp[1]."s";
		?>
		</span>
		</div>
	</div> <!-- end .module -->

	<!--<div class="module forms block">
		<h2>DECT Base</h2>
		<div class="form-row">
			<span class="readonlyLabel">Status:</span>
			<span class="value"><?php echo ("true"==php_getstr("Device.X_CISCO_COM_MTA.Dect.Enable"))?"Active":"Inactive";?></span>
		</div>
		<div class="form-row odd">
			<span class="readonlyLabel">DECT Module HW Version:</span>
			<span class="value"><?php echo php_getstr("Device.X_CISCO_COM_MTA.Dect.HardwareVersion");?></span>
		</div>
		<div class="form-row">
			<span class="readonlyLabel">RFPI:</span>
			<span class="value"><?php echo php_getstr("Device.X_CISCO_COM_MTA.Dect.RFPI");?></span>
		</div>
	</div> --> <!-- end .module -->

</div><!-- end #content -->

<?php include('includes/footer.php'); ?>
