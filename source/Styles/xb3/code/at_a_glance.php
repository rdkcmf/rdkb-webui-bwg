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
<!-- $Id: at_a_glance.dory.php 2943 2009-08-25 20:58:43Z slemoine $ -->

<?php

session_start();

	$a = getStr("Device.X_CISCO_COM_MTA.Battery.RemainingCharge");
	$b = getStr("Device.X_CISCO_COM_MTA.Battery.ActualCapacity");
	$sta_batt = ($a<=$b && $a && $b) ? round(100*$a/$b) : 0;
	
	//$sta_batt = "61";
	//find battery class manually	
	if($sta_batt > 90) { $battery_class = "bat-100"; }
	elseif($sta_batt > 60) { $battery_class = "bat-75"; }
	elseif($sta_batt > 39) { $battery_class = "bat-50"; }
	elseif($sta_batt > 18) { $battery_class = "bat-25"; }
	elseif($sta_batt > 8) { $battery_class = "bat-10"; }
	else { $battery_class = "bat-0"; }

	$fistUSif = getStr("com.cisco.spvtg.ccsp.pam.Helper.FirstUpstreamIpInterface");

	$WANIPv4 = getStr($fistUSif."IPv4Address.1.IPAddress");

	$ids = explode(",", getInstanceIds($fistUSif."IPv6Address."));
	foreach ($ids as $i){
		$val = getStr($fistUSif."IPv6Address.$i.IPAddress");
		if (!strstr($val, "fe80::")){
			$WANIPv6 = $val;
			break;
		}
	}

	$sta_inet = ($WANIPv4 != "0.0.0.0" || strlen($WANIPv6) > 0) ? "true" : "false";

	//in Bridge mode > Internet connectivity status is always active
	$sta_inet = ($_SESSION["lanMode"] != "router") ? "true" : $sta_inet ;

	$sta_wifi = "false";
	if("Disabled"==$_SESSION["psmMode"]){
		$ssids = explode(",", getInstanceIds("Device.WiFi.SSID."));
		foreach ($ssids as $i){
			$r = (2 - intval($i)%2);	//1,3,5,7==1(2.4G); 2,4,6,8==2(5G)
			if ("true" == getStr("Device.WiFi.Radio.$r.Enable") && "true" == getStr("Device.WiFi.SSID.$i.Enable")){	//bwg has radio.enable, active status is “at least one SSID and its Radio is enabled”
				$sta_wifi = "true";
				break;
			}
		}	
	}
	
	if("Disabled"==$_SESSION["psmMode"]) { 
		$sta_moca_enabled = getStr("Device.MoCA.Interface.1.Enable");
		$sta_moca_status = getStr("Device.MoCA.Interface.1.Status");
		$sta_moca = (($sta_moca_enabled=="true")&&(strtolower($sta_moca_status)=="up")) ? "true" : "false";
	}
	$sta_dect = getStr("Device.X_CISCO_COM_MTA.Dect.Enable");
	$sta_fire = getStr("Device.X_CISCO_COM_Security.Firewall.FirewallLevel");
	
	$arConfig = array(
		"target" => "sta_inet,sta_wifi,sta_moca,sta_fire",
		"status" => $sta_inet.",".$sta_wifi.",".$sta_moca.",".$sta_fire,
	);

	//to update main status
	$mainStatus = array($sta_inet,$sta_wifi,$sta_moca,$sta_fire,$sta_batt,$battery_class);
	$_SESSION['sta_inet'] = $sta_inet;
	$_SESSION['sta_wifi'] = $sta_wifi;
	$_SESSION['sta_moca'] = $sta_moca;
	$_SESSION['sta_fire'] = $sta_fire;
	$_SESSION['sta_batt'] = $sta_batt;
	$_SESSION['battery_class'] = $battery_class;

?>
<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?>
<?php include('includes/utility.php'); ?>

<style>
#bridge_method.disabled:hover {
	cursor:not-allowed
}
</style>

<script type="text/javascript">
$(document).ready(function() {
	comcast.page.init("Gateway > At a Glance", "nav-at-a-glance");

	/*
	** view management: if admin login, remove brige mdoe part
	*/
	/* New requirements: Enable/Disable should be available for all users
	var login_user = "<?php echo $_SESSION["loginuser"]; ?>";

    if(login_user == "admin") {
    	$('.div-bridge').remove();
    }*/

<?php
	/*
	 *	bridge-static 		> Advanced Bridge Mode
	 *	router 				> Bridge Mode Disabled
	 *	full-bridge-static 	> Basic Bridge Mode
	 */
	$bridge_mode = getStr("Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanMode");
	$enableXDNS = getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_EnableXDNS");
?>

	$("#bridge_switch").radioswitch({
		id: "at-a-glance-switch",
		radio_name: "at_a_glance",
		id_on: "at_a_glance_enabled",
		id_off: "at_a_glance_disabled",
		title_on: "Enable bridge mode",
		title_off: "Disable bridge mode",
		state: "<?php echo ($bridge_mode != 'router' ? "on" : "off"); ?>"
	});
	
	function changeBridge(status) {
		if($("#bridge_switch").radioswitch("getState").on){
			$("[name=bridge_method]").attr('disabled', false);
			$('#bridge_method').removeClass('disabled');
		}
		else {
			$("#bridge_method_advanced").attr('checked', false);
			$("#bridge_method_basic").attr('checked', false);
			$("[name=bridge_method]").attr('disabled', true);
			$('#bridge_method').addClass('disabled');
		}
		if(status){
			var bridgeMode = ($("#bridge_switch").radioswitch("getState").on) ? $('input[name=bridge_method]:checked').val() : 'router';
			var cnt = 90;
			jProgress('Bridge Mode changes will be fully applied in <b id="cnt">' + cnt + '</b> seconds, please be patient...', 600);
			$.ajax({
			type:"POST",
			url:"actionHandler/ajax_at_a_glance.php",
			data:{Bridge:"true",bridgeMode:bridgeMode},
			success:function(){
					// don't do jHide, with latest firmware, switch bridge will return before httpd restart, GUI reload at return will cause 500 error. So wait until http restored. 
					var hCnt = setInterval(function(){
						$("#cnt").text(cnt--);
						if (cnt < 0) {clearInterval(hCnt); location.reload();}
					}, 1000);
				}
			});
		}
	}

	$("#bridge_switch").change(function(){
		var isBridgeModelEnable = $("#bridge_switch").radioswitch("getState").on;
		//the 200ms timer is only used to fix confirm dialogue not shown issue on IE
		if (isBridgeModelEnable) {
			setTimeout(function(){
				jConfirm(
				"Enabling Advanced Bridge Mode will disable Gateway’s Router functionality and turn off private Wi-Fi network. Are you sure you want to continue?"
				,"WARNING:"
				,function(ret) {
					if(ret) {
						$("[name=bridge_method]").attr('disabled', false);
						$('#bridge_method').removeClass('disabled');
						$("#bridge_method_advanced").attr('checked', true);
						changeBridge(true);
					} //end of if ret
					else {
						$("#bridge_switch").radioswitch("doSwitch", "off");
						changeBridge(false);
					}
				});//end of jConfirm

			}, 200);
		} //end of if Enabled
		else {
			changeBridge(true);
		}
	});

	$("[name=bridge_method]").change(function(){
		$bridge_method_val = $('input[name=bridge_method]:checked').val();
		$advanced_message = "Enabling Advanced Bridge Mode will disable Gateway’s Router functionality and turn off private Wi-Fi network. Are you sure you want to continue?";
		$basic_message = "Enabling Basic Bridge Mode will disable the Gateway’s router functionality and turn off both radios completely. This setting is recommended for SD-WAN configuration only. Are you sure you want to continue?";
		$message = ($bridge_method_val!='bridge-static') ? $basic_message : $advanced_message ;
		setTimeout(function(){
			jConfirm(
				$message
				,"WARNING:"
				,function(ret) {
					if(ret) {
						changeBridge(true);
					} //end of if ret
					else {
						$('input[name="bridge_method"]').not(':checked').prop("checked", true);
						changeBridge(false);
					}
				}
			);//end of jConfirm
		}, 200);
	});

	$("#IGMP_snooping_switch").change(function(){
		var IGMPEnable=$("input[name='IGMP_snooping']:radio:checked").val();
		jProgress('This may take several seconds', 60);
		$.ajax({
			type:"POST",
			url:"actionHandler/ajax_at_a_glance.php",
			data:{IGMP:"true",IGMPEnable:IGMPEnable},
			success:function(results){
				jHide();
				if(IGMPEnable!=results){
					jAlert("Could not do it!");
					$("input[name='IGMP_snooping']").each(function(){
						//alert($(this).val());alert(result);
						if($(this).val()==results){$(this).parent().addClass("selected");$(this).prop("checked",true);}
						else{$(this).parent().removeClass("selected");$(this).prop("checked",false);}
					});
				}
			}
		});
	});
});

function popUp(URL) {
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=700,height=400,left = 320.5,top = 105');");
}

</script>
<div id="content">
	<h1>Gateway > At a Glance</h1>

	<div id="educational-tip">
		<p class="tip">Summary of your network and connected devices.</p>
		<p class="hidden">
			Select <strong>VIEW CONNECTED DEVICES </strong>to manage devices connected to your network.<br>
			<strong>Advanced Bridge Mode: The Gateway’s Router functionality will be disabled and private Wi-Fi network will be turned off.<br>
			Basic Bridge Mode: The Gateway’s router functionality will be disabled and both radios will be turned off completely. This setting is recommended for SD-WAN configuration only.</strong>
		</p>
	</div>


	<?php
	//Home Network WiFi Settings
	//Only do WiFi SSID check if we are not in power saving mode
	if ("Disabled"==$_SESSION["psmMode"]) {
		//Assumes that private network is always SSID's 1 and 2
		$wifi_param = array(
	        "wifi_24_enabled" 	=> "Device.WiFi.SSID.1.Enable",
			"wifi_24_ssid" 		=> "Device.WiFi.SSID.1.SSID",
			"wifi_24_passkey" 	=> "Device.WiFi.AccessPoint.1.Security.X_COMCAST-COM_KeyPassphrase",
			"wifi_50_enabled" 	=> "Device.WiFi.SSID.2.Enable",
			"wifi_50_ssid" 		=> "Device.WiFi.SSID.2.SSID",
			"wifi_50_passkey" 	=> "Device.WiFi.AccessPoint.2.Security.X_COMCAST-COM_KeyPassphrase",
		);
	    $wifi_value = KeyExtGet("Device.WiFi.", $wifi_param);
		

		$wifi_24_enabled = $wifi_value["wifi_24_enabled"];
		$wifi_24_ssid = htmlspecialchars($wifi_value["wifi_24_ssid"], ENT_NOQUOTES, 'UTF-8');
		$wifi_24_passkey =  htmlspecialchars($wifi_value["wifi_24_passkey"], ENT_NOQUOTES, 'UTF-8');
		$wifi_50_enabled = $wifi_value["wifi_50_enabled"];
		$wifi_50_ssid = htmlspecialchars($wifi_value["wifi_50_ssid"], ENT_NOQUOTES, 'UTF-8');
		$wifi_50_passkey = htmlspecialchars($wifi_value["wifi_50_passkey"], ENT_NOQUOTES, 'UTF-8');
		//If at least one private SSID is enabled
		if ( $bridge_mode == "router" && ("true" == $wifi_24_enabled || "true" == $wifi_50_enabled) ) {
			echo '<div class="module forms" id="wifi-config">';
				echo '<div>';
					echo '<h2>Wi-Fi Configuration</h2>';
				echo '</div>';

				//If both 2.4ghz and 5ghz ssid's and passkeys are the same, or only one is active, then just show one row
				if ((($wifi_24_ssid == $wifi_50_ssid) && ($wifi_24_passkey == $wifi_50_passkey)) || !("true" == $wifi_24_enabled && "true" == $wifi_50_enabled)) {
					//Figure out whice one is active
					if ("true" == $wifi_24_enabled) {
						$wifi_ssid = $wifi_24_ssid;
						$wifi_passkey = $wifi_24_passkey;
					} else {
						$wifi_ssid = $wifi_50_ssid;
						$wifi_passkey = $wifi_50_passkey;
					}
			
				echo '<div class="form-row even">';
					echo '<div class="form-row even">';
						echo '<span class="readonlyLabel">Wi-Fi SSID:</span>';
						echo '<span class="value">';echo $wifi_ssid;echo '</span>';
					echo '</div>';
					if ("admin" == $_SESSION["loginuser"]) {
						echo '<div class="form-row even">';
							echo '<span class="readonlyLabel">Wi-Fi Passkey:</span>';
							echo '<span class="value">';echo $wifi_passkey;echo '</span>';
						echo '</div>';
					}
				echo '</div>';

				} else {
				echo '<div class="form-row even">';
					echo '<div class="form-row even">';
						echo '<span class="readonlyLabel">Wi-Fi SSID (2.4 GHz):</span>';
						echo '<span class="value">';echo $wifi_24_ssid;echo '</span>';
					echo '</div>';
					if ("admin" == $_SESSION["loginuser"]) {
						echo '<div class="form-row even">';
							echo '<span class="readonlyLabel">Wi-Fi Passkey (2.4 GHz):</span>';
							echo '<span class="value">';echo $wifi_24_passkey;echo '</span>';
						echo '</div>';
					}
				echo '</div>';
				echo '<div class="form-row odd">';
					echo '<div class="form-row odd">';
						echo '<span class="readonlyLabel">Wi-Fi SSID (5 GHz):</span>';
						echo '<span class="value">';echo $wifi_50_ssid;echo '</span>';
					echo '</div>';
					if ("admin" == $_SESSION["loginuser"]) { 
						echo '<div class="form-row odd">';
							echo '<span class="readonlyLabel">Wi-Fi Passkey (5 GHz):</span>';
							echo '<span class="value">';echo $wifi_50_passkey;echo '</span>';
						echo '</div>';
					}
				echo '</div>';
			}
			echo '</div>';
		}
	} else {
	//Power Saving Mode is Enabled
		echo '<div class="module psm">';
			echo '<div class="select-row">';
				echo '<span class="readonlyLabel label">Power Saving Mode is enabled!</span>';
			echo '</div>';
		echo '</div>';
	}
	?>
	
		<div class="module div-bridge">
			<div class="select-row">
				<span class="readonlyLabel label">Bridge Mode:</span>
				<span id="bridge_switch"></span>
			</div>
			<div id="bridge_method" class="select-row <?php if($bridge_mode=='router') echo 'disabled'; ?>" style="margin-left: 105px;width: 150px;">
				<input type="radio" name="bridge_method" value="bridge-static" id="bridge_method_advanced" <?php if($bridge_mode=='router') echo 'disabled="disabled"'; ?> <?php if($bridge_mode=='bridge-static') echo 'checked="checked"'; ?> /><b>Advanced</b>
				<input type="radio" name="bridge_method" value="full-bridge-static" id="bridge_method_basic" <?php if($bridge_mode=='router') echo 'disabled="disabled"'; ?> <?php if($bridge_mode=='full-bridge-static') echo 'checked="checked"'; ?> /><b>Basic</b>
			</div>
			<?php
			if($bridge_mode=="router" && $enableXDNS=="true"){
			?>
			<p class="error">Comcast Business SecurityEdge works only in Router Mode</p>
			<?php
			}
			?>
		</div>
	<div class="module forms">
		<a href="at_downloading.php" class="btn">Save Current Configuration</a>
		<a href="upload.php" class="btn">Restore Saved Configuration</a>
	</div>
	<div class="module block" id="home-network">
		<div>
			<h2>Local Network</h2>
			<?php
			if ("Disabled"==$_SESSION["psmMode"]) {
				/*
				$InterfaceNumber=getStr("Device.Ethernet.InterfaceNumberOfEntries");$InterfaceEnable=0;
				for($i=1;$i<=$InterfaceNumber;$i++){
					$EthernetEnable=getStr("Device.Ethernet.Interface.".$i.".Enable");
					$InterfaceEnable+=($EthernetEnable=="true"?1:0);
				}
				if ($InterfaceEnable==$InterfaceNumber) {
					echo "<div class=\"form-row\"><span class=\"on-off\">On</span> <span class=\"readonlyLabel\">Ethernet</span></div>";
				} else {
					echo "<div class=\"form-row off\"><span class=\"on-off\">Off</span> <span class=\"readonlyLabel\">Ethernet</span></div>";
				}*/

				$ids = explode(",", getInstanceIds("Device.Ethernet.Interface."));
				$ethEnable = false;

				foreach ($ids as $i){
					if ("true" == getStr("Device.Ethernet.Interface.".$i.".Enable")){
						$ethEnable = true;
						break;
					}
				}

				if ($ethEnable) {
					echo "<div class=\"form-row\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='Ethernet On' /></span> <span class=\"readonlyLabel\">Ethernet</span></div>";
				} else {
					echo "<div class=\"form-row off\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='Ethernet Off' /></span> <span class=\"readonlyLabel\">Ethernet</span></div>";
				}

				// if (getStr("Device.WiFi.SSID.1.Enable")=="true" || getStr("Device.WiFi.SSID.2.Enable")=="true") {
				if ("true" == $sta_wifi) {		// define in userhar, should have defined every componet status in userbar
					echo "<div class=\"form-row odd\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='WiFi On' /></span> <span class=\"readonlyLabel\">Wi-Fi</span></div>";
				} else {
					echo "<div class=\"form-row odd off\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='WiFi Off' /></span> <span class=\"readonlyLabel\">Wi-Fi</span></div>";
				}

				if ($sta_moca == "true") {
					echo "<div class=\"form-row\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='MoCA On' /></span> <span class=\"readonlyLabel\">MoCA</span></div>";
				} else {
					echo "<div class=\"form-row off\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='MoCA Off' /></span> <span class=\"readonlyLabel\">MoCA</span></div>";
				}
			}
			else {
				echo "<div class=\"form-row off\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='Ethernet Off' /></span> <span class=\"readonlyLabel\">Ethernet</span></div>";
				echo "<div class=\"form-row odd off\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='WiFi Off' /></span> <span class=\"readonlyLabel\">Wi-Fi</span></div>";
				echo "<div class=\"form-row off\"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='MoCA Off' /></span> <span class=\"readonlyLabel\">MoCA</span></div>";
			}
			?>
			<div class="form-row odd">
				<span class="readonlyLabel">Firewall Security Level:</span> <span class="value"><?php echo getStr("Device.X_CISCO_COM_Security.Firewall.FirewallLevel")?></span>
			</div>
		</div>
	</div> <!-- end .module -->

	<div id="internet-usage" class="module block">
		<h2>Connected Devices</h2>
		<?php
		if ("Disabled"==$_SESSION["psmMode"]) {

			$rootObjName    = "Device.Hosts.Host.";
			$paramNameArray = array("Device.Hosts.Host.");
			$mapping_array  = array("PhysAddress", "HostName", "Active");

			$HostIndexArr = DmExtGetInstanceIds("Device.Hosts.Host.");
			if(0 == $HostIndexArr[0]){
				// status code 0 = success
				$HostNum = count($HostIndexArr) - 1;
			}

			if(!empty($HostNum)){

				$Host = getParaValues($rootObjName, $paramNameArray, $mapping_array);
				//this is to construct host info array

				$j = 1;
				if(!empty($Host)){

					foreach ($Host as $key => $value) {
						if (!strcasecmp("true", $value['Active'])) {
							$HostInfo[$j]['HostName']   = $value['HostName'];
							$HostInfo[$j]['Active']     = $value['Active'];
							$HostInfo[$j]['PhysAddress']  = $value['PhysAddress'];
							$j += 1;
						}
					}// end of foreach

					for($i=1; $i<$j; $i++) {

						if( $i%2 ) {$divClass="form-row ";}
							else {$divClass="form-row odd";}

						$HostName = $HostInfo[$i]['HostName'];

						if (($HostName == "*") || (strlen($HostName) == 0)) {
							$HostName = strtoupper($HostInfo[$i]['PhysAddress']);
						}

						echo "
						   <div class=\" $divClass \"><span class=\"on-off sprite_cont\"><img src=\"./cmn/img/icn_on_off.png\" alt='Host On' /></span> <span class=\"readonlyLabel\">$HostName</span></div>
						";

					}//end of for
				}//end of empty $host
			}//end of if empty $hostnum

			echo '<div class="btn-group"><a href="connected_devices_computers.php" class="btn">View Connected Devices</a></div>';

		}//end of psmMode condition
		?>
	</div> <!-- end .module -->

	<!--div class="module">
		<div class="select-row">
			<span class="readonlyLabel label">IGMP Snooping:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</span>
			<?php
			//$IGMP_mode=getStr("Device.X_CISCO_COM_DeviceControl.IGMPSnoopingEnable");
			$IGMP_mode = "false";
			if ($IGMP_mode=="true") { //or Enabled
			?>
			<ul id="IGMP_snooping_switch" class="radio-btns enable">
				<li>
					<input id="IGMP_snooping_enabled" name="IGMP_snooping" type="radio"  value="Enabled" checked="checked" />
					<label for="IGMP_snooping_enabled" >Enable </label>
				</li>
				<li class="radio-off">
					<input id="IGMP_snooping_disabled" name="IGMP_snooping" type="radio"  value="Disabled" />
					<label for="IGMP_snooping_disabled" >Disable </label>
				</li>
			</ul>
			<?php }else{?>
			<ul id="IGMP_snooping_switch" class="radio-btns enable">
				<li>
					<input id="IGMP_snooping_enabled" name="IGMP_snooping" type="radio"  value="Enabled"/>
					<label for="IGMP_snooping_enabled" >Enable </label>
				</li>
				<li class="radio-off">
					<input id="IGMP_snooping_disabled" name="IGMP_snooping" type="radio"  value="Disabled" checked="checked"/>
					<label for="IGMP_snooping_disabled" >Disable </label>
				</li>
			</ul>
			<?php } ?>
		</div>
	</div-->

</div><!-- end #content -->
<?php include('includes/footer.php'); ?>
