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
<div  id="sub-header">
	<?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?>
<?php include('includes/utility.php'); ?>
<?php
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

/* prepare the data */
$wanTrueStaticIpAddr = getStr('Device.X_CISCO_COM_TrueStaticIP.IPAddress');
$wanTrueStaticIpMask = getStr('Device.X_CISCO_COM_TrueStaticIP.SubnetMask');
$addiSubnetTable = fetchAdditionalSubnetTable();
/*if ($_DEBUG) {
	$wanTrueStaticIpAddr = '1.1.1.6';
	$wanTrueStaticIpMask = '255.255.255.0';
}*/

$pageTypeStr = ($reqType === 'add') ? 'Add' : 'Edit';

if ($reqType === 'add') {
	/* add, nothing to do here */
}
else {
	/* edit based on id */
	$rootObjName    = "Device.X_CISCO_COM_TrueStaticIP.PortManagement.Rule.$id.";
	$paramNameArray = array("Device.X_CISCO_COM_TrueStaticIP.PortManagement.Rule.$id.");
	$mapping_array  = array("Enable", "Name", "Protocol", "IPRangeMin", "IPRangeMax", "PortRangeMin", "PortRangeMax");
	$pmArray = getParaValues($rootObjName, $paramNameArray, $mapping_array);
	/*if ($_DEBUG) {
		$pmArray = array(
			array('__id' => "$id", 'Name' => 'app '.$id, 'PortRangeMin' => $id.'1', 'PortRangeMax' => $id.'2', 'IPRangeMin' => '111.111.111.11'.$id, 'IPRangeMax' => '111.111.111.12'.$id, 'Protocol' => 'TCP', 'Enable' => 'true')
		);
	}*/

	if (count($pmArray) > 0) {
		$ruleEntry = $pmArray[0];
	}
	else {
		echo 'Failed to fetch Port Management rule';
		exit(0);
	}
}

/* prepare connected devices */
$rootObjName    = "Device.Hosts.Host.";
$paramNameArray = array("Device.Hosts.Host.");
$mapping_array  = array("HostName", "IPAddress", "IPv6Addr",
						"PhysAddress", "Layer1Interface", "Layer3Interface", "Active");
$connDevArray = getParaValues($rootObjName, $paramNameArray, $mapping_array);
/*if ($_DEBUG) {
	$connDevArray = array(
		array('HostName'=>'Computer1', 'IPAddress'=>'10.1.10.67', 'IPv6Addr'=>'::', 'X_CISCO_COM_TrueStaticIPClient'=>'false',
			  'PhysAddress'=>'11:11:11:11:11:11', 'Layer1Interface'=>'Ethernet', 'Layer3Interface'=>'brlan0', 'Active'=>'true'),
		array('HostName'=>'Computer2', 'IPAddress'=>'10.1.10.68', 'IPv6Addr'=>'::', 'X_CISCO_COM_TrueStaticIPClient'=>'false',
			  'PhysAddress'=>'22:22:22:22:22:22', 'Layer1Interface'=>'Ethernet', 'Layer3Interface'=>'brlan0', 'Active'=>'true'),
		array('HostName'=>'Computer3', 'IPAddress'=>'6.6.6.10', 'IPv6Addr'=>'::', 'X_CISCO_COM_TrueStaticIPClient'=>'true',
			  'PhysAddress'=>'33:33:33:33:33:33', 'Layer1Interface'=>'Ethernet', 'Layer3Interface'=>'brlan0', 'Active'=>'false'),
		array('HostName'=>'Computer4', 'IPAddress'=>'6.6.6.15', 'IPv6Addr'=>'::', 'X_CISCO_COM_TrueStaticIPClient'=>'true',
			  'PhysAddress'=>'44:44:44:44:44:44', 'Layer1Interface'=>'Device.WiFi.SSID.1.', 'Layer3Interface'=>'ath0', 'Active'=>'true')
	);
}*/
/* prune those hosts not belonging to TSI or not active, and convert values */
for ($i = count($connDevArray) - 1; $i >= 0; --$i) {
	if ($connDevArray[$i]['Active'] !== 'true') {
		array_splice($connDevArray, $i, 1);
	}
	else {
		$tempType = ProcessLay1Interface($connDevArray[$i]["Layer1Interface"]);
		$connDevArray[$i]["Layer1Interface"] = $tempType["connectionType"];
	}
}

?>

<style type="text/css">

label{
	margin-right: 10px !important;
}

</style>

<script type="text/javascript">
var o_id = <?php echo isset($id) ? "\"$id\"" : "null"; ?>;
var o_ruleEntry = <?php echo isset($ruleEntry) ? json_encode($ruleEntry) : "null"; ?>;
var o_connDevArray = <?php echo isset($connDevArray) ? json_encode($connDevArray) : "[]"; ?>;

/* for validation */
var o_wanTrueStaticIpAddr = "<?php echo $wanTrueStaticIpAddr; ?>";
var o_wanTrueStaticIpMask = "<?php echo $wanTrueStaticIpMask; ?>";
var o_addiSubnets = <?php echo isset($addiSubnetTable) ? json_encode($addiSubnetTable) : "[]"; ?>;

function initPmFields() {
	if (o_ruleEntry == null) return;
	var ipStartArr = o_ruleEntry.IPRangeMin.split('.');
	var ipEndArr = o_ruleEntry.IPRangeMax.split('.');

	$('#appName').val(htmlspecialchars_js(o_ruleEntry.Name));
	$('#protocol').comboVal(o_ruleEntry.Protocol);
	$('[id^=ip_start_]').each(function(idx){
		$(this).val(ipStartArr[idx]);
	});
	$('[id^=ip_end_]').each(function(idx){
		$(this).val(ipEndArr[idx]);
	});
	$('#start_port').val(o_ruleEntry.PortRangeMin);
	$('#end_port').val(o_ruleEntry.PortRangeMax);
}

function initConnDevTable() {
	var jq_connDevTable = $('#device_list > table');
	var htmlStr = '';
	$.each(o_connDevArray, function(idx, elem){
		htmlStr += '<tr cdid="'+idx+'">';
		htmlStr += '<td headers="hdr_name">' + elem.HostName + '</td>';
		htmlStr += '<td headers="hdr_ip">' + elem.IPAddress + '</td>';
		htmlStr += '<td headers="hdr_mac">' + elem.PhysAddress + '</td>';
		htmlStr += '<td headers="hdr_if">' + elem.Layer1Interface + '</td>';
		htmlStr += '<td headers="hdr_add"><label for="select_device'+idx+'" class="acs-hide"></label>'+
			'<input type="radio" value="add" id="select_device'+idx+'" name="select_device" /></td>';
		htmlStr += '</tr>';
	});
	jq_connDevTable.find('tr').not(':first').not('.acs-hide').remove();
	jq_connDevTable.find("tr:first").after(htmlStr);

	if (o_connDevArray.length <= 0) {
		/* disable add button as there is no entry could be added to form */
		$("#add_btn").prop("disabled", true);
	}
	else {
		$("#add_btn").prop("disabled", false);
	}
}

function initValidation() {
	jQuery.validator.addMethod("ltstart",function(value,element){
		return this.optional(element) || value >= parseInt($("#start_port").val());
	}, "Please enter a value more than or equal to Start Port.");

	$("#pageForm").validate({
		onfocusout: false,
		onkeyup: false,
		groups: {
			public_ip: "ip_start_1 ip_start_2 ip_start_3 ip_start_4",
			private_ip: "ip_end_1 ip_end_2 ip_end_3 ip_end_4"
		},
		rules: {
			appName: {
				required: true,
				maxlength: 32
			},
			ip_start_1: {
        		required: true,
        		min: 1,
        		max: 255,
        		digits: true
        	},
			ip_start_2: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			ip_start_3: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			ip_start_4: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			ip_end_1: {
        		required: true,
        		min: 1,
        		max: 255,
        		digits: true
        	},
			ip_end_2: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			ip_end_3: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
        	},
			ip_end_4: {
        		required: true,
        		min: 0,
        		max: 255,
        		digits: true
			},
			start_port: {
				required: true,
				min: 1,
				max: 65535,
				digits: true
			},
			end_port: {
				required: true,
				min: 1,
				max: 65535,
				digits: true,
				ltstart: true
			}
		}
	});
}

function validateIpRange(ipStart, ipEnd) {
	var msg = '';
	var ipStartBin = ip4StrToBin(ipStart);
	var ipEndBin = ip4StrToBin(ipEnd);
	var ipStartMaskBin = 0;

	/* check start ip */
	var isInTSIRange = isIp4ValidInSubnet(ipStartBin, ip4StrToBin(o_wanTrueStaticIpAddr), ip4StrToBin(o_wanTrueStaticIpMask));
	if (isInTSIRange) ipStartMaskBin = ip4StrToBin(o_wanTrueStaticIpMask);

	var isInAddiSubnetRange = false;
	for (var i=0; !isInTSIRange && i<o_addiSubnets.length; ++i) {
		var subnet = o_addiSubnets[i];
		if (isIp4ValidInSubnet(ipStartBin, ip4StrToBin(subnet.ip), ip4StrToBin(subnet.mask))) {
			isInAddiSubnetRange = true;
			ipStartMaskBin = ip4StrToBin(subnet.mask);
			break;
		}
	}
	if (!isInTSIRange && !isInAddiSubnetRange) {
		msg += 'Start IP is in range of neither True Static IP subnet nor Additional Public Subnets.\n';
	}

	/* check end ip */
	if (ipStartMaskBin && !isIp4ValidInSubnet(ipEndBin, ipStartBin, ipStartMaskBin)) {
		msg += 'End IP is not in the same subnet with Start IP.\n';
	}
	else if (ipStartMaskBin && ipStartBin > ipEndBin) {
		msg += 'Start IP is greater than End IP.\n';
	}
	else if (isInTSIRange) {
		/* check if the range including TSI */
		var TSIIpBin = ip4StrToBin(o_wanTrueStaticIpAddr);
		if (TSIIpBin >= ipStartBin && TSIIpBin <= ipEndBin) {
			msg += 'WAN True Static IP ('+o_wanTrueStaticIpAddr+') should not be included in the range.\n';
		}
	}

	return msg;
}

function onsave() {
	if ($('#pageForm').valid()) {
		var errMsg = '';
		var postData = {};
		postData.ipStart = $('#ip_start_1').val() + '.' +
						$('#ip_start_2').val() + '.' +
						$('#ip_start_3').val() + '.' +
						$('#ip_start_4').val();
		postData.ipEnd = $('#ip_end_1').val() + '.' +
						$('#ip_end_2').val() + '.' +
						$('#ip_end_3').val() + '.' +
						$('#ip_end_4').val();
		/* try to validate ip range */
		errMsg = validateIpRange(postData.ipStart, postData.ipEnd);
		if (errMsg != '') {
			jAlert("Please fix the following problems:\n\n"+errMsg);
			return;
		}

		postData.enable = true;
		if (o_id === null) {
			postData.op = 'add';
		}
		else {
			postData.op = 'edit';
			postData.id = o_id;
		}

		postData.appName = $.trim($('#appName').val());
		postData.protocol = $('#protocol').comboVal();
		postData.portStart = $('#start_port').val();
		postData.portEnd = $('#end_port').val();

		jProgress('This may take several seconds', 60);
		$.ajax({
			type: 'POST',
			url: 'actionHandler/ajax_port_management.php',
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
				window.location.href = 'port_management.php';
			},
			error: function() {
				jHide();
				jAlert("Failure, please try again.");
			}
		});
	}
}

function addSelectedIp() {
	var jq_selectedRow = $('input[name=select_device]:checked').closest('tr');
	if (jq_selectedRow.length === 0) {
		alert("Please select a device to add first.");
		return;
	}
	var cdid = parseInt(jq_selectedRow.attr('cdid'));
	var selectedIp = o_connDevArray[cdid].IPAddress;
	if (selectedIp !== undefined) {
		var ipElemArr = selectedIp.split('.');
		$('[id^=ip_start_]').each(function(idx){
			$(this).val(ipElemArr[idx]);
		});
		$('[id^=ip_end_]').each(function(idx){
			$(this).val(ipElemArr[idx]);
		});
	}
	$.virtualDialog("hide");
}

function onRefreshConnectedDev() {
	$("#load_ind").show();
	if ($("#load_ind").data('isLoading') === true) {
		/* allow only one loading process */
		return;
	}
	$("#load_ind").data("isLoading", true);

	/* start fetching */
	$.ajax({
		type: "POST",
		url: "actionHandler/ajax_port_management.php",
		data: {op: 'refreshConnDev'},
		success: function(data) {
			if (data.status !== 'success') {
				return;
			}
			/* replace the device array and re-init the table */
			o_connDevArray = data.connDevArray;
			initConnDevTable();
		},
		complete: function() {
			$("#load_ind").hide();
			$("#load_ind").data("isLoading", false);
		}
	});
}

$(document).ready(function() {
    comcast.page.init("Advanced > Port Management > <?php echo $pageTypeStr; ?> Rule", "nav-port-management");

	initPmFields();
	initConnDevTable();
	initValidation();

	$('#device').click(function(){
		$.virtualDialog({
			title: "Select from below Connected Devices:",
			content: $("#device_list"),
			footer: '<div id="pop_btn_group">' +
				'<input id="ref_btn" type="button" class="btn" value="Refresh"/>' +
				'<span id="load_ind"><img alt="Refreshing" title="Refreshing" src="./cmn/img/spinner.gif" /></span>' +
				'<div id="pop_btn_group_right">' +
				'<input id="add_btn" type="button" class="btn" value="Add"/>' +
				'<input id="close_btn" type="button" class="btn alt reset" value="Close" />' +
				'</div>' +
				'</div>',
			width: "550px"
		});
		$('#close_btn').off("click").on("click", function(){
			$.virtualDialog("hide");
		});
		$('#add_btn').off("click").on("click", addSelectedIp);
		$('#ref_btn').off("click").on("click", onRefreshConnectedDev);
		$('#ref_btn').focus();
	});

	$('#btn-save').click(function(e){
		e.stopPropagation();
		e.preventDefault();
		onsave();
	});
    $("#btn-cancel").click(function() {
    	window.location.href = "port_management.php";
    });
});//end of document ready
</script>

<div id="content">
	<h1>Advanced > Port Management > <?php echo $pageTypeStr; ?> Rule</h1>
    <div id="educational-tip">
        <p class="tip"> Add/Edit a rule for port management of True Static IP by user.</p>
    </div>
	<form method="post" id="pageForm" action="">
	<div class="module forms">
		<h2><?php echo $pageTypeStr; ?> Port Management</h2>

		<div class="form-row ">
			<label  for="appName">Application Name:</label> 
			<input type="text"  class="text" value="" id="appName" name="appName" maxlength="32" />
		</div>

		<div  class="form-row odd">
			<label for="protocol">Protocol:</label>
			<select id="protocol">
				<option value="BOTH">TCP/UDP</option>
				<option value="TCP">TCP</option>
				<option value="UDP">UDP</option>
			</select>
		</div>

		<div class="form-row ">
			<label for="ip_start_1">Start True Static IP:</label>
	        <input type="text" size="2"  maxlength="3"  id="ip_start_1" name="ip_start_1" class="ipv4-addr-elem smallInput" title="first part"/>
			<label for="ip_start_2" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="ip_start_2" name="ip_start_2" class="ipv4-addr-elem smallInput" title="second part"/>
			<label for="ip_start_3" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="ip_start_3" name="ip_start_3" class="ipv4-addr-elem smallInput" title="third part"/>
			<label for="ip_start_4" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="ip_start_4" name="ip_start_4" class="ipv4-addr-elem smallInput" title="fourth part"/>
		</div>

		<div class="form-row odd">
			<label for="ip_end_1">End True Static IP:</label>
	        <input type="text" size="2"  maxlength="3"  id="ip_end_1" name="ip_end_1" class="ipv4-addr-elem smallInput" title="first part"/>
			<label for="ip_end_2" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="ip_end_2" name="ip_end_2" class="ipv4-addr-elem smallInput" title="second part"/>
			<label for="ip_end_3" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="ip_end_3" name="ip_end_3" class="ipv4-addr-elem smallInput" title="third part"/>
			<label for="ip_end_4" class="acs-hide"></label>
			.<input type="text" size="2" maxlength="3"  id="ip_end_4" name="ip_end_4" class="ipv4-addr-elem smallInput" title="fourth part"/>
		</div>

		<div class="form-row ">
			<label for="start_port">Start Port:</label>  <input type="text" class="port" value="" id="start_port" name="start_port" />
		</div>
		<div class="form-row odd">
			<label for="end_port">End Port:</label>  <input type="text" class="port" value="" id="end_port" name="end_port" />
		</div>

		<div class="form-row">
			<strong><p>Select a device to add IP address</p></strong>
			<input  id="device" type="button" value="Connected Device" class="btn"  style="position:relative;top:0px;right: 0px;"/>
		</div>

		<div class="form-btn">
			<input type="button" id="btn-save" value="save" class="btn submit"/>
			<input type="button" id="btn-cancel" value="Cancel" class="btn alt reset"/>
		</div>

	</div> <!-- end .module -->
	</form>
</div><!-- end #content -->

<style type="text/css">

#pop_btn_group {
	overflow: hidden;
	padding: 2px;
	margin-top: 4px;
}
#pop_btn_group #load_ind {
	display: none;
}
#pop_btn_group_right {
	float: right;
}
#pop_btn_group_right #add_btn, #pop_btn_group_right #close_btn {
	margin-right: 15px;
}

</style>
<div id="device_list" style="display: none;">
	<table summary="This table lists all connected devices which are in true static ip range">
	<tr>
		<th id="hdr_name">Device Name</th>
		<th id="hdr_ip">IPv4 Address</th>
		<th id="hdr_mac">MAC Address</th>
		<th id="hdr_if">Interface</th>
		<th id="hdr_add">Add</th>
	</tr>
	<tfoot>
		<tr class="acs-hide">
			<td headers="hdr_name">null</td>
			<td headers="hdr_ip">null</td>
			<td headers="hdr_mac">null</td>
			<td headers="hdr_if">null</td>
			<td headers="hdr_add">null</td>
		</tr>
	</tfoot>
	</table>
</div>

<?php include('includes/footer.php'); ?>
