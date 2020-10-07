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

<?php include('includes/nav.php'); ?>
<link rel="stylesheet" type="text/css" href="./cmn/css/comcastPaginator.css"/>
<script type="text/javascript" src="./cmn/js/lib/jquery-simple-pagination-plugin.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    comcast.page.init("Gateway > Connection > MTA >SIP Packet Log", "nav-service-sip");
	
	$("#showlogs").click(function() {
		jConfirm("This action may take more than one minute. Do you want to continue?", "Are You Sure?", function(ret){
			if(ret){
				jProgress('This may take several seconds...', 180);
				$.ajax({
					type:"GET",
					url:"actionHandler/ajaxSet_mta_sip_packet_log.php",
					dataType:"json",
					success:function(results){
						var length=0;		
						var trClass="odd";	
						
						// $("#event_logs_today > tbody").empty();
						if (""==results) {
							document.getElementById('log_summary').innerHTML='<b>There are currently no SIP Packet Logs</b>';
							jHide();
							return;
						}
						else {
							document.getElementById('event').innerHTML='<h2>MTA SIP Packet Log</h2><table summary="This table shows SIP Packet Log" id="event_logs_today" class="data" style="word-break:break-all"><thead><th id="sip_value">Description</th><th width="111" id="sip_time">Time</th></thead><tbody></tbody><tfoot><tr class="acs-hide"><td headers="sip_value">null</td><td headers="sip_time">null</td></tr></tfoot></table>';
						}
						
						$.each(results,function(key,value) {
							$("#event_logs_today > tbody").append('<tr class="'+trClass+'"><td headers="sip_value">'+value.Des+'</td><td headers="sip_time">'+value.time+'</td></tr>');
							trClass=((trClass=="")?"odd":"");
							length++;
						});
						
						if (length>10) {
                                                        $('#event_logs_today').simplePagination({
                                                                items_per_page: 10
                                                        });
                                                }
						jHide();
					},
					error: function(){            
						jHide();
						jAlert("Failure, please try again.");
					}
				});
			}
		});
	});
});

</script>

<div id="content">
	<h1>Gateway > Connection > MTA > SIP Packet Log</h1>
	<div id="educational-tip">
		<p class="tip">Information related to the SIP Packet Log.</p>
	</div>
	<div class="module forms data" id="event">
		<h2>MTA SIP Packet Log</h2>
		<div class="form-row">
			<span class="" id="log_summary"><b>The SIP trace log didn't generate yet</b></span>
		</div>
	</div> <!-- end .module -->
	<div class="form-btn">
		<input id="showlogs" type="button" value="REFRESH" class="btn" style="position:relative;top:0px;left:300px;"/>
	</div>
</div><!-- end #content -->

<?php include('includes/footer.php'); ?>
