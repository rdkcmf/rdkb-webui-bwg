﻿<?php
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
<?php include('includes/utility.php') ?>
<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?>
<?php 

$device_ctrl_param = array(
	"LanGwIP"    		=> "Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanIPAddress",
	"DeviceMode" 		=> "Device.X_CISCO_COM_DeviceControl.DeviceMode",
	"subnetmask" 		=> "Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanSubnetMask",
);
$device_ctrl_value = KeyExtGet("Device.X_CISCO_COM_DeviceControl.", $device_ctrl_param);
$dhcpv4_param = array(
        "DHCPTime"   		=> "Device.DHCPv4.Server.Pool.1.LeaseTime",
        "WAN_GW_IPv4_Address" 	=> "Device.DHCPv4.Client.1.IPRouters",
        "beginAddr" 		=> "Device.DHCPv4.Server.Pool.1.MinAddress",
        "endAddr" 		=> "Device.DHCPv4.Server.Pool.1.MaxAddress",
		"DHCPEnable" 	=> "Device.DHCPv4.Server.Enable",
);
$dhcpv4_value = KeyExtGet("Device.DHCPv4.", $dhcpv4_param);

$dhcpv6_param = array(
	"DHCPV6Time" 	=> "Device.DHCPv6.Server.Pool.1.LeaseTime",
	"state" 	=> "Device.DHCPv6.Server.X_CISCO_COM_Type",
	"v6_begin_addr" => "Device.DHCPv6.Server.Pool.1.PrefixRangeBegin",
	"v6_end_addr" 	=> "Device.DHCPv6.Server.Pool.1.PrefixRangeEnd",
);
$dhcpv6_value = KeyExtGet("Device.DHCPv6.Server.", $dhcpv6_param);
$LanGwIP		= $device_ctrl_value["LanGwIP"];

// DNS servers
// dmcli eRT setv Device.DHCPv4.Server.Pool.1.DNSServers string 172.168.10.11,10.1.14.1
// dmcli eRT setv Device.DHCPv6.Server.Pool.1.X_RDKCENTRAL-COM_DNSServers string "2001:558:feed::1 2001:558:feed::2"
$ipv4_dns_enable 	= getStr("Device.DHCPv4.Server.Pool.1.DNSServersEnabled");
$ipv4_dns_server 	= getStr("Device.DHCPv4.Server.Pool.1.DNSServers");

$ipv4_dns_enable_1	= $ipv4_dns_enable;
$ipv4_dns_enable_2	= $ipv4_dns_enable;

$ipv4_dns 			= explode(',', $ipv4_dns_server);
$ipv4_primary_dns	= $ipv4_dns[0];
$ipv4_secondary_dns	= $ipv4_dns[1];

$ipv6_dns_enable 	= getStr("Device.DHCPv6.Server.Pool.1.X_RDKCENTRAL-COM_DNSServersEnabled");
$ipv6_dns_server 	= getStr("Device.DHCPv6.Server.Pool.1.X_RDKCENTRAL-COM_DNSServers");

$ipv6_dns_enable_1	= $ipv6_dns_enable;
$ipv6_dns_enable_2	= $ipv6_dns_enable;

$ipv6_dns 			= explode(' ', $ipv6_dns_server);
$ipv6_primary_dns	= $ipv6_dns[0];
$ipv6_secondary_dns	= $ipv6_dns[1];

$DHCPTime   = $dhcpv4_value["DHCPTime"];
$DHCPV6Time = $dhcpv6_value["DHCPV6Time"];
$ipv6_prefix = getStr("Device.IP.Interface.1.IPv6Prefix.1.Prefix");
$interface = getStr("com.cisco.spvtg.ccsp.pam.Helper.FirstDownstreamIpInterface");
$DeviceMode = $device_ctrl_value["DeviceMode"];

//CM GW IP Address
$CM_GW_IP_Address = getStr("Device.X_CISCO_COM_CableModem.Gateway");

//CM IP Address
$CM_IP_Address = getStr("Device.X_CISCO_COM_CableModem.IPAddress");

//WAN GW IP Address (IPv4)
$WAN_GW_IPv4_Address = $dhcpv4_value["WAN_GW_IPv4_Address"];

$DHCPEnable = $dhcpv4_value["DHCPEnable"];

//Virtual LAN_GW_IPv4_Address
exec("ifconfig lan0", $out);
foreach ($out as $v){
	if (strpos($v, 'inet addr')){
		$tmp = explode('Bcast', $v);
		$tmp = explode('addr:', $tmp[0]);
		$LAN_GW_IPv4_Address = trim($tmp[1]);
	}
}

// $interface = "Device.IP.Interface.2.";

// initial some variable to suppress some error
$ipv6_local_addr = "";
$ipv6_global_addr = "";
$idArr = explode(",", getInstanceIds($interface."IPv6Address."));
foreach ($idArr as $key => $value) {
  $ipv6addr = getStr($interface."IPv6Address.$value.IPAddress");
  if (stripos($ipv6addr, "fe80::") !== false) {
  	$ipv6_local_addr = $ipv6addr;
  }
  else{
  	$ipv6_global_addr = $ipv6addr;
  }
}

//$ipv6_local_addr = "fe80::20c:29ff:fe43:aac4/64";
//$ipv6_global_addr= "2002:48a3::48a3:ff63";
$tmp = substr($ipv6_local_addr, 6); //remove fe80::
$tmp1 = explode('/', $tmp); //trim /64
$local_ipv6 = $tmp1[0];
$local_ipv6_arr = explode(':', $local_ipv6);

?>

<style type="text/css">

label{
	margin-right: 10px !important;
}

.form-row input.ipv6-input {
	width: 35px;
}

</style>

<script type="text/javascript">
$(document).ready(function() {
	comcast.page.init("Gateway > Connection > Local IP Configuration", "nav-local-ip-network");
	/*
	** view management: if admin login, pop up alert msg if change gw ip addr
	*/
	var login_user = "<?php echo $_SESSION["loginuser"]; ?>";

	var jsGwIP = "<?php echo $LanGwIP; ?>";
	var jsLeaseTime = "<?php echo $DHCPTime; ?>";
	var jsV6LeaseTime = "<?php echo $DHCPV6Time; ?>";
	var ipv4_dns_enable_1="<?php echo $ipv4_dns_enable_1;?>";
	var ipv4_dns_enable_2="<?php echo $ipv4_dns_enable_2;?>";
	for(var i=1;i<=4;i++){
		if((ipv4_dns_enable_1=="true") && (ipv4_dns_enable_2=="true") ){
			$('#ipv4_primary_dns_'+i).prop("disabled", false);
			$('#ipv4_secondary_dns_'+i).prop("disabled", false);
		}else{
			$('#ipv4_primary_dns_'+i).prop("disabled", true);
			$('#ipv4_secondary_dns_'+i).prop("disabled", true);
		}
	}

	var ipv6_dns_enable_1="<?php echo $ipv6_dns_enable_1;?>";
	var ipv6_dns_enable_2="<?php echo $ipv6_dns_enable_2;?>";
	for(var i=1;i<9;i++){
		if((ipv6_dns_enable_1=="true") && (ipv6_dns_enable_2=="true") ){
			$('#ipv6_primary_dns_'+i).prop("disabled", false);
			$('#ipv6_secondary_dns_'+i).prop("disabled", false);
		}else{
			$('#ipv6_primary_dns_'+i).prop("disabled", true);
			$('#ipv6_secondary_dns_'+i).prop("disabled", true);
		}
	}

	function updateIPv4() {
	
		if (netmask_is_validate() == false) {
			return;
		}
		
		var ip_addr = $("#ipv4_gateway_address_1").val() + "." + $("#ipv4_gateway_address_2").val() + "." + $("#ipv4_gateway_address_3").val() + "." + $("#ipv4_gateway_address_4").val();		
		var subnet_mask = $("#ipv4_subnet_mask_1").val() + "." + $("#ipv4_subnet_mask_2").val() + "." + $("#ipv4_subnet_mask_3").val() + "." + $("#ipv4_subnet_mask_4").val();

		if (subnet_mask == "255.255.255.255") {
			var ipv4_dhcp_beginning_address = ip_addr;
			var ipv4_dhcp_ending_address = ip_addr;		
		} else {
			var ip = ip2long(ip_addr);
			var nm = ip2long(subnet_mask);
			var nw = ip & nm;
			var bc = nw | (~nm);
			var ipv4_dhcp_beginning_address = long2ip(nw + 2);
			var ipv4_dhcp_ending_address = long2ip(bc - 1);		
		}
		var beginning_ip = ipv4_dhcp_beginning_address.split('.');
		var ending_ip = ipv4_dhcp_ending_address.split('.');	
		ending_ip[3] -= 1;
		
		$("#ipv4_dhcp_beginning_address_1").val(replaceNaNwithEmptyString(beginning_ip[0]));
		$("#ipv4_dhcp_beginning_address_2").val(replaceNaNwithEmptyString(beginning_ip[1]));
		$("#ipv4_dhcp_beginning_address_3").val(replaceNaNwithEmptyString(beginning_ip[2]));
		$("#ipv4_dhcp_beginning_address_4").val(replaceNaNwithEmptyString(beginning_ip[3]));

		$("#ipv4_dhcp_ending_address_1").val(replaceNaNwithEmptyString(ending_ip[0]));
		$("#ipv4_dhcp_ending_address_2").val(replaceNaNwithEmptyString(ending_ip[1]));
		$("#ipv4_dhcp_ending_address_3").val(replaceNaNwithEmptyString(ending_ip[2]));
		$("#ipv4_dhcp_ending_address_4").val(replaceNaNwithEmptyString(ending_ip[3]));		
		
		$("#ipv4_dhcp_beginning_address_1").prop("disabled", false);
		$("#ipv4_dhcp_ending_address_1").prop("disabled", false);		
		$("#ipv4_dhcp_beginning_address_2").prop("disabled", false);
		$("#ipv4_dhcp_ending_address_2").prop("disabled", false);
		$("#ipv4_dhcp_beginning_address_3").prop("disabled", false);
		$("#ipv4_dhcp_ending_address_3").prop("disabled", false);		
		$("#ipv4_dhcp_beginning_address_4").prop("disabled", false);		
		$("#ipv4_dhcp_ending_address_4").prop("disabled", false);
			
		if ($("#ipv4_subnet_mask_1").val() == "255") {
			$("#ipv4_dhcp_beginning_address_1").prop("disabled", true);
			$("#ipv4_dhcp_ending_address_1").prop("disabled", true);
		}
		
		if ($("#ipv4_subnet_mask_2").val() == "255") {		
			$("#ipv4_dhcp_beginning_address_2").prop("disabled", true);
			$("#ipv4_dhcp_ending_address_2").prop("disabled", true);
		}
		
		if ($("#ipv4_subnet_mask_3").val() == "255") {	
			$("#ipv4_dhcp_beginning_address_3").prop("disabled", true);
			$("#ipv4_dhcp_ending_address_3").prop("disabled", true);
		}	
			
		if ($("#ipv4_subnet_mask_4").val() == "255") {	
			$("#ipv4_dhcp_beginning_address_4").prop("disabled", true);		
			$("#ipv4_dhcp_ending_address_4").prop("disabled", true);
		}

	}//end of updateIPv4

	// Update range addresses automatically
	$(".gateway_address").keyup(function() {
		updateIPv4();
	});
/*
	$("#ipv4_subnet_mask").change(function() {
		updateIPv4();
	});
*/
	function initPopulateDHCPv4(){
		if ($("#ipv4_subnet_mask_1").val() == "255") {
			$("#ipv4_dhcp_beginning_address_1").prop("disabled", true);
			$("#ipv4_dhcp_ending_address_1").prop("disabled", true);
		}
		
		if ($("#ipv4_subnet_mask_2").val() == "255") {		
			$("#ipv4_dhcp_beginning_address_2").prop("disabled", true);
			$("#ipv4_dhcp_ending_address_2").prop("disabled", true);
		}
		
		if ($("#ipv4_subnet_mask_3").val() == "255") {	
			$("#ipv4_dhcp_beginning_address_3").prop("disabled", true);
			$("#ipv4_dhcp_ending_address_3").prop("disabled", true);
		}	
				
		if ($("#ipv4_subnet_mask_4").val() == "255") {	
			$("#ipv4_dhcp_beginning_address_4").prop("disabled", true);		
			$("#ipv4_dhcp_ending_address_4").prop("disabled", true);
		}
	}

	initPopulateDHCPv4();

	// Disable time text field, if lease time is forever
	$("#ipv4_dhcp_lease_time_measure").change(function() {
		var $select = $(this);
		var $time = $("#ipv4_dhcp_lease_time_amount");
		if($select.find("option:selected").val() == "forever") {
			$time.prop("disabled", true).addClass("disabled").val();
		} else {
			$time.prop("disabled", false).removeClass("disabled").val();
		}
	}).trigger("change");

	$("#ipv6_dhcp_lease_time_measure").change(function() {
		var $select = $(this);
		var $time = $("#ipv6_dhcp_lease_time_amount");
		if($select.find("option:selected").val() == "forever") {
			$time.prop("disabled", true).addClass("disabled").val();
		} else {
			$time.prop("disabled", false).removeClass("disabled").val();
		}
	}).trigger("change");


	jQuery.validator.addMethod("checkMask",function(value,element){		
		return netmask_is_validate();
	}, "DHCP address is beyond the valid range.");


	$.validator.addMethod("hexadecimal", function(value, element) {
		return this.optional(element) || /^[a-fA-F0-9]+$/i.test(value);
	}, "Only hexadecimal characters are valid. Acceptable characters are ABCDEF0123456789.");

	var ipv4_dns_ruleSet = {
		required: function() { return $('#dns_manual_ipv4').is(":checked"); },
		min: 0,
		max: 255,
		digits: true,
	};

	$("#pageForm").validate({
		groups: {
		ip_set:		"ipv4_gateway_address_1 ipv4_gateway_address_2 ipv4_gateway_address_3 ipv4_gateway_address_4",
		net_mask: 	"ipv4_subnet_mask_1 ipv4_subnet_mask_2 ipv4_subnet_mask_3 ipv4_subnet_mask_4",
		b_range: 	"ipv4_dhcp_beginning_address_2 ipv4_dhcp_beginning_address_3 ipv4_dhcp_beginning_address_4",
		e_range: 	"ipv4_dhcp_ending_address_2 ipv4_dhcp_ending_address_3 ipv4_dhcp_ending_address_4",
		ipv4_dns_1: "ipv4_primary_dns_1 ipv4_primary_dns_2 ipv4_primary_dns_3 ipv4_primary_dns_4",
		ipv4_dns_2: "ipv4_secondary_dns_1 ipv4_secondary_dns_2 ipv4_secondary_dns_3 ipv4_secondary_dns_4",
		},
		rules: {
			ipv4_gateway_address_1: {
				required: true,
				min: 0,
				max: 255,
				digits: true
			},
			ipv4_gateway_address_2: {
				required: true,
				min: 0,
				max: 255,
				digits: true
			},
			ipv4_gateway_address_3: {
				required: true,
				min: 0,
				max: 255,
				digits: true
			}
			,ipv4_gateway_address_4: {
				required: true,
				min: 1,
				max: function(){
					/*[10.0.0.1 ~ 10.255.255.254,
					172.16.0.1 ~ 172.31.255.254,
					192.168.0.1 ~ 192.168.255.254]*/
					var adrs_1 = $('#ipv4_gateway_address_1').val();
					var adrs_2 = $('#ipv4_gateway_address_2').val();
					var adrs_3 = $('#ipv4_gateway_address_3').val();
					if((adrs_1 == '10'  && adrs_2 == '255' && adrs_3 == '255') ||
						(adrs_1 == '172' && adrs_2 == '31'  && adrs_3 == '255') ||
						(adrs_1 == '192' && adrs_2 == '168' && adrs_3 == '255')
					) return 253;
					else return 254;
				},
				digits: true
			}
			,ipv4_subnet_mask_1: {
				required: true,
				min: 1,
				max: 255,
				digits: true
			}
			,ipv4_subnet_mask_2: {
				required: true,
				min: 0,
				max: 255,
				digits: true
			}
			,ipv4_subnet_mask_3: {
				required: true,
				min: 0,
				max: 255,
				digits: true
			}	
			,ipv4_subnet_mask_4: {
				required: true,
				min: 0,
				max: 254,
				digits: true
			}			
			,ipv4_dhcp_beginning_address_1: {
			    required: true,
				min: 0,
				max: 255,
				digits: true
			}
			,ipv4_dhcp_ending_address_1: {
			    required: true,
				min: 0,
				max: 255,
				digits: true,
			}
			,ipv4_dhcp_beginning_address_2: {
			    required: true,
				min: 0,
				max: 255,
				digits: true
			}
			,ipv4_dhcp_ending_address_2: {
			    required: true,
				min: 0,
				max: function(){
					var mask_2 = parseInt($("#ipv4_subnet_mask_2").val(),10);
					return (255-mask_2);
				},
				digits: true,
			}
			,ipv4_dhcp_beginning_address_3: {
			    required: true,
				min: 0,
				max: 255,
				digits: true
			}
			,ipv4_dhcp_ending_address_3: {
			    required: true,
				min: 0,
				max: 255,
				digits: true,
			}
			,ipv4_dhcp_beginning_address_4: {
			    required: true,
				min: 1,
				max: 255,
				digits: true
			}
			,ipv4_dhcp_ending_address_4: {
			    required: true,
				min: 1,
				max: function(){
					var mask_3 = parseInt($("#ipv4_subnet_mask_3").val(),10);
					var mask_4 = parseInt($("#ipv4_subnet_mask_4").val(),10);
					var ending_address_3 = parseInt($("#ipv4_dhcp_ending_address_3").val(),10);
					var ipv4_DEA_4 = 255-mask_4-1;
					if((parseInt(mask_3,10) + parseInt(ending_address_3,10))>='255') return (ipv4_DEA_4-1);
					else return ipv4_DEA_4;
				},
				digits: true,
			}
			,ipv4_primary_dns_1: ipv4_dns_ruleSet
			,ipv4_primary_dns_2: ipv4_dns_ruleSet
			,ipv4_primary_dns_3: ipv4_dns_ruleSet
			,ipv4_primary_dns_4: ipv4_dns_ruleSet
			,ipv4_secondary_dns_1: ipv4_dns_ruleSet
			,ipv4_secondary_dns_2: ipv4_dns_ruleSet
			,ipv4_secondary_dns_3: ipv4_dns_ruleSet
			,ipv4_secondary_dns_4: ipv4_dns_ruleSet
			,ipv4_dhcp_lease_time_amount: {
				required: function() {
					return $("#ipv4_dhcp_lease_time_measure option:selected").val() != "forever";
				},
			    	digits : true,
				min: function () {
					if($("#ipv4_dhcp_lease_time_measure option:selected").val() == "seconds") return 120;
					else if($("#ipv4_dhcp_lease_time_measure option:selected").val() == "minutes") return 2;
					else return 1;
				}
	        	}
		},
	    	highlight: function( element, errorClass, validClass ) {
			$(element).closest(".form-row").find("input").addClass(errorClass).removeClass(validClass);
		},
		unhighlight: function( element, errorClass, validClass ) {
			$(element).closest(".form-row").find("input").removeClass(errorClass).addClass(validClass);
		}
	});

	var ipv6_dns_ruleSet = {
		required: function() { return $('#dns_manually_ipv6').is(":checked"); }
		,hexadecimal: true
	};

	$("#pageFormV6").validate({
		groups:{
			DBA: "DBA_5 DBA_6 DBA_7 DBA_8",
			DEA: "DEA_5 DEA_6 DEA_7 DEA_8",
	    		ipv6_dns_1: "ipv6_primary_dns_1 ipv6_primary_dns_2 ipv6_primary_dns_3 ipv6_primary_dns_4 ipv6_primary_dns_5 ipv6_primary_dns_6 ipv6_primary_dns_7 ipv6_primary_dns_8",
	    		ipv6_dns_2: "ipv6_secondary_dns_1 ipv6_secondary_dns_2 ipv6_secondary_dns_3 ipv6_secondary_dns_4 ipv6_secondary_dns_5 ipv6_secondary_dns_6 ipv6_secondary_dns_7 ipv6_secondary_dns_8"
		},
		rules: {
	        DBA_5: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,DBA_6: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,DBA_7: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,DBA_8: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,DEA_5: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,DEA_6: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,DEA_7: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,DEA_8: {
	            required: function() {
	                return $('#Stateful').is(":checked");
	            }
	            ,hexadecimal: true
	        }
	        ,ipv6_primary_dns_1: ipv6_dns_ruleSet
			,ipv6_primary_dns_2: ipv6_dns_ruleSet
			,ipv6_primary_dns_3: ipv6_dns_ruleSet
			,ipv6_primary_dns_4: ipv6_dns_ruleSet
			,ipv6_primary_dns_5: ipv6_dns_ruleSet
			,ipv6_primary_dns_6: ipv6_dns_ruleSet
			,ipv6_primary_dns_7: ipv6_dns_ruleSet
			,ipv6_primary_dns_8: ipv6_dns_ruleSet
			,ipv6_secondary_dns_1: ipv6_dns_ruleSet
			,ipv6_secondary_dns_2: ipv6_dns_ruleSet
			,ipv6_secondary_dns_3: ipv6_dns_ruleSet
			,ipv6_secondary_dns_4: ipv6_dns_ruleSet
			,ipv6_secondary_dns_5: ipv6_dns_ruleSet
			,ipv6_secondary_dns_6: ipv6_dns_ruleSet
			,ipv6_secondary_dns_7: ipv6_dns_ruleSet
			,ipv6_secondary_dns_8: ipv6_dns_ruleSet
			,ipv6_dhcp_lease_time_amount: {
				required: function() {
					return $("#ipv6_dhcp_lease_time_measure option:selected").val() != "forever";
				},
			    	digits : true,
				min: function () {
					if($("#ipv6_dhcp_lease_time_measure option:selected").val() == "seconds") return 120;
					else if($("#ipv6_dhcp_lease_time_measure option:selected").val() == "minutes") return 2;
					else return 1;
				}
	        	}
		},
	    	highlight: function( element, errorClass, validClass ) {
			$(element).closest(".form-row").find("input").addClass(errorClass).removeClass(validClass);
		},
		unhighlight: function( element, errorClass, validClass ) {
			$(element).closest(".form-row").find("input").removeClass(errorClass).addClass(validClass);
		}
	}); //end of pageform v6

	$("#ipv4_dhcp_lease_time_measure, #ipv4_dhcp_lease_time_amount").change(function() {
	  	$("#pageForm").valid();
	});
	$("#ipv6_dhcp_lease_time_measure, #ipv6_dhcp_lease_time_amount").change(function() {
		$("#pageFormV6").valid();
	});

	$("#restore-default-settings-ipv4").click(function(e) {
		e.preventDefault();
		var str = 'Changing Gateway IP or any DHCP setting would reset the gateway.\n';
		str += '\n';
		str += '<b>WARNING</b>: Gateway will be rebooted!\n';
		str += 'Incoming/Outgoing call and internet connection will be interrupted!';
		jConfirm(
		str
		,"Are you sure?"
		,function(ret) {
			if(ret) {
				jConfirm(
				"Are you sure you want the change LAN IPv4 to default settings?"
				,"Reset Default IPv4 Settings"
				,function(ret) {
					if(ret) {

						$("#ipv4_gateway_address_1").val(10);
						$("#ipv4_gateway_address_2").val(1);
						$("#ipv4_gateway_address_3").val(10);
						$("#ipv4_gateway_address_4").val(1);
						$("#ipv4_subnet_mask_1").val(255);
						$("#ipv4_subnet_mask_2").val(255);
						$("#ipv4_subnet_mask_3").val(255);
						$("#ipv4_subnet_mask_4").val(0);
						$("#ipv4_dhcp_beginning_address_1").val(10);
						$("#ipv4_dhcp_beginning_address_2").val(1);
						$("#ipv4_dhcp_beginning_address_3").val(10);
						$("#ipv4_dhcp_beginning_address_4").val(2);

						$("#ipv4_dhcp_ending_address_1").val(10);
						$("#ipv4_dhcp_ending_address_2").val(1);
						$("#ipv4_dhcp_ending_address_3").val(10);
						$("#ipv4_dhcp_ending_address_4").val(253);
						
						$("#ipv4_dhcp_lease_time_amount").val(1);
						$("#ipv4_dhcp_lease_time_measure").val("weeks");

						$('#dns_manual_ipv4').attr('checked', false);
						disablevalipv4();

						/*$("#ipv4_primary_dns_1").val(75);
						$("#ipv4_primary_dns_2").val(75);
						$("#ipv4_primary_dns_3").val(75);
						$("#ipv4_primary_dns_4").val(75);

						$("#ipv4_secondary_dns_1").val(75);
						$("#ipv4_secondary_dns_2").val(75);
						$("#ipv4_secondary_dns_3").val(76);
						$("#ipv4_secondary_dns_4").val(76);*/
						
						var ipaddr = "10.1.10.1"; //ares specific requirement
						var subnet_mask = "255.255.255.0"; 
						var dhcp_begin_addr = "10.1.10.2";
						var dhcp_end_addr = "10.1.10.253";
						var lease_time = 604800; // 1 week
						/*var primary_dns = "75.75.75.75";
						var secondary_dns = "75.75.76.76";*/

						var Config = '{"Ipaddr":"' + ipaddr + '", "Subnet_mask":"' + subnet_mask + '", "Dhcp_begin_addr":"' + dhcp_begin_addr 
				        	+ '", "Dhcp_end_addr":"' + dhcp_end_addr +'", "Dhcp_lease_time":"' + lease_time 
							+ '","dns_manually":"false"}';
				        if((login_user == "cusadmin") && (jsGwIP != ipaddr)) {
				    	jConfirm(	        
					        "This may need you to relogin with new Gateway IP address"
					        , "Are you sure?"
					        ,function(ret) {
					            if(ret) {	
					            	jProgress('Device is rebooting. Please be patient...', 600);               
				            		$.ajax({
				            			type: "POST",
				            			url:  "actionHandler/ajaxSet_IP_configuration.php",
				            			data: {
				            				configInfo: Config
				            			},
				            			dataType: "json",
				            			//timeout:  15000,
				            			success: function(){ 
				            			//jAlert("Please login with new IP address "); 	            			
				            			},
				            			error: function(){ 
				            			//jAlert("Please login with new IP address ");             				
				            			}
				            		}); //end of ajax

				            		setTimeout(function(){
										jHide();
				        				window.location.replace('http://' + ipaddr + '/index.php');
									}, 90000);

					            } //end of if ret
						    }); //end of jConfirm
						} //end of login user
						else{
							setIPconfiguration(Config);
						}
					} //end of if ret
				});//end of jConfirm
			}
		});
	});
/*
	var gw_ip_1 = parseInt($('#ipv4_gateway_address_1').val());
	if (gw_ip_1 == 172){
		//gw ip is B class ip address		
		if( $('#mask4').length>0 ) $('#mask4').remove();
		if( ! $('#mask2').length>0 ) mask2Option.insertAfter('#mask1');
	}
	else if (gw_ip_1 == 192){
		//gw ip is C class ip address
		if( $('#mask2').length>0 ) $('#mask2').remove();
		if( $('#mask4').length>0 ) $('#mask4').remove();
	}
*/
/*
	$('#ipv4_gateway_address_1').change(function(){
		var gw_ip1 = parseInt($('#ipv4_gateway_address_1').val());

	    var mask2Option = $('<option id="mask2" value="255.255.0.0">255.255.0.0</option>');
	    var mask4Option = $('<option id="mask4" value="255.0.0.0">255.0.0.0</option>');

	    if (gw_ip1 == 10){
			if( ! $('#mask2').length>0 ) mask2Option.insertAfter('#mask1');
			if( ! $('#mask4').length>0 ) mask4Option.insertAfter('#mask3');
	    }
	    else if (gw_ip1 == 172){
			//gw ip is B class ip address
			if( $('#mask4').length>0 ) $('#mask4').remove();
			if( ! $('#mask2').length>0 ) mask2Option.insertAfter('#mask1');
		}
		else if (gw_ip1 == 192){
			//gw ip is C class ip address
			if( $('#mask2').length>0 ) $('#mask2').remove();
			if( $('#mask4').length>0 ) $('#mask4').remove();
		}
	});
*/

function ipv4_valid_range(){
	var gw4_1 = parseInt($("#ipv4_gateway_address_1").val());
	var gw4_2 = parseInt($("#ipv4_gateway_address_2").val());
	var gw4_3 = parseInt($("#ipv4_gateway_address_3").val());
	var gw4_4 = parseInt($("#ipv4_gateway_address_4").val());

	var ip_addr = gw4_1 + "." + gw4_2 + "." + gw4_3 + "." + gw4_4;
	var subnet_mask = $("#ipv4_subnet_mask_1").val() + "." + $("#ipv4_subnet_mask_2").val() + "." + $("#ipv4_subnet_mask_3").val() + "." + $("#ipv4_subnet_mask_4").val();

	if (subnet_mask == "255.255.255.255") {
		var ipv4_dhcp_beginning_address = ip_addr;
		var ipv4_dhcp_ending_address = ip_addr;
	} else {
		var ip = ip2long(ip_addr);
		var nm = ip2long(subnet_mask);
		var nw = ip & nm;
		var bc = nw | (~nm);
		var ipv4_dhcp_beginning_address = long2ip(nw + 1);
		var ipv4_dhcp_ending_address = long2ip(bc - 1);
	}
	var beginning_ip = ipv4_dhcp_beginning_address;
	var ending_ip = ipv4_dhcp_ending_address.split('.');
	ending_ip[3] -= 1;
	ending_ip = ending_ip.join('.');
	return [beginning_ip, ending_ip];
}

/* 
 This function checks dhcpv4 ending address should be larger than begin address
 @DBArr = Array(dhcp begin dot address partial 2, 3, 4)
 @DEArr = Array(dhcp ending dot address partial 2, 3, 4)
 */
function validate_v4addr_pool(DBArr, DEArr) {
	
	var flag = true;

	if (DEArr[0] < DBArr[0]) {
		flag = false;
	}
	else if (DEArr[0] == DBArr[0]) {
		if (DEArr[1] < DBArr[1]) {
			flag = false;
		}
		else if (DEArr[1] == DBArr[1]) {
			if (DEArr[2] < DBArr[2]) {
				flag = false;
			}			
		}
	}

	return flag;
}

function is_ipv4_in_range(ipv4_addr, ipv4_start, ipv4_end) {
	var ipv4_num = ip4StrToBin(ipv4_addr);
	return (ipv4_num >= ip4StrToBin(ipv4_start)) && (ipv4_num <= ip4StrToBin(ipv4_end));
}

function validate_v4addr_subnetRange(DBArr, DEArr) {
	ipv4_valid_range_ip	= ipv4_valid_range();
	var beginning_ip 	= ipv4_valid_range_ip[0];
	var ending_ip 		= ipv4_valid_range_ip[1];
	return is_ipv4_in_range(DBArr, beginning_ip, ending_ip) && is_ipv4_in_range(DEArr, beginning_ip, ending_ip);
}

$('#submit_ipv4').click(function(e){
	e.preventDefault();

	var dhcp4B2 = parseInt($("#ipv4_dhcp_beginning_address_2").val());
	var dhcp4B3 = parseInt($("#ipv4_dhcp_beginning_address_3").val());
	var dhcp4B4 = parseInt($("#ipv4_dhcp_beginning_address_4").val());
	var dhcp4E2 = parseInt($("#ipv4_dhcp_ending_address_2").val());
	var dhcp4E3 = parseInt($("#ipv4_dhcp_ending_address_3").val());
	var dhcp4E4 = parseInt($("#ipv4_dhcp_ending_address_4").val());

	var DBArr = Array(dhcp4B2, dhcp4B3, dhcp4B4);
	var DEArr = Array(dhcp4E2, dhcp4E3, dhcp4E4);

	var ipaddr = $('#ipv4_gateway_address_1').val() + "." + $('#ipv4_gateway_address_2').val() + "." + $('#ipv4_gateway_address_3').val() + "." + $('#ipv4_gateway_address_4').val();
	var subnet_mask = $("#ipv4_subnet_mask_1").val() + "." + $("#ipv4_subnet_mask_2").val() + "." + $("#ipv4_subnet_mask_3").val() + "." + $("#ipv4_subnet_mask_4").val();
	var dhcp_begin_addr = $('#ipv4_dhcp_beginning_address_1').val() + "." + $('#ipv4_dhcp_beginning_address_2').val() + "." + $('#ipv4_dhcp_beginning_address_3').val() + "." + $('#ipv4_dhcp_beginning_address_4').val();
	var dhcp_end_addr = $('#ipv4_dhcp_ending_address_1').val() + "." + $('#ipv4_dhcp_ending_address_2').val() + "." + $('#ipv4_dhcp_ending_address_3').val() + "." + $('#ipv4_dhcp_ending_address_4').val();
	var primary_dns = $('#ipv4_primary_dns_1').val() + "." + $('#ipv4_primary_dns_2').val() + "." + $('#ipv4_primary_dns_3').val() + "." + $('#ipv4_primary_dns_4').val();
	var secondary_dns = $('#ipv4_secondary_dns_1').val() + "." + $('#ipv4_secondary_dns_2').val() + "." + $('#ipv4_secondary_dns_3').val() + "." + $('#ipv4_secondary_dns_4').val();

	if (netmask_is_validate() == false) {
		jAlert("Invalid netmask!");
        return;
	}
	
    if (! validate_v4addr_pool(DBArr, DEArr)) {
        jAlert("Beginning Address can't be larger than ending address!");
        return;
    }

    if (! validate_v4addr_subnetRange(dhcp_begin_addr, dhcp_end_addr)) {
        jAlert("Either Gateway or DHCP Begin/End Addresses are invaid, please input again!");
        return;
    }

    var gw_ip1 = parseInt($('#ipv4_gateway_address_1').val());
    var gw_ip2 = parseInt($('#ipv4_gateway_address_2').val());
    var gw_ip3 = parseInt($('#ipv4_gateway_address_3').val());
    if( ((gw_ip1 != 10) && (gw_ip1 != 172) && (gw_ip1 != 192)) || ((gw_ip1 == 172) && ((gw_ip2<16) || (gw_ip2>31)))  || ((gw_ip1== 192) && ((gw_ip2 != 168) || (gw_ip3== 147)) ) ){
		jAlert("Gateway IP is not in valid private IP range\n [10.0.0.1 ~ 10.255.255.253,\n172.16.0.1 ~ 172.31.255.253,\n192.168.0.1 ~ 192.168.146.253,\n192.168.148.1 ~ 192.168.255.253]");
    	return;
    }
    if ((gw_ip1==172) && (gw_ip2==16) && (gw_ip3==12)) {
    	jAlert("This IP address is reserved for Home Security, please input again");
    	return;
    }

	var dhcp_lease_num = $('#ipv4_dhcp_lease_time_amount').val();
	var dhcp_lease_unit = $('#ipv4_dhcp_lease_time_measure').val();
	var dhcp_lease_time = calcuate_lease_time(dhcp_lease_num, dhcp_lease_unit);

	if ((ipaddr == dhcp_begin_addr) || (ipaddr == dhcp_end_addr)){
    	jAlert("DHCP Beginning Address or DHCP Ending Address shoud not be the same with Gateway Address");
    	return;
    }
	
	var CM_GW_IP_Address = "<?php echo $CM_GW_IP_Address;?>";
	var CM_IP_Address = "<?php echo $CM_IP_Address;?>";
	var WAN_GW_IPv4_Address = "<?php echo $WAN_GW_IPv4_Address;?>";
	var LAN_GW_IPv4_Address = "<?php echo $LAN_GW_IPv4_Address;?>";

	if(ipaddr == CM_GW_IP_Address){
		jAlert("This IP address is reserved for CM Gateway IP Address, please input again!");
	    	return;
	}
	else if(ipaddr == CM_IP_Address){
                jAlert("This IP address is reserved for CM IP Address, please input again!");
                return;
        }
	else if(ipaddr == WAN_GW_IPv4_Address){
		jAlert("This IP address is reserved for WAN Gateway IPv4 Address, please input again!");
    		return;
	}
	else if(ipaddr == LAN_GW_IPv4_Address){
		jAlert("This IP address is reserved for Virtual LAN IPv4 Address, please input again!");
    		return;
	}
	else if(dhcp_begin_addr == dhcp_end_addr){
		jAlert("DHCP beginning and ending address cannot be same, Please input again!");
    		return;
	}
	var dns_manual = $('#dns_manual_ipv4').is(":checked");
    
    var IPv4Config = '{"Ipaddr":"' + ipaddr + '", "Subnet_mask":"' + subnet_mask + '", "Dhcp_begin_addr":"' + dhcp_begin_addr 
    + '", "Dhcp_end_addr":"' + dhcp_end_addr + '", "Dhcp_lease_time":"' + dhcp_lease_time + '","dns_manually":"' + dns_manual
    + '","primary_dns":"' + primary_dns + '","secondary_dns":"' + secondary_dns + '"}';
    if($("#pageForm").valid()){
	    var str = 'Changing Gateway IP or any DHCP setting would reset the gateway.\n';
		str += '\n';
		str += '<b>WARNING</b>: Gateway will be rebooted!\n';
		str += 'Incoming/Outgoing call and internet connection will be interrupted!';
		jConfirm(
		str
		,"Are you sure?"
		,function(ret) {
			if(ret) {
				if((login_user == "cusadmin") && (jsGwIP != ipaddr)) {
						jConfirm(
							"This may need you to relogin with new Gateway IP address"
							,"Are you sure?"
							,function(ret) {
								if(ret) {
									jProgress('Device is rebooting. Please be patient...', 600);
									$.ajax({
										type: "POST",
										url:  "actionHandler/ajaxSet_IP_configuration.php",
										data: {
											configInfo: IPv4Config
										},
										dataType: "json",
										//timeout:  15000,
										success: function(){ 
											//jAlert("Please login with new IP address ");
										},
										error: function(){ 
											//jAlert("Please login with new IP address ");
										}
									});//end of ajax
								
									setTimeout(function(){
										jHide();
										//console.log('http://' + ipaddr + '/index.php');
										window.location.replace('http://' + ipaddr + '/index.php');
									}, 90000);
								} //end of if ret
						}); //end of jConfirm
					} //end of login user
				else{
					setIPconfiguration(IPv4Config);
				}
			}
		});
	}
});

function setIPconfiguration(configuration){
  
	if($("#pageForm").valid()){
		jProgress('This may take several seconds...', 120);
		$.ajax({
			type: "POST",
			url: "actionHandler/ajaxSet_IP_configuration.php",
			data: { configInfo: configuration },
			success: function(){            
				jHide();
				jProgress("Please wait for rebooting ...", 999999);
				setTimeout(checkForRebooting, 3 * 50 * 1000);
			},
			error: function(){            
				jHide();
				//jAlert("Failure, please try again.");
			}
		});
	}
}

function checkForRebooting() {
    $.ajax({
    type: "GET",
    url: "index.php",
    timeout: 10000,
        success: function() {
                /* goto login page once the box reboots*/
                window.open ("index.php","_self");
                setTimeout(window.close(),1000);
           },
           error: function() {
                /* retry after 2 minutes */
                setTimeout(checkForRebooting, 30 * 1000);
           }
    });
}

  
function setIPv6configuration(configuration){
  
	if($("#pageFormV6").valid()){

		jProgress('This may take several seconds...', 120);
		$.ajax({
			type: "POST",
			url: "actionHandler/ajaxSet_IP_configuration.php",
			data: { configInfo: configuration },
			success: function(){            
				jHide();
				window.location.href = "local_ip_configuration.php";
			},
			error: function(){            
				jHide();
				//jAlert("Failure, please try again.");
			}
		});
	}
}

function calcuate_lease_time(num, unit){
   
    switch (unit) {
	case 'seconds':
		return num;
		break;
	case 'minutes':
		return num * 60;
		break;
	case 'hours':
		return num * 3600;
		break;
	case 'days': 
		return num * 3600 * 24;
		break;	
	case 'weeks': 
		return num * 3600 * 24 * 7;
		break;	
	case 'forever':
	    return -1;
	    break;	
   }
}

	display_time_format(jsLeaseTime);
	display_time_format_V6(jsV6LeaseTime);

function display_time_format(num){

	if (num == "") return;
    var timeNum = parseInt(num);
	var TimeVal  = '#ipv4_dhcp_lease_time_amount';

	if ( (timeNum % 604800) == 0) {
		$(TimeVal).val(timeNum / 604800);
		$('#ipv4_dhcp_lease_time_measure option[value="weeks"]').prop("selected", true);
	}
	else if( (timeNum % 86400) == 0 ) {
		$(TimeVal).val(timeNum / 86400);
		$('#ipv4_dhcp_lease_time_measure option[value="days"]').prop("selected", true);
	}
	else if( (timeNum % 3600) == 0 ) {
		$(TimeVal).val(timeNum / 3600);
		$('#ipv4_dhcp_lease_time_measure option[value="hours"]').prop("selected", true);
	}
	else if( (timeNum % 60) == 0 ) {
		$(TimeVal).val(timeNum / 60);
		$('#ipv4_dhcp_lease_time_measure option[value="minutes"]').prop("selected", true);
	}
	else if( timeNum == -1) {
		$(TimeVal).prop("disabled", true);
		$('#ipv4_dhcp_lease_time_measure option[value="forever"]').prop("selected", true);
	}
	else {
		$(TimeVal).val(timeNum);
		$('#ipv4_dhcp_lease_time_measure option[value="seconds"]').prop("selected", true);
	}
}

function display_time_format_V6(num){

	if (num == "") return;
    var timeNum = parseInt(num);
	var TimeVal  = '#ipv6_dhcp_lease_time_amount';

	if ( (timeNum % 604800) == 0) {
		$(TimeVal).val(timeNum / 604800);
		$('#ipv6_dhcp_lease_time_measure option[value="weeks"]').prop("selected", true);
	}
	else if( (timeNum % 86400) == 0 ) {
		$(TimeVal).val(timeNum / 86400);
		$('#ipv6_dhcp_lease_time_measure option[value="days"]').prop("selected", true);
	}
	else if( (timeNum % 3600) == 0 ) {
		$(TimeVal).val(timeNum / 3600);
		$('#ipv6_dhcp_lease_time_measure option[value="hours"]').prop("selected", true);
	}
	else if( (timeNum % 60) == 0 ) {
		$(TimeVal).val(timeNum / 60);
		$('#ipv6_dhcp_lease_time_measure option[value="minutes"]').prop("selected", true);
	}
	else if( timeNum == -1) {
		$(TimeVal).prop("disabled", true);
		$('#ipv6_dhcp_lease_time_measure option[value="forever"]').prop("selected", true);
	}
	else {
		$(TimeVal).val(timeNum);
		$('#ipv6_dhcp_lease_time_measure option[value="seconds"]').prop("selected", true);
	}
}


function populateIPv6Addr(v6addr){
	if (!isValidIp6Str(v6addr)) {
		return [];
	}
    var v6_arr = new Array();
	var arr = v6addr.split("::");
	if (arr[1] != undefined) { //:: exist
		var arr_first = arr[0].split(':');
		var arr_second = arr[1].split(':');
		var arr1_num = arr_first.length;
		var arr2_num = arr_second.length;
		var zero_num = 8 - arr1_num - arr2_num;

		if (arr1_num == 0) v6_arr[0] = 0;
	    for (var i = 0; i < arr1_num ; i++) {
	    	v6_arr[i] = arr_first[i];
	    }
	    for (var i = arr1_num, j = 0; j<zero_num; i++, j++) {
	    	v6_arr[i] = 0;
	    }
	    for (var i = arr1_num + zero_num, j = 0; j < arr2_num; i++, j++) {
	    	v6_arr[i] = arr_second[j];
	    }
	} //end of if undefined
	else{
	    v6_arr = v6addr.split(':');
	}
    return v6_arr;
}

	var ipv6_global_addr = "<?php echo $ipv6_global_addr; ?>";
	var ipv6_global_arr = populateIPv6Addr(ipv6_global_addr);

	$("#GGA_1").val(ipv6_global_arr[0]);
    $("#GGA_2").val(ipv6_global_arr[1]);
    $("#GGA_3").val(ipv6_global_arr[2]);
    $("#GGA_4").val(ipv6_global_arr[3]);
    $("#GGA_5").val(ipv6_global_arr[4]);
    $("#GGA_6").val(ipv6_global_arr[5]);
    $("#GGA_7").val(ipv6_global_arr[6]);
    $("#GGA_8").val(ipv6_global_arr[7]);

function updateIPv6(){

	if ($('#Stateful').is(":checked")) {
	    $('#DBA_5').prop("disabled", false);
	    $('#DBA_6').prop("disabled", false);
	    $('#DBA_7').prop("disabled", false);
	    $('#DBA_8').prop("disabled", false);
	    $('#DEA_5').prop("disabled", false);
	    $('#DEA_6').prop("disabled", false);
	    $('#DEA_7').prop("disabled", false);
	    $('#DEA_8').prop("disabled", false);
	    if ($('#ipv6_dhcp_lease_time_measure').val() != "forever") 
	    	$('#ipv6_dhcp_lease_time_amount').prop("disabled", false);
	    $('#ipv6_dhcp_lease_time_measure').prop("disabled", false);
	}
	else{
		$('#DBA_5').prop("disabled", true);
	    $('#DBA_6').prop("disabled", true);
	    $('#DBA_7').prop("disabled", true);
	    $('#DBA_8').prop("disabled", true);
	    $('#DEA_5').prop("disabled", true);
	    $('#DEA_6').prop("disabled", true);
	    $('#DEA_7').prop("disabled", true);
	    $('#DEA_8').prop("disabled", true);
	    $('#ipv6_dhcp_lease_time_amount').prop("disabled", true);
	    $('#ipv6_dhcp_lease_time_measure').prop("disabled", true);
	}
}

updateIPv6();

$('#Stateful').click(function(){
	updateIPv6();
	$("#pageFormV6").valid();
	$("#ipv6_dhcp_lease_time_amount").removeClass("error");
})

/* This function checks ending address should be larger than begin address */
function validate_v6addr_pool (DBArr, DEArr) {
	
	var flag = true;

	if (DEArr[0] < DBArr[0]) {
		flag = false;
	}
	else if (DEArr[0] == DBArr[0]) {
		if (DEArr[1] < DBArr[1]) {
			flag = false;
		}
		else if (DEArr[1] == DBArr[1]) {
			if (DEArr[2] < DBArr[2]) {
				flag = false;
			}
			else if (DEArr[2] == DBArr[2]) {
				if (DEArr[3] < DBArr[3]) {
					flag = false;
				}
			}
		}
	}	
	return flag;
}

$('#submit_ipv6').click(function(e){
	e.preventDefault();

	//convert to int with radix 16
    var DBA_5 = parseInt($('#DBA_5').val(), 16);
    var DBA_6 = parseInt($('#DBA_6').val(), 16);
    var DBA_7 = parseInt($('#DBA_7').val(), 16);
    var DBA_8 = parseInt($('#DBA_8').val(), 16);
    var DEA_5 = parseInt($('#DEA_5').val(), 16);
    var DEA_6 = parseInt($('#DEA_6').val(), 16);
    var DEA_7 = parseInt($('#DEA_7').val(), 16);
    var DEA_8 = parseInt($('#DEA_8').val(), 16);

    var DBArr = Array(DBA_5, DBA_6, DBA_7, DBA_8);
    var DEArr = Array(DEA_5, DEA_6, DEA_7, DEA_8);
    
    var primary_dns_1 = $('#ipv6_primary_dns_1').val();
    var secondary_dns_1 = $('#ipv6_secondary_dns_1').val();
    var primary_dns_val = primary_dns_1;
    var secondary_dns_val = secondary_dns_1;
    for (var j=2;j<9;j++) {
    	var primary_dns_j = $('#ipv6_primary_dns_'+j).val();
    	primary_dns_val = primary_dns_val+":"+primary_dns_j;
    	var secondary_dns_j = $('#ipv6_secondary_dns_'+j).val();
    	secondary_dns_val = secondary_dns_val+":"+secondary_dns_j;
    }
   	var dns_manually_ipv6 = $('#dns_manually_ipv6').is(":checked");
    if (! validate_v6addr_pool(DBArr, DEArr)) {
    	jAlert("DHCPv6 beginning address can't be larger than ending address!");
    	return;
    }
   
    var Stateful = $('#Stateful').is(":checked"); //bool, true/false
    var dhcpv6_begin_addr = $('#DBA_5').val() + ":" + $('#DBA_6').val() + ":" + $('#DBA_7').val() + ":" + $('#DBA_8').val();
    var dhcpv6_end_addr   = $('#DEA_5').val() + ":" + $('#DEA_6').val() + ":" + $('#DEA_7').val() + ":" + $('#DEA_8').val();
    var dhcp_lease_num = $('#ipv6_dhcp_lease_time_amount').val();
	var dhcp_lease_unit = $('#ipv6_dhcp_lease_time_measure').val();
	var dhcpv6_lease_time = calcuate_lease_time(dhcp_lease_num, dhcp_lease_unit);
    
    var IPv6Config = '{"IPv6": "Yes", "Stateful": "' + Stateful + '", "dhcpv6_begin_addr": "' + dhcpv6_begin_addr + '", "dhcpv6_end_addr": "' + dhcpv6_end_addr 
    	+'", "dhcpv6_lease_time": "' + dhcpv6_lease_time + '", "dns_manually_ipv6": "' + dns_manually_ipv6 + '", "primary_dns": "' + primary_dns_val 
    	+ '", "secondary_dns": "' + secondary_dns_val + '"}';

   	setIPv6configuration(IPv6Config);

});


//========enable/disable Lan DHCP server
$('#Lan').click(function(){
	if ( $(this).is(':checked') ) {
		var configInfo = '{enableDHCP:"true"}';		
	}
	else{
		var configInfo = '{enableDHCP:"false"}';	
	}
	jProgress("This may take several seconds.",60);
		$.ajax({
		type:"POST",
		url:"actionHandler/ajaxSet_IP_configuration.php",
		data: eval('('+configInfo+')'),
		success:function(){
			jHide();
			window.location.reload();
		},
		error: function(){
			jHide();
			jAlert("Error! Please try later!");
		}
	});
});

var DHCPEnable = "<?php echo $DHCPEnable; ?>";
if (DHCPEnable == 'false'){
	$("#ipv4_gateway_address_1").prop("disabled", true);
	$("#ipv4_gateway_address_2").prop("disabled", true);
	$("#ipv4_gateway_address_3").prop("disabled", true);
	$("#ipv4_gateway_address_4").prop("disabled", true);
	$("#ipv4_subnet_mask_1").prop("disabled", true);
	$("#ipv4_subnet_mask_2").prop("disabled", true);
	$("#ipv4_subnet_mask_3").prop("disabled", true);
	$("#ipv4_subnet_mask_4").prop("disabled", true);
	$("#ipv4_dhcp_beginning_address_4").prop("disabled", true);
	$("#ipv4_dhcp_ending_address_4").prop("disabled", true);
	$("#ipv4_dhcp_lease_time_amount").prop("disabled", true);
	$("#ipv4_dhcp_lease_time_measure").prop("disabled", true);
	$('#dns_manual_ipv4').prop('disabled', true);
	$('#submit_ipv4').prop("disabled", true);
	for(var i=1;i<=4;i++){
				$('#ipv4_primary_dns_'+i).prop("disabled", true);
				$('#ipv4_secondary_dns_'+i).prop("disabled", true);
			}
}


$('#restore_ipv6').click(function(e) {
	e.preventDefault();

	jConfirm(
	"Are you sure you want the change LAN IPv6 to default settings?"
	,"Reset Default IPv6 Settings"
	,function(ret) {
	if(ret) {
		$('#DBA_5').val(0);
		$('#DBA_6').val(0);
		$('#DBA_7').val(0);
		$('#DBA_8').val(1);
		$('#DEA_5').val(0);
		$('#DEA_6').val(0);
		$('#DEA_7').val(0);
		$('#DEA_8').val("fffe");
		$("#ipv6_dhcp_lease_time_amount").val(1);
		$("#ipv6_dhcp_lease_time_measure").val("weeks");
		$('#dns_manually_ipv6').attr('checked', false);
		disablevalipv6();
		
		/*$("#ipv6_primary_dns_1").val(2001);
		$("#ipv6_primary_dns_2").val(558);
		$("#ipv6_primary_dns_3").val("feed");
		$("#ipv6_primary_dns_4").val("0");
		$("#ipv6_primary_dns_5").val(1);
		$("#ipv6_primary_dns_6").val("0");
		$("#ipv6_primary_dns_7").val("0");
		$("#ipv6_primary_dns_8").val("0");

		$("#ipv6_secondary_dns_1").val(2001);
		$("#ipv6_secondary_dns_2").val(558);
		$("#ipv6_secondary_dns_3").val("feed");
		$("#ipv6_secondary_dns_4").val("0");
		$("#ipv6_secondary_dns_5").val(2);
		$("#ipv6_secondary_dns_6").val("0");
		$("#ipv6_secondary_dns_7").val("0");
		$("#ipv6_secondary_dns_8").val("0");*/
		
		var dhcpv6_begin_addr = "0:0:0:1";
		var dhcpv6_end_addr = "0:0:0:fffe";
		var dhcpv6_lease_time = 604800;    // 1 week  
		/*var ipv6_primary_dns = "2001:558:feed:0:1:0:0:0";
		var ipv6_secondary_dns = "2001:558:feed:0:2:0:0:0";*/
	    
        var IPv6Config = '{"IPv6": "Yes", "restore": "true", "dhcpv6_begin_addr": "' + dhcpv6_begin_addr + '", "dhcpv6_end_addr": "' + dhcpv6_end_addr 
		+ '", "dhcpv6_lease_time": "' + dhcpv6_lease_time + '", "dns_manually_ipv6": "false"}';

    	setIPv6configuration(IPv6Config);
	} //end of if ret
	}); //end of jconfirm
});//end of click restore ipv6

	//if DeviceMode is Ipv4 then DHCPv6 parameters should be grayed out on the page
	var DeviceMode = "<?php echo $DeviceMode; ?>";
	if(DeviceMode == "Ipv4"){
		$('#Stateful').prop("disabled", true);
		$('#DBA_5').prop("disabled", true);
		$('#DBA_6').prop("disabled", true);
		$('#DBA_7').prop("disabled", true);
		$('#DBA_8').prop("disabled", true);
		$('#DEA_5').prop("disabled", true);
		$('#DEA_6').prop("disabled", true);
		$('#DEA_7').prop("disabled", true);
		$('#DEA_8').prop("disabled", true);
		$('#ipv6_dhcp_lease_time_amount').prop("disabled", true);
		$('#ipv6_dhcp_lease_time_measure').prop("disabled", true);
	}

}); //end of document ready

	function praseIpToBinary(ipAddress){
		var numArray = ipAddress.split(".");
		if(numArray.length != 4){
			return;
		}
		var returnIpStr = "";
		for (var i = 0; i < 4; i++) {
			var curr_num = numArray[i];
			var number_Bin = parseInt(curr_num);
			number_Bin = number_Bin.toString(2);
			var iCount = 8-number_Bin.length;
			for (var j = 0; j < iCount; j++) {
				number_Bin = "0"+number_Bin;
			}
			returnIpStr += number_Bin;
		}
		return returnIpStr;
	}

	function judgeSubnetMask(ipAddress){
		var ip_addr = $("#ipv4_gateway_address_1").val();
		var exp="";
		if(ip_addr=="10"){
			exp="^(((255\.){2}(255|254|252|248|240|224|192|128|0+)(\.(252|248|240|224|192|128|0+)))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))$";
		}else if(ip_addr=="172"){
			exp="^(((255\.255\.255\.)(252|248|240|224|192|128|0+))|((255\.255\.)(255|254|252|248|240|224|192|128|0+)\.0))$";
		}else if(ip_addr=="192"){
			exp="^(((255\.255\.255\.)(252|248|240|224|192|128|0+)))$";
		}
	var str = ipAddress;
	var patt = new RegExp(exp);
	var res= patt.test(str);
	if(patt.test(str)==true)
		return true;
	else
		return false;
	}
	function netmask_is_validate()
	{
		var netmask = $("#ipv4_subnet_mask_1").val() + "." + $("#ipv4_subnet_mask_2").val() + "." + $("#ipv4_subnet_mask_3").val() + "." + $("#ipv4_subnet_mask_4").val();
		return judgeSubnetMask(netmask);
	}
		function long2ip (ip) {
			if (!isFinite(ip))
			return false

			return [ip >>> 24, ip >>> 16 & 0xFF, ip >>> 8 & 0xFF, ip & 0xFF].join('.')
		}
//  discuss at: https://locutus.io/php/ip2long/
// original by: Waldo Malqui Silva (https://waldo.malqui.info)
// improved by: Victor
//  revised by: fearphage (https://my.opera.com/fearphage/)
//  revised by: Theriault (https://github.com/Theriault)
//    estarget: es2015
//   example 1: ip2long('192.0.34.166')
//   returns 1: 3221234342
//   example 2: ip2long('0.0xABCDEF')
//   returns 2: 11259375
//   example 3: ip2long('255.255.255.256')
//   returns 3: false
		function ip2long (IP) {
			var i = 0

			IP = IP.match(
				/^([1-9]\d*|0[0-7]*|0x[\da-f]+)(?:\.([1-9]\d*|0[0-7]*|0x[\da-f]+))?(?:\.([1-9]\d*|0[0-7]*|0x[\da-f]+))?(?:\.([1-9]\d*|0[0-7]*|0x[\da-f]+))?$/i
			) // Verify IP format.
			if (!IP) {
				// Invalid format.
				return false
			}
			// Reuse IP variable for component counter.
			IP[0] = 0
			for (i = 1; i < 5; i += 1) {
				IP[0] += !!((IP[i] || '')
				.length)
				IP[i] = parseInt(IP[i]) || 0
			}
			// Continue to use IP for overflow values.
			IP.push(256, 256, 256, 256)
			IP[4 + IP[0]] *= Math.pow(256, 4 - IP[0])
			if (IP[1] >= IP[5] || IP[2] >= IP[6] || IP[3] >= IP[7] || IP[4] >= IP[8]) {
				return false
			}
			return IP[1] * (IP[0] === 1 || 16777216) + IP[2] * (IP[0] <= 2 || 65536) + IP[3] * (IP[0] <= 3 || 256) + IP[4] * 1
		}	
		function replaceNaNwithEmptyString(value) {
			if(isNaN(value)) {
				return "";
			} else {
				return value;
			}
		}	
		function disablevalipv4(){
			var val=$('#dns_manual_ipv4').is(":checked");
			for(var i=1;i<=4;i++){
				$('#ipv4_primary_dns_'+i).prop("disabled", !val);
				$('#ipv4_secondary_dns_'+i).prop("disabled", !val);
			}
		}
		function disablevalipv6(){
			
			var val=$('#dns_manually_ipv6').is(":checked");
			for(var i=1;i<9;i++){
				$('#ipv6_primary_dns_'+i).prop("disabled", !val);
				$('#ipv6_secondary_dns_'+i).prop("disabled", !val);
			}
		
		}
</script>

<div id="content">
	<h1>Gateway > Connection > Local IP Configuration</h1>

	<div id="educational-tip">
			<p class="tip">Manage your home network settings.</p>
			<p class="hidden"><strong>Gateway address:</strong> Enter the IP address of the Gateway.</p>
			<p class="hidden"><strong>Subnet Mask:</strong> The subnet mask is associated with the IP address. Select the appropriate subnet mask based on the number of devices that will be connected to your network.</p>
	<p class="hidden"><strong>DHCP Beginning and Ending Addresses:</strong> The DHCP server in the Gateway allows the router to manage IP address assignment for the connected devices.</p>
			<p class="hidden"><strong>DHCP Lease time:</strong> The lease time is the length of time the Gateway offers an IP address to a connected device. The lease is renewed while it is connected to the network. After the time expires, the IP address is freed and may be assigned to any new device that connects to the Gateway.</p>
	</div>


	<form action="#TBD" method="post" id="pageForm">
    <div class="module forms">
   	    <h2>IPv4</h2>
			<div id="dhcp-portion">
			<div class="form-row odd">
				<input type="checkbox"  name="lan" <?php if ($DHCPEnable == 'true') echo 'checked';  ?> value="Lan" id="Lan" />
				<label for="Lan" class="acs-hide"></label>
				<b>Enable LAN DHCP</b>
			</div>	
    		<div class="form-row">
    			<label for="ipv4_gateway_address_1">Gateway Address:</label>
    			<?php
    			   $ipv4_addr = $LanGwIP;
    			   $ipArr = explode(".", $ipv4_addr);
    			?>
                <input type="text" size="3" maxlength="3"  value="<?php echo $ipArr['0']; ?>" id="ipv4_gateway_address_1" name="ipv4_gateway_address_1" class="gateway_address smallInput"> 
    	        <label for="ipv4_gateway_address_2" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $ipArr['1']; ?>" id="ipv4_gateway_address_2" name="ipv4_gateway_address_2" class="gateway_address smallInput"> 
    	        <label for="ipv4_gateway_address_3" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $ipArr['2']; ?>" id="ipv4_gateway_address_3" name="ipv4_gateway_address_3" class="gateway_address smallInput"> 
    	        <label for="ipv4_gateway_address_4" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $ipArr['3']; ?>" id="ipv4_gateway_address_4" name="ipv4_gateway_address_4" class="gateway_address smallInput" />
    		</div>
    		<div class="form-row odd">
    			<label for="ipv4_subnet_mask">Subnet Mask:</label>
    			<?php 
                   		$subnetmask = getStr("Device.X_CISCO_COM_DeviceControl.LanManagementEntry.1.LanSubnetMask");
				$mask = explode(".", $subnetmask); 
    			?>
				<input type="text" size="3" maxlength="3"  value="<?php echo $mask['0']; ?>" id="ipv4_subnet_mask_1" name="ipv4_subnet_mask_1" class="gateway_address smallInput" /> 
    	        <label for="ipv4_subnet_mask_2" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $mask['1']; ?>" id="ipv4_subnet_mask_2" name="ipv4_subnet_mask_2" class="gateway_address smallInput" /> 
    	        <label for="ipv4_subnet_mask_3" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $mask['2']; ?>" id="ipv4_subnet_mask_3" name="ipv4_subnet_mask_3" class="gateway_address smallInput" /> 
    	        <label for="ipv4_subnet_mask_4" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $mask['3']; ?>" id="ipv4_subnet_mask_4" name="ipv4_subnet_mask_4" class="gateway_address smallInput" />
               
    	    </div>
    		<div class="form-row">
    			<label for="ipv4_dhcp_beginning_address_1">DHCP Beginning Address:</label>
<!--     			<span id="ipv4_dhcp_beginning_address" class="readonlyValue"></span> -->
                <?php  
    			   $beginAddr = $dhcpv4_value["beginAddr"];
    			   $beginArr = explode(".", $beginAddr);    			   
    			?>  
    			<input type="text" size="3" maxlength="3" value="<?php echo $beginArr['0']; ?>" id="ipv4_dhcp_beginning_address_1" name="ipv4_dhcp_beginning_address_1" class="smallInput" />
    	        <label for="ipv4_dhcp_beginning_address_2" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $beginArr['1']; ?>" id="ipv4_dhcp_beginning_address_2" name="ipv4_dhcp_beginning_address_2" class="smallInput" />
    	        <label for="ipv4_dhcp_beginning_address_3" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $beginArr['2']; ?>" id="ipv4_dhcp_beginning_address_3" name="ipv4_dhcp_beginning_address_3" class="smallInput" />
    	        <label for="ipv4_dhcp_beginning_address_4" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $beginArr['3']; ?>" id="ipv4_dhcp_beginning_address_4" name="ipv4_dhcp_beginning_address_4" class="smallInput" />
    		</div>
    		<div class="form-row odd">
                <label for="ipv4_dhcp_ending_address_1">DHCP Ending Address:</label>
                <?php  
    			   $endAddr = $dhcpv4_value["endAddr"];
    			   $endArr = explode(".", $endAddr);    
    			?> 
				<input type="text" size="3" maxlength="3" value="<?php echo $endArr['0']; ?>" id="ipv4_dhcp_ending_address_1" name="ipv4_dhcp_ending_address_1" class="smallInput" />
    	        <label for="ipv4_dhcp_ending_address_2" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $endArr['1']; ?>" id="ipv4_dhcp_ending_address_2" name="ipv4_dhcp_ending_address_2" class="smallInput" />
    	        <label for="ipv4_dhcp_ending_address_3" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $endArr['2']; ?>" id="ipv4_dhcp_ending_address_3" name="ipv4_dhcp_ending_address_3" class="smallInput" />
    	        <label for="ipv4_dhcp_ending_address_4" class="acs-hide"></label>
    	        .<input type="text" size="3" maxlength="3" value="<?php echo $endArr['3']; ?>" id="ipv4_dhcp_ending_address_4" name="ipv4_dhcp_ending_address_4" class="smallInput"  />
    	       
    		 </div>
    		<div class="form-row" id="ipv4_dhcp_lease_time">
    			<label for="ipv4_dhcp_lease_time_amount">DHCP Lease Time:</label>                 
    			<input type="text" maxlength="3" id="ipv4_dhcp_lease_time_amount" name="ipv4_dhcp_lease_time_amount" class="smallInput" />
    			<label for="ipv4_dhcp_lease_time_measure" class="acs-hide"></label>
                <select id="ipv4_dhcp_lease_time_measure" name="ipv4_dhcp_lease_time_measure">
    	            <option value="seconds">Seconds</option>
    	            <option value="minutes">Minutes</option>
    	            <option value="hours">Hours</option>
    	            <option value="days">Days</option>
    	            <option value="weeks">Weeks</option>
    	            <option value="forever">Forever</option>
    	        </select>
    		</div>

			<div class="form-row odd" id="assign_dns_manually">
				<?php
					$checked= (($ipv4_dns_enable_1=="true") && ($ipv4_dns_enable_2=="true"))?"checked=checked":"";
				?>
				<input type="checkbox"  name="dns_manually" value="dns_manual_ipv4" id="dns_manual_ipv4" onclick="return disablevalipv4();" <?php echo $checked;?> />
				<label for="DnsManual" class="acs-hide"></label> <b>Assign DNS Manually</b>
			</div>
				<div class="form-row">
					<label for="primary_dns_1">Primary DNS:</label>
						<?php
							$ipv4_primary_dns = $ipv4_primary_dns;
							$ipv4dns = explode(".", $ipv4_primary_dns);
						?>
					<input type="text" size="3" maxlength="3" id="ipv4_primary_dns_1" name="ipv4_primary_dns_1" class="smallInput" value="<?php echo $ipv4dns['0']; ?>"/>
					<label for="primary_dns_2" class="acs-hide"></label>
					.<input type="text" size="3" maxlength="3" id="ipv4_primary_dns_2" name="ipv4_primary_dns_2" class="smallInput" value="<?php echo $ipv4dns['1']; ?>"/>
					<label for="primary_dns_3" class="acs-hide"></label>
					.<input type="text" size="3" maxlength="3"  id="ipv4_primary_dns_3" name="ipv4_primary_dns_3" class="smallInput" value="<?php echo $ipv4dns['2']; ?>"/>
					<label for="primary_dns_4" class="acs-hide"></label>
					.<input type="text" size="3" maxlength="3"  id="ipv4_primary_dns_4" name="ipv4_primary_dns_4" class="smallInput"  value="<?php echo $ipv4dns['3']; ?>"/>
				</div>
				<div class="form-row odd">
					<label for="secondary_dns_1">Secondary DNS:</label>
						<?php
							$ipv4_secondary_dns = $ipv4_secondary_dns;
							$ipv4secdns = explode(".", $ipv4_secondary_dns);
						?>
					<input type="text" size="3" maxlength="3" id="ipv4_secondary_dns_1" name="ipv4_secondary_dns_1" class="smallInput" value="<?php echo $ipv4secdns['0']; ?>"/>
					<label for="secondary_dns_2" class="acs-hide"></label>
					.<input type="text" size="3" maxlength="3" id="ipv4_secondary_dns_2" name="ipv4_secondary_dns_2" class="smallInput"  value="<?php echo $ipv4secdns['1']; ?>"/>
					<label for="secondary_dns_3" class="acs-hide"></label>
					.<input type="text" size="3" maxlength="3"  id="ipv4_secondary_dns_3" name="ipv4_secondary_dns_3" class="smallInput"  value="<?php echo $ipv4secdns['2']; ?>"/>
					<label for="secondary_dns_4" class="acs-hide"></label>
					.<input type="text" size="3" maxlength="3"  id="ipv4_secondary_dns_4" name="ipv4_secondary_dns_4" class="smallInput"  value="<?php echo $ipv4secdns['3']; ?>" />
				</div>
		</div> <!-- end of dhcp portion -->

  	    		<div class="form-btn">
					<input id="submit_ipv4" type="button" value="Save Settings" class="btn" />
					<input id="restore-default-settings-ipv4" type="button" value="Restore Default Settings" class="btn alt" />
				</div>
    		</div> <!-- End Module -->
			</form>

			<form action="#TBD" method="post" id="pageFormV6">
    		 <div class="module forms">
    		 <h2>IPv6</h2>

    	   <div class="form-row odd">				

				<label for="LLGA_1">Link-Local Gateway Address:</label>
				<input type="text"  class="ipv6-input" size="2" maxlength="2"  id="LLGA_1" name="LLGA_1" disabled="disabled" value="fe80"/>
				<label for="LLGA_2" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="2" id="LLGA_2" name="LLGA_2"  disabled="disabled" value="0"/>
	    	    <label for="LLGA_3" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="2" id="LLGA_3" name="LLGA_3" disabled="disabled" value="0"/>
	    	    <label for="LLGA_4" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="2" id="LLGA_4" name="LLGA_4" disabled="disabled" value="0"/>
	    	    <label for="LLGA_5" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="2" id="LLGA_5" name="LLGA_5" disabled="disabled" value="<?php echo $local_ipv6_arr[0]; ?>" />
	    	    <label for="LLGA_6" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="2" id="LLGA_6" name="LLGA_6" disabled="disabled" value="<?php echo $local_ipv6_arr[1]; ?>" />
	    	    <label for="LLGA_7" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="2" id="LLGA_7" name="LLGA_7" disabled="disabled" value="<?php echo $local_ipv6_arr[2]; ?>" />
	    	    <label for="LLGA_8" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="2" id="LLGA_8" name="LLGA_8" disabled="disabled" value="<?php echo $local_ipv6_arr[3]; ?>" />
					
				<br/>
				
				</div> 

				<div class="form-row ">
				

				<label for="GGA_1">Global Gateway Address:</label>
				<input type="text"  class="ipv6-input" size="2" maxlength="4"  id="GGA_1" name="GGA_1" disabled="disabled" >
	    	    <label for="GGA_2" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="GGA_2" name="GGA_2" disabled="disabled" >
	    	    <label for="GGA_3" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="GGA_3" name="GGA_3" disabled="disabled" >
	    	    <label for="GGA_4" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="GGA_4" name="GGA_4" disabled="disabled" > 
	    	    <label for="GGA_5" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="GGA_5" name="GGA_5" disabled="disabled" >
	    	    <label for="GGA_6" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="GGA_6" name="GGA_6" disabled="disabled" >
	    	    <label for="GGA_7" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="4" id="GGA_7" name="GGA_7" disabled="disabled" >
	    	    <label for="GGA_8" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="4" id="GGA_8" name="GGA_8" disabled="disabled" >
					
				<br/>
				
				</div> 
				
				
			<div class="form-row odd">	<p><strong>LAN IPv6 Address Assignment</strong></p></div>
			<div class="form-row ">
			<?php 			    
			    $state = $dhcpv6_value["state"];
			?>	
				<input type="checkbox"  name="State" value="Stateless" checked="checked" id="Stateless" disabled="disabled" />
				<label for="Stateless" class="acs-hide"></label> <b>Stateless(Auto-Config)</b>
				<input type="checkbox"  name="State" value="Stateful" <?php if($state == 'Stateful') echo 'checked="checked"'; ?> id="Stateful" />
				<label for="Stateful" class="acs-hide"></label> <b>Stateful(Use Dhcp Server)</b>
			</div>
				
    	   <div class="form-row odd">
				
			    <?php  
			      //2040::/64, 2040:1::/64, 2040:1:2::/64 and 2040:1:2:3::/64
                  $prefix_arr = explode('::/', getStr("Device.IP.Interface.1.IPv6Prefix.1.Prefix"));
                  $ipv6_prefix_arr = explode(':', $prefix_arr[0]);
                  $ipa_size = count($ipv6_prefix_arr);

			      $v6_begin_addr = $dhcpv6_value["v6_begin_addr"];
			      $v6_beg_add_arr = explode(':', $v6_begin_addr);
			    ?>	

				<label for="DBA_1">DHCPv6 Beginning Address:</label>
				<input type="text"  class="ipv6-input" size="2" maxlength="4"  id="DBA_1" name="DBA_1" disabled="disabled" value="<?php echo $ipv6_prefix_arr[0]; ?>" />
	    	    <label for="DBA_2" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_2" name="DBA_2" disabled="disabled" value="<?php if($ipa_size > 1) echo $ipv6_prefix_arr[1]; else echo "0"; ?>" />
	    	    <label for="DBA_3" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_3" name="DBA_3" disabled="disabled" value="<?php if($ipa_size > 2) echo $ipv6_prefix_arr[2]; else echo "0"; ?>" />
	    	    <label for="DBA_4" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_4" name="DBA_4" disabled="disabled" value="<?php if($ipa_size > 3) echo $ipv6_prefix_arr[3]; else echo "0"; ?>" />
	    	    <label for="DBA_5" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_5" name="DBA_5" disabled="disabled" value="<?php echo $v6_beg_add_arr[0]; ?>" />
	    	    <label for="DBA_6" class="acs-hide"></label>
        	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_6" name="DBA_6" disabled="disabled" value="<?php echo $v6_beg_add_arr[1]; ?>" />
	    	    <label for="DBA_7" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_7" name="DBA_7" disabled="disabled" value="<?php echo $v6_beg_add_arr[2]; ?>" />
	    	    <label for="DBA_8" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_8" name="DBA_8" disabled="disabled" value="<?php echo $v6_beg_add_arr[3]; ?>" />
	    	    <label for="DBA_9" class="acs-hide"></label>
					/<input type="text" class="ipv6-input" size="2" maxlength="4" id="DBA_9" name="DBA_9" disabled="disabled" value="64"/>
					
				<br/>
				
				</div> 		

    	   <div class="form-row ">
				
				<?php  
			      $v6_end_addr = $dhcpv6_value["v6_end_addr"];
			      $v6_end_add_arr = explode(':', $v6_end_addr);
			    ?>	

				<label for="DEA_1">DHCPv6 Ending Address:</label>
				<input type="text"  class="ipv6-input" size="2" maxlength="4"  id="DEA_1" name="DEA_1" disabled="disabled" value="<?php echo $ipv6_prefix_arr[0]; ?>" />
	    	    <label for="DEA_2" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_2" name="DEA_2" disabled="disabled" value="<?php if($ipa_size > 1) echo $ipv6_prefix_arr[1]; else echo "0"; ?>" />
	    	    <label for="DEA_3" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_3" name="DEA_3" disabled="disabled" value="<?php if($ipa_size > 2) echo $ipv6_prefix_arr[2]; else echo "0"; ?>" />
	    	    <label for="DEA_4" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_4" name="DEA_4" disabled="disabled" value="<?php if($ipa_size > 3) echo $ipv6_prefix_arr[3]; else echo "0"; ?>" />
	    	    <label for="DEA_5" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_5" name="DEA_5" disabled="disabled" value="<?php echo $v6_end_add_arr[0]; ?>" />
	    	    <label for="DEA_6" class="acs-hide"></label>
	    	        :<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_6" name="DEA_6" disabled="disabled" value="<?php echo $v6_end_add_arr[1]; ?>" />
	    	    <label for="DEA_7" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_7" name="DEA_7" disabled="disabled" value="<?php echo $v6_end_add_arr[2]; ?>" />
	    	    <label for="DEA_8" class="acs-hide"></label>
					:<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_8" name="DEA_8" disabled="disabled" value="<?php echo $v6_end_add_arr[3]; ?>" />
	    	    <label for="DEA_9" class="acs-hide"></label>
					/<input type="text" class="ipv6-input" size="2" maxlength="4" id="DEA_9" name="DEA_9" disabled="disabled" value="64"/>
					
				<br/>
				
				</div> 		

    		<div class="form-row odd" id="ipv6_dhcp_lease_time">
    			<label for="ipv6_dhcp_lease_time_amount">DHCPv6 Lease Time:</label>
    			<input type="text" size="3" maxlength="3" id="ipv6_dhcp_lease_time_amount" name="ipv6_dhcp_lease_time_amount" class="smallInput" />
                <label for="ipv6_dhcp_lease_time_measure" class="acs-hide"></label>
                <select id="ipv6_dhcp_lease_time_measure" name="ipv6_dhcp_lease_time_measure">
    	            <option value="seconds">Seconds</option>
    	            <option value="minutes">Minutes</option>
    	            <option value="hours">Hours</option>
    	            <option value="days">Days</option>
    	            <option selected value="weeks">Weeks</option>
    	            <option value="forever">Forever</option>
    	        </select>
    		</div>
    		 <div class="form-row" id="assign_dns_manually_ipv6">
				<?php
					$checked_ipv6= (($ipv6_dns_enable_1=="true") && ($ipv6_dns_enable_2=="true"))?"checked=checked":"";
				?>
				<input type="checkbox"  name="dns_manually_ipv6" id="dns_manually_ipv6"  onclick="return disablevalipv6();" <?php echo $checked_ipv6;?> />
				<label for="DnsManual" class="acs-hide"></label> <b>Assign DNS Manually</b>
			</div>
				<div class="form-row odd">
					<label for="primary_dns_ipv6_1">Primary DNS:</label>
					<?php
						$ipv6_primary_dns = $ipv6_primary_dns;
						$ipv6dns = explode(":", $ipv6_primary_dns);
					?>
					<input type="text" size="3" maxlength="4" id="ipv6_primary_dns_1" name="ipv6_primary_dns_1" class="ipv6-input" value="<?php echo $ipv6dns[0]; ?>"/>
					<label for="primary_dns_ipv6_2" class="acs-hide"></label>
					:<input type="text" size="3" maxlength="4" id="ipv6_primary_dns_2" name="ipv6_primary_dns_2" class="ipv6-input" value="<?php echo $ipv6dns[1]; ?>" />
					<label for="primary_dns_ipv6_3" class="acs-hide"></label>
					:<input type="text" size="3" maxlength="4"  id="ipv6_primary_dns_3" name="ipv6_primary_dns_3" class="ipv6-input" value="<?php echo $ipv6dns[2]; ?>"/>
					<label for="primary_dns_ipv6_4" class="acs-hide"></label>
					:<input type="text" size="3" maxlength="4"  id="ipv6_primary_dns_4" name="ipv6_primary_dns_4" class="ipv6-input" value="<?php echo $ipv6dns[3]; ?>" />
					<label for="primary_dns_ipv6_5" class="acs-hide"></label>
					:<input type="text" size="3" maxlength="4"  id="ipv6_primary_dns_5" name="ipv6_primary_dns_5" class="ipv6-input" value="<?php echo $ipv6dns[4]; ?>" />
					<label for="primary_dns_ipv6_6" class="acs-hide"></label>
					:<input type="text" size="3" maxlength="4"  id="ipv6_primary_dns_6" name="ipv6_primary_dns_6" class="ipv6-input" value="<?php echo $ipv6dns[5]; ?>" />
					<label for="primary_dns_ipv6_7" class="acs-hide"></label>
					:<input type="text" size="3" maxlength="4"  id="ipv6_primary_dns_7" name="ipv6_primary_dns_7" class="ipv6-input" value="<?php echo $ipv6dns[6]; ?>" />
					<label for="primary_dns_ipv6_8" class="acs-hide"></label>
					:<input type="text" size="3" maxlength="4"  id="ipv6_primary_dns_8" name="ipv6_primary_dns_8" class="ipv6-input" value="<?php echo $ipv6dns[7]; ?>" />
				</div>
				<div class="form-row">
					<label for="secondary_dns_ipv6_1">Secondary DNS:</label>
					<?php  
						$ipv6_secondary_dns = $ipv6_secondary_dns;
						$ipv6secdns = explode(":", $ipv6_secondary_dns);
					?>
					<input type="text" size="2" maxlength="4" id="ipv6_secondary_dns_1" name="ipv6_secondary_dns_1" class="ipv6-input" value="<?php echo $ipv6secdns[0]; ?>"/>
					<label for="secondary_dns_ipv6_2" class="acs-hide"></label>
					:<input type="text" size="2" maxlength="4" id="ipv6_secondary_dns_2" name="ipv6_secondary_dns_2" class="ipv6-input" value="<?php echo $ipv6secdns[1]; ?>"/>
					<label for="secondary_dns_ipv6_3" class="acs-hide"></label>
					:<input type="text" size="2" maxlength="4"  id="ipv6_secondary_dns_3" name="ipv6_secondary_dns_3" class="ipv6-input" value="<?php echo $ipv6secdns[2]; ?>"/>
					<label for="secondary_dns_ipv6_4" class="acs-hide"></label>
					:<input type="text" size="2" maxlength="4"  id="ipv6_secondary_dns_4" name="ipv6_secondary_dns_4" class="ipv6-input" value="<?php echo $ipv6secdns[3]; ?>" />
					<label for="secondary_dns_ipv6_5" class="acs-hide"></label>
					:<input type="text" size="2" maxlength="4"  id="ipv6_secondary_dns_5" name="ipv6_secondary_dns_5" class="ipv6-input" value="<?php echo $ipv6secdns[4]; ?>" />
					<label for="secondary_dns_ipv6_6" class="acs-hide"></label>
					:<input type="text" size="2" maxlength="4"  id="ipv6_secondary_dns_6" name="ipv6_secondary_dns_6" class="ipv6-input" value="<?php echo $ipv6secdns[5]; ?>" />
					<label for="secondary_dns_ipv6_7" class="acs-hide"></label>
					:<input type="text" size="2" maxlength="4"  id="ipv6_secondary_dns_7" name="ipv6_secondary_dns_7" class="ipv6-input" value="<?php echo $ipv6secdns[6]; ?>" />
					<label for="secondary_dns_ipv6_8" class="acs-hide"></label>
					:<input type="text" size="2" maxlength="4"  id="ipv6_secondary_dns_8" name="ipv6_secondary_dns_8" class="ipv6-input" value="<?php echo $ipv6secdns[7]; ?>" />
				</div>

  	    		<div class="form-btn">
					<input type="button" id="submit_ipv6" value="Save Settings" class="btn" />
					<input id="restore_ipv6" type="button" value="Restore Default Settings" class="btn alt" />
				</div>				
 	    </div> <!-- end .module -->
 	   	</form>

</div><!-- end #content -->
<?php include('includes/footer.php'); ?>
