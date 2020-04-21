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
<!-- $Id: wizard_step2.php 2943 2009-08-25 20:58:43Z slemoine $ -->

<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?>
<?php $ForceDisable = getStr("Device.WiFi.X_RDK-CENTRAL_COM_ForceDisable"); ?>
<?php
	if("cusadmin" == $_SESSION["loginuser"] && !$_POST["userPassword"]){
		echo '<script type="text/javascript"> alert("Please finish Wizard - Step 1 first."); window.location = "wizard_step1.php";</script>';
		exit;
	}
?>

<?php
	$wifi_param = array(
		//get DefaultSSID & DefaultKeyPassphrase
		"defaultSSID1"		=> "Device.WiFi.SSID.1.X_COMCAST-COM_DefaultSSID",
		"defaultKeyPassphrase1"	=> "Device.WiFi.AccessPoint.1.Security.X_COMCAST-COM_DefaultKeyPassphrase",
		"defaultSSID2"		=> "Device.WiFi.SSID.2.X_COMCAST-COM_DefaultSSID",
		"defaultKeyPassphrase2"	=> "Device.WiFi.AccessPoint.2.Security.X_COMCAST-COM_DefaultKeyPassphrase",
		"OperatingStandards1"	=> "Device.WiFi.Radio.1.OperatingStandards",
		"OperatingStandards2"	=> "Device.WiFi.Radio.2.OperatingStandards",
		"network_name"		=> "Device.WiFi.SSID.1.SSID",
		"encrypt_mode"		=> "Device.WiFi.AccessPoint.1.Security.ModeEnabled",
		"encrypt_method"	=> "Device.WiFi.AccessPoint.1.Security.X_CISCO_COM_EncryptionMethod",
		"network_password"	=> "Device.WiFi.AccessPoint.1.Security.X_COMCAST-COM_KeyPassphrase",
		"network_pass_64"	=> "Device.WiFi.AccessPoint.1.Security.X_CISCO_COM_WEPKey64Bit.1.WEPKey",
		"network_pass_128"	=> "Device.WiFi.AccessPoint.1.Security.X_CISCO_COM_WEPKey128Bit.1.WEPKey",
		"network_name1"		=> "Device.WiFi.SSID.2.SSID",
		"encrypt_mode1"		=> "Device.WiFi.AccessPoint.2.Security.ModeEnabled",
		"encrypt_method1"	=> "Device.WiFi.AccessPoint.2.Security.X_CISCO_COM_EncryptionMethod",
		"network_password1"	=> "Device.WiFi.AccessPoint.2.Security.X_COMCAST-COM_KeyPassphrase",
		"network_pass_64_1"	=> "Device.WiFi.AccessPoint.2.Security.X_CISCO_COM_WEPKey64Bit.1.WEPKey",
		"network_pass_128_1"	=> "Device.WiFi.AccessPoint.2.Security.X_CISCO_COM_WEPKey128Bit.1.WEPKey",
		);
	$wifi_value = KeyExtGet("Device.WiFi.", $wifi_param);

	$ret = init_psmMode("Gateway > Local Network Wizard - Step 2", "nav-wizard");
	if ("" != $ret){echo $ret;	return;}

	//get DefaultSSID & DefaultKeyPassphrase
	$defaultSSID1		= $wifi_value['defaultSSID1'];
	$defaultKeyPassphrase1	= $wifi_value['defaultKeyPassphrase1'];

	$defaultSSID2		= $wifi_value['defaultSSID2'];
	$defaultKeyPassphrase2	= $wifi_value['defaultKeyPassphrase2'];
	//don't show current password for mso user
	$password_mso_user = !($_SESSION["loginuser"] == "mso");
?>

<script type="text/javascript">
$(document).ready(function() {
	<?php
		if("cusadmin" == $_SESSION["loginuser"]){
			echo 'comcast.page.init("Gateway > Local Network Wizard - Step 2", "nav-wizard");';
		}
		else {
			echo 'comcast.page.init("Gateway > Local Network Wizard", "nav-wizard");';
		}
	?>
       $ForceDisable = '<?php echo $ForceDisable; ?>';
     if($ForceDisable == "true"){
		//disable >>  Network Name:, Password: , Security:
		 $('#network_name,#security, #network_password, #password_check, #network_name1 , #security1 ,#network_password1,#password_check1').prop("disabled", true);
	}
	var password_mso_user = '<?php echo $password_mso_user; ?>';
        if(($ForceDisable == "true") && (!password_mso_user)){
            $("input[type=submit]").attr("disabled", "disabled");
        }
    /*
     *  Manage password field: open wep networks don't use passwords
     */

    $("#security").change(function() {
		var $select = $(this);
    	var $selected_option = $("option:selected", $select);

    	if($selected_option.val() == "None") {
    		$("#netPassword").find("*").addClass("disabled").filter("input").attr("disabled", "disabled").val("");
		$("#div_change_password").hide();
    	} else {
			$("#netPassword").find("*").removeClass("disabled").filter("input").attr("disabled", false);
			if(!password_mso_user){
				$("#network_password").val("");
				$("#network_password").prop("disabled", true);
				$("#div_change_password").show();
			}
			else {
                             if($ForceDisable == "true") {
                                $("#network_password").prop("disabled", true);
                               }
                             else
				$("#network_password").prop("disabled", false);
			   $("#div_change_password").hide();
			}
    	}
		
    	// Update footnote to display password validation rules
    	$("#netPassword-footnote").text($selected_option.attr("title"));
    }).trigger("change");
	if(!password_mso_user){
		$('#password_check').click(function() {
			$("#network_password").prop("disabled", !(this.checked));
		});
	}
	$("#security1").change(function() {
		var $select1 = $(this);
		var $selected_option1 = $("option:selected", $select1);

		if($selected_option1.val() == "None") {
			$("#netPassword1").find("*").addClass("disabled").filter("input").attr("disabled", "disabled").val("");
			$("#div_change_password1").hide();
		} else {
			$("#netPassword1").find("*").removeClass("disabled").filter("input").attr("disabled", false);
			if(!password_mso_user){
				$("#network_password1").val("");
				$("#network_password1").prop("disabled", true);
				$("#div_change_password1").show();
			}
			else {
                             if($ForceDisable == "true") {
                                $("#network_password1").prop("disabled", true);
                               }
                             else
				$("#network_password1").prop("disabled", false);
			     $("#div_change_password1").hide();
			}
		}

		// Update footnote to display password validation rules
		$("#netPassword-footnote1").text($selected_option1.attr("title"));
	if(!password_mso_user){
		$('#password_check1').click(function() {
			$("#network_password1").prop("disabled", !(this.checked));
		});
	}
	}).trigger("change");

    $.validator.addMethod("wep_64", function(value, element, param) {
		return !param || /^[a-fA-F0-9]{10}$|^[\S]{5}$/i.test(value);
	}, "5 Ascii characters or 10 Hex digits.");

    $.validator.addMethod("wep_128", function(value, element, param) {
		return !param || /^[a-fA-F0-9]{26}$|^[\S]{13}$/i.test(value);
	}, "13 Ascii characters or 26 Hex digits.");

    $.validator.addMethod("wpa", function(value, element, param) {
		return !param || /^[ -~]{8,63}$/i.test(value);
	}, "8 to 63 ASCII characters.");

    $.validator.addMethod("wpa2", function(value, element, param) {
		return !param || /^[\S]{8,63}$/i.test(value);
	}, "8 to 63 ASCII characters.");
	
    $.validator.addMethod("ssid_name", function(value, element, param) {
		return !param || /^[ -~]{3,32}$/i.test(value);
	}, "3 to 32 ASCII characters.");

    $.validator.addMethod("not_hhs", function(value, element, param) {
		//prevent users to set XHSXXX or Xfinityxxx as ssid
		return value.toLowerCase().indexOf("xhs-") !=0 && value.toLowerCase().indexOf("xh-") !=0;
	}, 'SSID containing "XHS-" and "XH-" are reserved !');

    $.validator.addMethod("not_hhs2", function(value, element, param) {
		//prevent users to set optimumwifi or TWCWiFi  or CableWiFi or CoxWiFi as ssid
		//zqiu:
		var str = value.replace(/[\.,-\/#@!$%\^&\*;:{}=+?\-_`~()"'\\|<>\[\]\s]/g,'').toLowerCase();
		return str.indexOf("cablewifi") == -1 && str.indexOf("twcwifi") == -1 && str.indexOf("optimumwifi") == -1 && str.indexOf("xfinity") == -1 && str.indexOf("xfinitywifi") == -1 && str.indexOf("coxwifi") == -1 ;
		//return value.toLowerCase().indexOf("optimumwifi")==-1 && value.toLowerCase().indexOf("twcwifi")==-1 && value.toLowerCase().indexOf("cablewifi")==-1;
	}, 'SSID containing "optimumwifi", "TWCWiFi", "CoxWiFi", "cablewifi", "xfinity" and "xfinitywifi" are reserved !');

    $.validator.addMethod("not_defaulSSID1", function(value, element, param) {
		//prevent users to set defaul-SSID as ssid
		return value.toLowerCase() != "<?php echo $defaultSSID1; ?>".toLowerCase();
	}, 'Choose a different Network Name (SSID) (2.4GHz) than the one provided on your gateway.');

    $.validator.addMethod("not_defaulPassword1", function(value, element, param) {
		//prevent users to set defaul-Password as Password
		return value != "<?php echo $defaultKeyPassphrase1; ?>";
	}, 'Choose a different Network Password (2.4GHz) than the one provided on your gateway.');

    $.validator.addMethod("not_defaulSSID2", function(value, element, param) {
		//prevent users to set defaul-SSID as ssid
		return value.toLowerCase() != "<?php echo $defaultSSID2; ?>".toLowerCase();
	}, 'Choose a different Network Name (SSID) (5 GHz) than the one provided on your gateway.');

    $.validator.addMethod("not_defaulPassword2", function(value, element, param) {
		//prevent users to set defaul-Password as Password
		return value != "<?php echo $defaultKeyPassphrase2; ?>";
	}, 'Choose a different Network Password (5 GHz) than the one provided on your gateway.');

    // XFSETUP HOME xfinitywifi cablewifi
    // a term starting with the following combination of text in uppercase or lowercase should not be allowed
/*
    $.validator.addMethod("not_XFSETUP", function(value, element, param) {
		return value.toLowerCase().indexOf("xfsetup") != 0;
	}, 'SSID starting with "XFSETUP" is reserved !');
*/
    $.validator.addMethod("not_whitespace", function(value, element, param) {
		if(!value.trim())
			return false;
		else
			return true;
	}, 'SSID cannot have only whitespaces !');

  /*  $.validator.addMethod("not_HOME", function(value, element, param) {
		return value.toLowerCase().indexOf("home") != 0;
	}, 'SSID starting with "HOME" is reserved !');*/

/*
wep 64 ==> 5 Ascii characters or 10 Hex digits
wep 128 ==> 13 Ascii characters or 26 Hex digits
wpapsk ==> 8 to 63 Ascii characters or 64 Hex digits
wpa2psk ==> 8 to 63 Ascii characters
*/

    $("#pageForm").validate({
    	debug: true,
    	rules: {
			network_password: {
			not_defaulPassword1: true,
    			required: function() {
    				return ($("#security").val() != "None");
    			}
    			,wep_64: function() {
    				return ($("#security").val() == "WEP_64");
    			}
    			,wep_128: function() {
					return ($("#security").val() == "WEP_128");
    			}
    			// ,wpa: function() {
    				// return ($("#security option:selected").val() == "WPA_PSK_TKIP" || $("#security option:selected").val() == "WPA_PSK_AES");
    			// }
    			// ,wpa2: function() {
    				// return ($("#security option:selected").val() == "WPA2_PSK_TKIP" || $("#security option:selected").val() == "WPA2_PSK_AES" || $("#security option:selected").val() == "WPA2_PSK_TKIPAES" || $("#security option:selected").val() == "WPAWPA2_PSK_TKIPAES");
    			// }
    			,wpa: function() {
    				return ($("#security option:selected").val() != "None" && $("#security option:selected").val() != "WEP_64" && $("#security option:selected").val() != "WEP_128");
    			}
	    	},
    		network_password1: {
			not_defaulPassword2: true,
    			required: function() {
    				return ($("#security1").val() != "None");
    			}
    			,wep_64: function() {
    				return ($("#security1").val() == "WEP_64");
    			}
    			,wep_128: function() {
					return ($("#security1").val() == "WEP_128");
    			}
    			// ,wpa: function() {
    				// return ($("#security1 option:selected").val() == "WPA_PSK_TKIP" || $("#security1 option:selected").val() == "WPA_PSK_AES");
    			// }
    			// ,wpa2: function() {
    				// return ($("#security1 option:selected").val() == "WPA2_PSK_TKIP" || $("#security1 option:selected").val() == "WPA2_PSK_AES" || $("#security1 option:selected").val() == "WPA2_PSK_TKIPAES" || $("#security1 option:selected").val() == "WPAWPA2_PSK_TKIPAES");
    			// }
    			,wpa: function() {
    				return ($("#security1 option:selected").val() != "None" && $("#security1 option:selected").val() != "WEP_64" && $("#security1 option:selected").val() != "WEP_128");
    			}
	    	}
    	},
		
		submitHandler:function(form){
			click_save();
			// location.reload();
		}
    });
	
	if ("n" == "<?php echo $wifi_value['OperatingStandards1']; ?>")
	{
		$("#security option").attr("disabled", true);
		$("#security [value='None'],[value='WPA_PSK_AES'],[value='WPA2_PSK_AES']").attr("disabled", false);
		if ($("#security").val().indexOf("WPA2_PSK") != -1){
			$("#security").val("WPA2_PSK_AES");
		}
		else if ($("#security").val().indexOf("WPA_PSK") != -1){
			$("#security").val("WPA_PSK_AES");
		}
		else {
			$("#security").val("None");
		}
	}
	
	if ("n" == "<?php echo $wifi_value['OperatingStandards2']; ?>")
	{
		$("#security1 option").attr("disabled", true);
		$("#security1 [value='None'],[value='WPA_PSK_AES'],[value='WPA2_PSK_AES']").attr("disabled", false);
		if ($("#security1").val().indexOf("WPA2_PSK") != -1){
			$("#security1").val("WPA2_PSK_AES");
		}
		else if ($("#security1").val().indexOf("WPA_PSK") != -1){
			$("#security1").val("WPA_PSK_AES");
		}
		else {
			$("#security1").val("None");
		}
	}
	
});


function set_config(jsConfig)
{
	// alert(jsConfig);
	var network_name_1= '<?php echo $wifi_value['network_name']; ?>';
	var network_name_2= '<?php echo $wifi_value['network_name1']; ?>';
	jProgress('This may take several seconds...', 60);
	$.post(
		"actionHandler/ajaxSet_wizard_step2.php",
		{
			configInfo: jsConfig
		},
		function(msg)
		{
			jHide();
			msg_parseJSON = $.parseJSON(msg);
			// location.reload();
			if(msg_parseJSON.error_message){
				jAlert(msg_parseJSON.error_message);
				$("#network_name").val(network_name_1);
				$("#network_name1").val(network_name_2);
			}else{
				<?php 
					if($_SESSION["loginuser"] == "cusadmin")
						echo 'jAlert("Changes saved successfully. <br> Please login with the new password.");setTimeout(function(){jHide();location.href="home_loggedout.php";}, 5000);';
					else
						echo 'jAlert("Changes saved successfully.");setTimeout(function(){jHide();location.href="at_a_glance.php";}, 5000);';
				?>
			}
		});
}

function addslashes( str ) {
	return (str + '').replace(/[\\]/g, '\\$&').replace(/["]/g, '\\\$&').replace(/\u0000/g, '\\0');
}

function click_save()
{
	var password_mso_user = '<?php echo $password_mso_user; ?>';
	var network_password = "";
	var network_password1 = "";
	var network_name = 		addslashes($("#network_name").val());
	var security = 			$("#security").val();
	var network_name1 = 	addslashes($("#network_name1").val());
	var security1 = 		$("#security1").val();
	var newPassword	= '<?php if("cusadmin" == $_SESSION["loginuser"]) echo $_POST["userPassword"]; ?>';
	var password_update      = $("#password_check").prop("checked");
	var password_update1     = $("#password_check1").prop("checked");
	if((password_mso_user) || password_update){
		network_password	= addslashes($("#network_password").val());
	}
	if((password_mso_user) || password_update1){
		network_password1	= addslashes($("#network_password1").val());
	}
	if(newPassword){
		var jsConfig = '{"network_name":"'+network_name+'", "security":"'+security+'", "network_password":"'+network_password 
			+'", "network_name1":"'+network_name1+'", "security1":"'+security1+'", "network_password1":"'+network_password1
			+'", "password_update":"'+password_update+'", "password_update1":"'+password_update1
			+'", "newPassword":"'+newPassword
			+'"}';
	} else {
		var jsConfig = '{"network_name":"'+network_name+'", "security":"'+security+'", "network_password":"'+network_password 
			+'", "network_name1":"'+network_name1+'", "security1":"'+security1+'", "network_password1":"'+network_password1
			+'", "password_update":"'+password_update+'", "password_update1":"'+password_update1
			+'"}';
	}

	set_config(jsConfig);
}

</script>

<?php

//WiFi 2.4G**************************************************************************************
$network_name		= $wifi_value['network_name'];
$encrypt_mode		= $wifi_value['encrypt_mode'];
$encrypt_method		= $wifi_value['encrypt_method'];
$network_password	= $wifi_value['network_password'];
$network_pass_64	= $wifi_value['network_pass_64'];
$network_pass_128	= $wifi_value['network_pass_128'];

// $network_name 		= "string";
// $encrypt_mode 		= "WPA-Personal";
// $encrypt_method		= "TKIP";
// $network_password 	= "abc123456";
// $network_pass_64		= "wep64";
// $network_pass_128	= "wep128";

$security = "None";
if ("WEP-64" == $encrypt_mode){
		$security = "WEP_64";
		$network_password = $network_pass_64;
}elseif ("WEP-128" == $encrypt_mode){
		$security = "WEP_128";
		$network_password = $network_pass_128;
}elseif ("WPA-Personal" == $encrypt_mode){
	if ("TKIP" == $encrypt_method){
		$security = "WPA_PSK_TKIP";
	}else{
		$security = "WPA_PSK_AES";
	}
}elseif ("WPA2-Personal" == $encrypt_mode){
	if ("TKIP" == $encrypt_method){
		$security = "WPA2_PSK_TKIP";
	}elseif ("AES" == $encrypt_method){
		$security = "WPA2_PSK_AES";
	}else{
		$security = "WPA2_PSK_TKIPAES";
	}
}elseif ("WPA-WPA2-Personal" == $encrypt_mode){
		$security = "WPAWPA2_PSK_TKIPAES";
}else{
		$security = "None";
}

//WiFi 5G**************************************************************************************
$network_name1		= $wifi_value['network_name1'];
$encrypt_mode1		= $wifi_value['encrypt_mode1'];
$encrypt_method1	= $wifi_value['encrypt_method1'];
$network_password1	= $wifi_value['network_password1'];
$network_pass_64	= $wifi_value['network_pass_64_1'];
$network_pass_128	= $wifi_value['network_pass_128_1'];

// $network_name1 		= "string";
// $encrypt_mode1 		= "WPA-Personal";
// $encrypt_method1		= "TKIP";
// $network_password1 	= "abc123456";
// $network_pass_64		= "wep64";
// $network_pass_128	= "wep128";

$security1 = "None";
if ("WEP-64" == $encrypt_mode1){
		$security1 = "WEP_64";
		$network_password1 = $network_pass_64;
}elseif ("WEP-128" == $encrypt_mode1){
		$security1 = "WEP_128";
		$network_password1 = $network_pass_128;
}elseif ("WPA-Personal" == $encrypt_mode1){
	if ("TKIP" == $encrypt_method1){
		$security1 = "WPA_PSK_TKIP";
	}else{
		$security1 = "WPA_PSK_AES";
	}
}elseif ("WPA2-Personal" == $encrypt_mode1){
	if ("TKIP" == $encrypt_method1){
		$security1 = "WPA2_PSK_TKIP";
	}elseif ("AES" == $encrypt_method1){
		$security1 = "WPA2_PSK_AES";
	}else{
		$security1 = "WPA2_PSK_TKIPAES";
	}
}elseif ("WPA-WPA2-Personal" == $encrypt_mode1){
		$security1 = "WPAWPA2_PSK_TKIPAES";
}else{
		$security1 = "None";
}

?>

<div id="content">
	<?php
		if("cusadmin" == $_SESSION["loginuser"]){
			echo '<h1>Gateway > Local Network Wizard - Step 2</h1>';
		}
		else {
			echo '<h1>Gateway > Local Network Wizard</h1>';
		}
	?>

	<div id="educational-tip">
		<p class="tip">You may want to edit information about your Wi-Fi network for both 2.4 GHz and 5 GHz Wi-Fi bands.</p>
		<p class="hidden"><strong>Wi-Fi Network Name:</strong> Names of the 2.4 GHz and 5 GHz Wi-Fi networks of your Gateway. The default Network Names (SSIDs), located on the bottom label of the Gateway, is unique to this Gateway.</p>
		<p class="hidden"><strong>Encryption Method:</strong> WPAWPA2-PSK (TKIP/AES), the default setting, offers the best security and performance.</p>
		<p class="hidden"><strong>Network Password:</strong> The default Password (key), located on the bottom label of the Gateway, is unique to this Gateway.</p>
		<p class="hidden"><strong>Note:</strong> If you change any of the default settings, you'll need to reconnect Wi-Fi products on your network (using the new information).</p>
	</div>

	<div class="module forms">
		<form action="at_a_glance.php" method="post" id="pageForm">
			<?php
				if("cusadmin" == $_SESSION["loginuser"]){
					echo '<h2>Step 2 of 2</h2>';
				}
				else {
					echo '<h2>Local Network Wizard</h2>';
				}
			?>
               <?php
            if($ForceDisable == "true") {
         ?>
                      <div class= "error" style="text-align: center;" >
                             <h3 style="width:92%"><?php echo _("WiFi is configured to be disabled");?></h3>
                          </div>
              <?php
             }
           ?>
			<p class="summary">Next, we need to configure your wireless network. Note that your network can be accessed  by both 2.4 GHz (Wi-Fi B, G, N) and 5GHz(Wi-Fi A, N, AC ) compatible devices.</p>
			
			<div class="form-row odd">
				<label for="network_name">Wi-Fi Network Name (2.4GHz):</label>
				<input type="text" size="23" value="<?php echo htmlspecialchars($network_name);?>" id="network_name" name="network_name" class="text" />
			</div>
			<div class="form-row">
				<label for="security">Encryption Method (2.4GHz):</label>
				<select name="encryption_method" id="security">
					<option value="None" 				title="Open networks do not have a password." 			<?php if ("None"==$security) echo "selected";?> >Open (risky)</option>
					<!--option value="WEP_64" 				title="WEP  64 requires a  5 ASCII character or 10 hex character password.  Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WEP_64"==$security)              echo "selected";?> >WEP 64 (risky)</option-->
					<!--option value="WEP_128" 			title="WEP 128 requires a 13 ASCII character or 16 hex character password.  Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WEP_128"==$security)             echo "selected";?> >WEP 128 (risky)</option-->
					<!--option value="WPA_PSK_TKIP" 		title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA_PSK_TKIP"==$security)        echo "selected";?> >WPA-PSK (TKIP)</option-->
					<!--option value="WPA_PSK_AES" 		title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA_PSK_AES"==$security)         echo "selected";?> >WPA-PSK (AES)</option-->
					<!--option value="WPA2_PSK_TKIP" 	title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA2_PSK_TKIP"==$security)       echo "selected";?> >WPA2-PSK (TKIP)</option-->
					<option value="WPA2_PSK_AES" 		title="WPA requires an 8-63 ASCII character password." <?php if ("WPA2_PSK_AES"==$security)        echo "selected";?> >WPA2-PSK (AES)</option>
					<!--option value="WPA2_PSK_TKIPAES" 	title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA2_PSK_TKIPAES"==$security)    echo "selected";?> >WPA2-PSK (TKIP/AES)</option-->
					<option value="WPAWPA2_PSK_TKIPAES" 	title="WPA requires an 8-63 ASCII character password." <?php if ("WPAWPA2_PSK_TKIPAES"==$security) echo "selected";?> >WPAWPA2-PSK (TKIP/AES)(Recommended)</option>
				</select>
			</div>
			<div id="netPassword">
				<div class="form-row odd">
					<label for="network_password">Network Password (2.4GHz):</label>
					<input type="text" size="23" id="network_password" name="network_password" class="text" value="<?php if($password_mso_user) echo htmlspecialchars($network_password); ?>"/>
				</div>
				<p id="netPassword-footnote" class="footnote">8-16 characters. Letter and numbers only. No spaces. Case sensitive.</p>
			</div>
			<div class="form-row" id="div_change_password">
				<label for="network_password">Change Password:</label>
				<span class="checkbox"><input type="checkbox" id="password_check" name="password_check" /></span>
			</div>
			<div class="form-row odd">
				<label for="network_name1">Wi-Fi Network Name (5 GHz):</label>
				<input type="text" size="23" value="<?php echo htmlspecialchars($network_name1);?>" id="network_name1" name="network_name1" class="text" />
			</div>	
			<div class="form-row">
				<label for="security1">Encryption Method (5 GHz):</label>
				<select name="encryption_method1" id="security1">
					<option value="None" 			title="Open networks do not have a password." 			<?php if ("None"==$security1) echo "selected";?> >Open (risky)</option>
					<!--option value="WEP_64"               title="WEP  64 requires a  5 ASCII character or 10 hex character password.  Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WEP_64"==$security1)              echo "selected";?> >WEP 64 (risky)</option-->
					<!--option value="WEP_128"          	title="WEP 128 requires a 13 ASCII character or 16 hex character password.  Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WEP_128"==$security1)             echo "selected";?> >WEP 128 (risky)</option-->
					<!--option value="WPA_PSK_TKIP" 	title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA_PSK_TKIP"==$security1)        echo "selected";?> >WPA-PSK (TKIP)</option-->
					<!--option value="WPA_PSK_AES" 		title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA_PSK_AES"==$security1)         echo "selected";?> >WPA-PSK (AES)</option-->
					<!--option value="WPA2_PSK_TKIP" 	title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA2_PSK_TKIP"==$security1)       echo "selected";?> >WPA2-PSK (TKIP)</option-->
					<option value="WPA2_PSK_AES" 		title="WPA requires an 8-63 ASCII character password." <?php if ("WPA2_PSK_AES"==$security1)        echo "selected";?> >WPA2-PSK (AES)</option>
					<!--option value="WPA2_PSK_TKIPAES" 	title="WPA requires an 8-63 ASCII character or a 64 hex character password. Hex means only the following characters can be used: ABCDEF0123456789." <?php if ("WPA2_PSK_TKIPAES"==$security1)    echo "selected";?> >WPA2-PSK (TKIP/AES)</option-->
					<option value="WPAWPA2_PSK_TKIPAES" 	title="WPA requires an 8-63 ASCII character password." <?php if ("WPAWPA2_PSK_TKIPAES"==$security1) echo "selected";?> >WPAWPA2-PSK (TKIP/AES)(Recommended)</option>
				</select>
			</div>
			<div id="netPassword1">
				<div class="form-row odd">
					<label for="network_password1">Network Password (5 GHz):</label>
					<input type="text" size="23" id="network_password1" name="network_password1" class="text" value="<?php if($password_mso_user) echo htmlspecialchars($network_password1); ?>"/>
				</div>
				<p id="netPassword-footnote1" class="footnote">8-16 characters. Letter and numbers only. No spaces. Case sensitive.</p>
			</div>
			<div class="form-row" id="div_change_password1">
				<label for="network_password1">Change Password:</label>
				<span class="checkbox"><input type="checkbox" id="password_check1" name="password_check1" /></span>
			</div>
			<div id="wizard-form-buttons" class="form-row form-btn">
				<input type="submit" value="Finish" class="btn" />
			</div>
			
		</form>
	</div> <!-- end .module -->
</div><!-- end #content -->
<?php include('includes/footer.php'); ?>
