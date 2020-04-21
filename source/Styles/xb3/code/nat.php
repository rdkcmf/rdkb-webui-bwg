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
/* construct the nat table for output */
$rootObjName    = "Device.NAT.PortMapping.";
$paramNameArray = array("Device.NAT.PortMapping.");
$mapping_array  = array("X_Comcast_com_PublicIP", "InternalClient", "Enable");
$natArray = getParaValues($rootObjName, $paramNameArray, $mapping_array, true);

$natEnabled = getStr('Device.NAT.X_Comcast_com_EnableNATMapping') === 'true';
$OneToOneNAT= getStr("Device.DeviceInfo.X_RDKCENTRAL-COM_RFC.Feature.OneToOneNAT");
/*if ($_DEBUG) {
	$natEnabled = true;
	$natArray = array(
		array('__id' => '2', 'X_Comcast_com_PublicIP' => '2.1.1.1', 'InternalClient' => '10.0.0.2', 'Enable' => 'true'),
		array('__id' => '5', 'X_Comcast_com_PublicIP' => '0.0.0.0', 'InternalClient' => '10.0.0.3', 'Enable' => 'true'),
		array('__id' => '3', 'X_Comcast_com_PublicIP' => '3.1.1.1', 'InternalClient' => '10.0.0.3', 'Enable' => 'true')
	);
}*/

/* remove those non-11nat entries */
for ($i = count($natArray) - 1; $i >= 0; --$i) {
	$publicIp = $natArray[$i]['X_Comcast_com_PublicIP'];

	if (empty($publicIp) || $publicIp === '0.0.0.0') {
		/* this is not 1-1 nat entry, remove it */
		array_splice($natArray, $i, 1);
	}
}

?>

<script type="text/javascript">
var o_natEnabled = <?php echo $natEnabled ? 'true' : 'false'; ?>;
var oneToOneNAT = ("<?php echo $OneToOneNAT; ?>"=="true") ? true : false;
function ondelete() {
	var $this = $(this);
	var id = $this.closest('tr').attr('eid');
	var postData = {};

	//console.log('To delete nat entry with id ['+id+']');

	postData.toOneOneNat = true;
	postData.del = id;

	jProgress('This may take several seconds', 60);
	$.ajax({
		type: 'POST',
		url: 'actionHandler/ajax_port_forwarding.php',
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
			else {
				window.location.reload(true);
			}
		},
		error: function() {
			jHide();
			jAlert("Failure, please try again.");
		}
	});
}

function onenable(isEnabling) {
	var $this = $(this);
	var id = $this.closest('tr').attr('eid');
	var postData = {};

	postData.toOneOneNat = true;
	postData.active = true;
	postData.id = id;
	postData.isChecked = isEnabling;

	jProgress('This may take several seconds', 60);
	$.ajax({
		type: 'POST',
		url: 'actionHandler/ajax_port_forwarding.php',
		dataType: 'json',
		data: postData,
		success: function(data) {
			jHide();
			if (data.status != 'success') {
				var str = "Failed, please try again later.";
				if (data.msg) {
					str += '\nMessage: ' + data.msg;
				}
				/* restore the previous state */
				$this.prop("checked", !isEnabling);

				jAlert(str);
				return;
			}
			else {
				window.location.reload(true);
			}
		},
		error: function() {
			/* restore the previous state */
			jHide();
			$this.prop("checked", !isEnabling);
			jAlert("Failure, please try again.");
		}
	});
}

function ondisableAll(isDisabling) {
	var $this = $(this);
	var postData = {};

	postData.toOneOneNat = true;
	postData.disableAllNat = isDisabling;

	jProgress('This may take several seconds', 60);
	$.ajax({
		type: 'POST',
		url: 'actionHandler/ajax_port_forwarding.php',
		dataType: 'json',
		data: postData,
		success: function(data) {
			jHide();
			if (data.status != 'success') {
				var str = "Failed, please try again later.";
				if (data.msg) {
					str += '\nMessage: ' + data.msg;
				}
				/* restore the previous state */
				$this.prop("checked", !isDisabling);

				jAlert(str);
				return;
			}
			else {
				setTimeout(function() {
					window.location.reload(true);
				}, 2000);
			}
		},
		error: function() {
			jHide();
			/* restore the previous state */
			$this.prop("checked", !isDisabling);
			jAlert("Failure, please try again.");
		}
	});
}

function initEvents() {
	/* enable checkbox */
	$('input[name=EnableNat]').unbind('click').click(function(){
		var that = this;
		var isEnabling = $(this).prop("checked");
		jConfirm('Are you sure you want to '+(isEnabling ? 'enable' : 'disable')+' this NAT mapping?',
			"Are You Sure?", function(ret) {
			if(ret) {
				onenable.call(that, isEnabling);
			}
			else {
				$(that).prop("checked", !isEnabling);
			}
		});
	});

	/* delete button */
	$('td.delete > a.btn').unbind('click').click(function(e){
		e.stopPropagation();
		e.preventDefault();
		if (!o_natEnabled || !oneToOneNAT) {
			/* prevent this action */
			return;
		}
		var message = ($(this).attr("title").length > 0) ?
			"Are you sure you want to " + $(this).attr("title") + "?" : "Are you sure?";
		var that = this;
		jConfirm(message, "Are You Sure?", function(ret) {
			if(ret) {
				ondelete.call(that);
			}    
		});
	});

	/* add and edit button */
	if (!o_natEnabled) {
		$('#add-service').unbind('click').click(function(e){
			e.preventDefault();
			e.stopPropagation();
		});
		$('td.edit > a.btn').unbind('click').click(function(e){
			e.preventDefault();
			e.stopPropagation();
		});
	}

	/* disable all */
	$('#disableAll').unbind('click').click(function(){
		var that = this;
		var isDisabling = $(this).prop("checked");
		jConfirm('Are you sure you want to '+(isDisabling ? 'disable <b>ALL</b>' : 'enable')+' NAT mapping?',
			"Are You Sure?", function(ret) {
			if(ret) {
				ondisableAll.call(that, isDisabling);
			}
			else {
				$(that).prop("checked", !isDisabling);
			}
		});
	});
}

function onchangeNatDisable(disabled, preFill) {
	if (disabled === undefined) {
		disabled = $('#disableAll').prop("checked");
	}
	else {
		$('#disableAll').prop("checked", disabled);
	}
	if (disabled) {
		$('#forwarding-items').addClass('disabled');
		$('.module.data *').addClass('disabled');
		$('.module.data :checkbox').prop("disabled", true);
	}
	else {
		$('#forwarding-items').removeClass('disabled');
		$('.module.data *').removeClass('disabled');
		$('.module.data :checkbox').prop("disabled", false);
	}
}

function initNatFields() {
	if(oneToOneNAT){
		onchangeNatDisable(!o_natEnabled, true);
	}else{
		$('#disableAll').prop("checked",!o_natEnabled );
		$('#disableAll').prop("disabled", true);
		$('#disableAll').addClass("disabled");
		$('#forwarding-items').addClass('disabled');
		$('.module.data *').addClass('disabled');
		$('.module.data :checkbox').prop("disabled", true);
		$('#add-service').unbind('click').click(function(e){
			e.preventDefault();
			e.stopPropagation();
		});
		$('td.edit > a.btn').unbind('click').click(function(e){
			e.preventDefault();
			e.stopPropagation();
		});
	}
		
}

$(document).ready(function() {
    comcast.page.init("Advanced > NAT", "nav-nat");

	initNatFields();
	initEvents();
});

</script>

<div id="content">
	<h1>Advanced > NAT</h1>

	<div id="educational-tip">
			<p class="tip">Manage 1-to-1 Network Address Translation.</p>
			<p class="tip">1-to-1 NAT allows internal servers be accessable by external clients using public IP address.</p>
			<p class="tip">Unselect Disable All check box and click +ADD NEW to add 1-to-1 NAT rule.</p>
	</div>

	<form action="" method="post">
	<div class="module">

	<div class="form-row ">
		<input type="checkbox" name="disableAll" value="disable" id="disableAll" /><b><label for="disableAll">Disable All</label></b>
	</div>

	</div>
	</form>
	<div id=forwarding-items>
	<div class="module data">
	<h2>Network Address Translation</h2>
			<p class="button"><a href="nat_edit.php?t=add" class="btn" id="add-service">+ ADD New</a></p>

		<table class="data" summary="This table lists all one to one NAT rules">
		    <tr>
		        <th id="hdr_number">#</th>
				<th id="hdr_publicIp">Public IP Address</th>
				<th id="hdr_privateIp">Private IP Address</th>
				<th id="hdr_enable">Enable</th>
				<th id="hdr_controls" colspan="2">&nbsp;</th>
		    </tr>
<?php
/* output the nat table */
if (count($natArray) > 0) {
	$eIdx = 0;
	$htmlStr = '';
	foreach ($natArray as $entry) {
		$publicIp = $entry['X_Comcast_com_PublicIP'];
		$privateIp = $entry['InternalClient'];
		$isEnable = $entry['Enable'] === 'true';

		$htmlStr .= '<tr eid="'.$entry['__id'].'" '.(($eIdx%2 != 0) ? 'class="odd"' : "").'>';
		$htmlStr .= '<td headers="hdr_number">'.($eIdx+1).'</td>';
		$htmlStr .= '<td headers="hdr_publicIp">'.$publicIp.'</td>';
		$htmlStr .= '<td headers="hdr_privateIp">'.$privateIp.'</td>';
		$htmlStr .= '<td headers="hdr_enable"><label for="EnableNat'.$eIdx.'" class="acs-hide"></label>'.
			'<input type="checkbox" id="EnableNat'.$eIdx.'" name="EnableNat" '.($isEnable ? 'checked' : '').' /></td>';
		$htmlStr .= '<td headers="hdr_controls" class="edit"><a href="nat_edit.php?id='.$entry['__id'].'" class="btn" >Edit</a></td>';
		$htmlStr .= '<td headers="hdr_controls" class="delete"><a href="javascript:void(0)" class="btn" title="delete NAT mapping for '.$privateIp.'?">x</a></td>';
		$htmlStr .= '</tr>';

		$eIdx++;
	}
	echo $htmlStr;
}
else {
	echo '<tr><td headers="hdr_number" colspan="6">No entries!</td></tr>';
}
?>
		<tfoot>
			<tr class="acs-hide">
				<td headers="hdr_number">null</td>
				<td headers="hdr_publicIp">null</td>
				<td headers="hdr_privateIp">null</td>
				<td headers="hdr_enable">null</td>
				<td headers="hdr_controls">null</td>
				<td headers="hdr_controls">null</td>
			</tr>
		</tfoot>
		</table>

	</div> <!-- end .module -->
	</div>
</div><!-- end #content -->
<?php include('includes/footer.php'); ?>
