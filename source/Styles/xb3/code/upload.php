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

<div id="sub-header">
  <?php include('includes/userbar.php'); ?>
</div><!-- end #sub-header -->

<?php include('includes/nav.php'); ?>

<?php
  $failedAttempt=getStr("Device.Users.User.1.NumOfRestoreFailedAttempt");
  $passLockoutAttempt=3;
  $passLockoutTimeMins=5;
?>

<script type="text/javascript">
  var failedAttempt=<?php echo $failedAttempt;?>;
  var passLockoutAttempt=<?php echo $passLockoutAttempt; ?>;
  var passLockoutTimeMins=<?php echo $passLockoutTimeMins; ?>;
  var do_not_submit=0;
  function findExtension(input){
    return input.split('.').pop().toUpperCase();
  }
  $(document).ready(function() {
    $('#uploadBtn').click(function(e){
      e.preventDefault();
      if (failedAttempt>=passLockoutAttempt) {
        alert("You have entered incorrect password " + passLockoutAttempt + " times. Configuration Restore will be locked for " + passLockoutTimeMins + " minutes");
        window.location.href="at_a_glance.php";
        do_not_submit=1;
      }
      if (!do_not_submit) {
        jConfirm(
          "By clicking OK you will lose your Current Configuraion!\nAre you sure you want to Restore Saved Configuration?"
          ,"Restore Saved Configuration"
          ,function(ret) {
            if(ret) {
              var path=document.getElementById('id1').value;
              var password=document.getElementById('VerifyPassword').value;
              if((path==null || path=="")){
                jAlert("Please Select a file to Restore the Configuration!");
              }
              else if (!((findExtension(path)=="CF2")||(findExtension(path)=="CFG"))) {
                jAlert("File type not recognized. Please upload valid .CF2 / .cfg file")
              }
              else if( (password==null) || (password==""))
              {
                jAlert("Password  is Empty. Please enter Password");
              }
              else if( /[^a-zA-Z0-9.:?]/.test(password) ) {
                jAlert('Input is not alphanumeric');
              }
              else{
                $('form').submit();
              }
            }
          });
      }
    });
  });
</script>

<div id="content">
  <form id="uploadConfig" enctype="multipart/form-data" action="restoreConfig.php" method="post">
    <input id="id1" name="file" type="file" style="border: solid 1px;">   </input>
    </br>
    </br>
    <label for="VerifyPassword">Enter Password for File:</label>
    <input type="password" size="23" id="VerifyPassword" name="VerifyPassword" class="text" value=""></input>
    </br>
    </br>
    <input class="btn" id="uploadBtn" type="button" value="Upload"> </input>
  </form>
</div>

<?php include('includes/footer.php'); ?>

