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

<!-- $Id: port_forwarding_add.php 3158 2010-01-08 23:32:05Z slemoine $ -->

<div id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?>
<?php
/* This page handles Add/Edit for Additional Subnet */
$reqType = $_GET['t'];
if (!isset($reqType)) $reqType = 'edit';
$id = $_GET['id'];
if ($reqType !== 'add' && !isset($id)) {
	echo 'Request parameter is invalid!';
	exit(0);
}

if(isset($id) && preg_match('/^[0-9]+$/', $id) != 1) {
	echo "Requested ID is invalid";
	exit(0);
}

$pageTypeStr = ($reqType === 'add') ? 'Add' : 'Edit';

if ($reqType === 'add') {
}
else {
	/* edit */
	$toFetchParam = array(
		"ip" => "Device.X_CISCO_COM_TrueStaticIP.AdditionalSubnet.$id.IPAddress",
		"mask" => "Device.X_CISCO_COM_TrueStaticIP.AdditionalSubnet.$id.SubnetMask",
		"enable" => "Device.X_CISCO_COM_TrueStaticIP.AdditionalSubnet.$id.Enable"
	);
	$entry = DmExtGetStrsWithRootObj("Device.X_CISCO_COM_TrueStaticIP.AdditionalSubnet.$id.", $toFetchParam);
	/*if ($_DEBUG) {
		$entry = array(0,array("dm1",$id.'.5.5.1'),array('dm2','255.255.255.248'));
	}*/
	if (empty($entry) || $entry[0] !== 0) {
		/* error fetching */
		echo 'Failed to fetching additional subnet!';
		exit(0);
	}
	else {
		$ipAddr = $entry[1][1];
		$mask = $entry[2][1];
		$enable = ($entry[3][1] === 'true');
	}
}

?>

<script type="text/javascript">
var o_id = <?php echo isset($id) ? "\"$id\"" : "null"; ?>;
var o_ipAddr = <?php echo isset($ipAddr) ? "\"$ipAddr\"" : "null"; ?>;
var o_mask = <?php echo isset($mask) ? "\"$mask\"" : "null"; ?>;

function initSubnetFields() {
	var ipArr = o_ipAddr ? o_ipAddr.split('.') : null;
	var maskArr = o_mask ? o_mask.split('.') : null;

	if (ipArr == null || maskArr == null) return;

	$('[id^=public_ip_address_]').each(function(idx){
		$(this).val(ipArr[idx]);
	});
	$('[id^=mask_]').each(function(idx){
		$(this).val(maskArr[idx]);
	});
}

function initValidation() {
	jQuery.validator.addMethod("netmaskFields", function(value, elem) {		
		/* use validator-prefix customized attr to fetch all fields */
		var prefix = $(elem).attr('validator-prefix');
		var compMask = $('#'+prefix+'1').val() + '.' +
						$('#'+prefix+'2').val() + '.' +
						$('#'+prefix+'3').val() + '.' +
						$('#'+prefix+'4').val();
		//console.log(compMask);
		maskPattern = /^(((128|192|224|240|248|252|254)\.0\.0\.0)|(255\.(0|128|192|224|240|248|252|254)\.0\.0)|(255\.255\.(0|128|192|224|240|248|252|254)\.0)|(255\.255\.255\.(0|128|192|224|240|248|252|254)))$/;
		return maskPattern.test(compMask);
	}, 'Invalid netmask');

	$("#pageForm").validate({
		onfocusout: false,
		onkeyup: false,
		groups: {
			public_ip: "public_ip_address_1 public_ip_address_2 public_ip_address_3 public_ip_address_4",
			netmask: "mask_1 mask_2 mask_3 mask_4"
		},
		rules: {
			public_ip_address_1: {
        		required: true,
        		min: 1,
        		max: 255,
        		digits: true
        	},
			public_ip_address_2: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			public_ip_address_3: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			public_ip_address_4: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			mask_1: {
				required: true,
				netmaskFields: true
			},
			mask_2: {
				required: true,
				netmaskFields: true
			},
			mask_3: {
				required: true,
				netmaskFields: true
			},
			mask_4: {
				required: true,
				netmaskFields: true
			}
		}
	});
}

function onsave() {
	if ($('#pageForm').valid()) {
		var postData = {};
		postData.ip = $('#public_ip_address_1').val() + '.' +
						$('#public_ip_address_2').val() + '.' +
						$('#public_ip_address_3').val() + '.' +
						$('#public_ip_address_4').val();
		postData.mask = $('#mask_1').val() + '.' +
						$('#mask_2').val() + '.' +
						$('#mask_3').val() + '.' +
						$('#mask_4').val();
		postData.enable = true;
		if (o_id === null) {
			/* this is an adding */
			postData.op = 'add';
		}
		else {
			/* this is an editing */
			postData.op = 'edit';
			postData.id = o_id;
		}

		jProgress('This may take several seconds', 60);
		$.ajax({
			type: 'POST',
			url: 'actionHandler/ajaxSet_WAN_additional_subnet.php',
			dataType: 'json',
			data: postData,
			success: function(data) {
				jHide();
				if (data.status != 'success') {
					var str = "Failed, please try again later.";
					if (data.msg) {
						str += '\nMessage: ' + data.msg;
					}
					jAlert(str);
					return;
				}
				window.location.href = 'wan.php';
			},
			error: function() {
				jHide();
				jAlert("Failure, please try again.");
			}
		});
	}
}

$(document).ready(function() {
	comcast.page.init("Gateway > Connection > WAN > <?php echo $pageTypeStr; ?> Public Subnet", "nav-wan");

	initSubnetFields();
	initValidation();

	$('input.submit').click(function(e) {
		e.stopPropagation();
		e.preventDefault();
		onsave();
	});
    $("#btn-cancel").click(function() {
    	window.location.href = "wan.php";
    });
	
});
</script>

<div id="content">
<h1>Gateway > Connection > WAN > <?php echo $pageTypeStr; ?> Public Subnet</h1>

    <div id="educational-tip">
        <p class="tip"> Add a rule to add or edit Secondary IP.</p>
    </div>

	<form method="post" id="pageForm" action="wan.php">
	<div class="module forms">
	<h2><?php echo $pageTypeStr; ?> Secondary IP</h2>

		<div class="form-row ">
			<label for="public_ip_address_1">Public IP Address:</label>
			 <input type="text" size="3" value="" id="public_ip_address_1" name="public_ip_address_1" class="" title="first part"/>
			<label for="public_ip_address_2" class="acs-hide"></label>
			.<input type="text" size="3" value="" id="public_ip_address_2" name="public_ip_address_2" class="" title="second part"/>
			<label for="public_ip_address_3" class="acs-hide"></label>
			.<input type="text" size="3" value="" id="public_ip_address_3" name="public_ip_address_3" class="" title="third part"/>
			<label for="public_ip_address_4" class="acs-hide"></label>
			.<input type="text" size="3" value="" id="public_ip_address_4" name="public_ip_address_4" class="" title="fourth part"/>
		</div>

		<div class="form-row ">
			<label for="mask_1">Subnet Mask:</label>
			 <input type="text" size="3" value="" id="mask_1" name="mask_1" class="" validator-prefix="mask_" title="first part"/>
			<label for="mask_2" class="acs-hide"></label>
			.<input type="text" size="3" value="" id="mask_2" name="mask_2" class="" validator-prefix="mask_" title="second part"/>
			<label for="mask_3" class="acs-hide"></label>
			.<input type="text" size="3" value="" id="mask_3" name="mask_3" class="" validator-prefix="mask_" title="third part"/>
			<label for="mask_4" class="acs-hide"></label>
			.<input type="text" size="3" value="" id="mask_4" name="mask_4" class="" validator-prefix="mask_" title="fourth part"/>
		</div>	


		<div class="form-btn">
			<input type="submit" value="save" class="btn submit"/>
			<input type="button" id="btn-cancel" value="Cancel" class="btn alt reset"/>
		</div>

	</div> <!-- end .module -->
</form>

</div><!-- end #content -->

<?php include('includes/footer.php'); ?>
