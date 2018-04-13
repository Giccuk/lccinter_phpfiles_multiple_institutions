<!DOCTYPE html>
<html>
<head>
	<title>Get trustee's repay</title>
</head>
<body>
<?php 

  include "../trucontrol/trustgameinfo.php";
//==================
  if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $firstagent_state=CreateFirstagent($lccengineaddress,$institutionname,$gameprotocol_id,$firstagent_id,$firstagent_role);//create first agent 
    $interactionid_trusteeside=GetInteractionId($firstagent_state,$lccengineaddress,$institutionname);

    if ($interactionid_trusteeside!=""){//firstagent has been created successfully
      $interactionpath="http://{$lccengineaddress}/interaction/user/manager/{$institutionname}/{$interactionid_trusteeside}";
      CreateOtherAgent($lccengineaddress,$institutionname,$interactionid_trusteeside,$secondagent_id,$secondagent_role);//create second agent
      sleep(1);
      $allagentsstates_json=getrequest($interactionpath);
      $allagentsstates=json_decode($allagentsstates_json,true);
      
      if (count($allagentsstates["agents"])==2){//all two agents have been created successfully
        //$firstagent_nextstep_1=AskAgentNextStep($lccengineaddress,$institutionname,$interactionid_Trusteeside,$firstagent_id);
        $investoroffer_now=$investoroffer;
        $trusteegetNum=$game_rate*$investoroffer_now;
        $firstagent_response_1="e(invest({$investoroffer_now}, {$secondagent_id}), _)";  
        AnswerAgentNextStep($lccengineaddress,$institutionname,$interactionid_trusteeside,$firstagent_id,$firstagent_response_1);
        sleep(1);  
        
        msgstorecsv("{$interactionid_trusteeside}","{$gameprotocol_id}","{$firstagent_id}","{$firstagent_role}","{$secondagent_id}","{$secondagent_role}","e(invest({$investoroffer_now}#{$secondagent_id}))","{$sourcefiledir}/gamemsgs.csv");
        mysql_insertmsgdata("{$interactionid_trusteeside}","{$gameprotocol_id}","{$firstagent_id}","{$firstagent_role}","{$secondagent_id}","{$secondagent_role}","e(invest({$investoroffer_now}#{$secondagent_id}))");
        
        //store info value
        #writeinfovalue($interactionid_trusteeside,$investoroffer_now,"{$sourcefiledir}/trsinfo.txt");
                           
      }
      else{
        echo "Failed to create the second agent. *_*<br><br>";
      }
    }
    else{
      echo "Failed to create new interaction. *_* <br><br>";
    }
  }
  else{
    $repay="";
    if (empty($_POST["trusteechoice"])) {
          echo "<br>";
          echo '<span style="color:#FF0000;"> Nothing is entered. Please enter your repay and send again. </span>';
    } 
    else {
      $repay = test_input($_POST["trusteechoice"]);
      $interactionid_trusteeside=$_GET['interid'];
      $investoroffer_now=bindec($_GET['code']);
      #$investoroffer_now=getinfovalue($interactionid_trusteeside,"{$sourcefiledir}/trsinfo.txt");
      $trusteegetNum=$investoroffer_now*$game_rate;
      
      // check if name only contains letters and whitespace
      if (preg_match("/[^\d+]/",$repay)) {
        echo "<br>";
        echo '<span style="color:#FF0000;">Only number are allowed. Please enter your rapay and send again.</span>';
      }
      else{
        $trusteechoiceNUM=$repay*1;
        if ($trusteechoiceNUM<=$trusteegetNum) {
        
          $finalrepay=$repay;

          $secondagent_response_1="e(repay({$finalrepay}, {$firstagent_id}), _)";
          AnswerAgentNextStep($lccengineaddress,$institutionname,$interactionid_trusteeside,$secondagent_id,$secondagent_response_1);
          sleep(1);

          msgstorecsv("{$interactionid_trusteeside}","{$gameprotocol_id}","{$secondagent_id}","{$secondagent_role}","{$firstagent_id}","{$firstagent_role}","e(repay({$finalrepay}#{$firstagent_id}))","{$sourcefiledir}/gamemsgs.csv");
          mysql_insertmsgdata("{$interactionid_trusteeside}","{$gameprotocol_id}","{$secondagent_id}","{$secondagent_role}","{$firstagent_id}","{$firstagent_role}","e(repay({$finalrepay}#{$firstagent_id}))");

            //store player info
            $playerinfo_pid=$playerid;
            $playerinfo_prole=$secondagent_role;
            $playerinfo_interid=$interactionid_trusteeside;
            $playerinfo_filedir="{$sourcefiledir}/playerinfo.csv";
            store_playerinfo("{$playerinfo_pid}","{$playerinfo_prole}","{$playerinfo_interid}","{$playerinfo_filedir}");
            mysql_insertplayerinfodata("{$playerinfo_interid}","{$playerinfo_pid}","{$playerinfo_prole}");

          $finalrepay_int=intval($finalrepay);
          $finalrepay_binary=decbin($finalrepay_int);
          header("Location:http://{$gameserveraddress}/trustgame/trusteeside/investorreply.php?code=$finalrepay_binary");
        }
        else{
          echo "<br>";
          echo '<span style="color:#FF0000;">'."Your repay is bigger than {$trusteegetNum}".'</span>'.'<span style="color:#FF0000;">. Please enter your rapay and send again.</span>';
        }
      }
      
    }
  }
  
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
  //=================
  /*
  $fp=fopen('trusteeinfo.json','r');
  $interiddata_json=fread($fp,filesize('trusteeinfo.json'));
  fclose($fp);
  $interiddata=json_decode($interiddata_json,true);
  $investorchoice=$interiddata["investorchoice"];
  $trusteegetNum=$game_rate*$investorchoice;
  */

  
?>

<p>The investor has decided to offer £<?php  sleep(1); echo $investoroffer_now;?> to you. So you have £<?php echo $trusteegetNum; ?>. How much will you repay?</p>
<span style="color: #FF0000;">*Please enter the number smaller than <?php echo $trusteegetNum;?> !</span><br><br>

<form action="http://<?php echo $gameserveraddress;?>/trustgame/trusteeside/trusteeform.php?interid=<?php echo $interactionid_trusteeside;?>&code=<?php echo decbin($investoroffer_now);?>" method="post">
  <input type="text", name="trusteechoice"><br>
  <input type="submit" name="submit" value="send the rapay">
</form>

<?php
/*
  $repay="";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["trusteechoice"])) {
      echo "<br>";
      echo '<span style="color:#FF0000;"> Nothing is entered. Please enter your repay and send again. </span>';
    } 
    else {
      $repay = test_input($_POST["trusteechoice"]);
      // check if name only contains letters and whitespace
      if (preg_match("/[^\d+]/",$repay)) {
        echo "<br>";
        echo '<span style="color:#FF0000;">Only number are allowed. Please enter your rapay and send again.</span>';
      }
      else{
         $trusteechoiceNUM=$repay*1;
         if ($trusteechoiceNUM<=$trusteegetNum) {
            $fp=fopen('trusteeinfo.json','r');
            $interiddata_json=fread($fp,filesize('trusteeinfo.json'));
            fclose($fp);
            $fp2=fopen('trusteeinfo.json', 'w');
            $interiddata=json_decode($interiddata_json,true);
            $interid=$interiddata["interid"];
            $newdata=array("interid"=>$interid,"trusteerepay"=>$repay);
            $newdata_json=json_encode($newdata);
            fwrite($fp2, $newdata_json);
            fclose($fp2);

            header("Location:http://{$gameserveraddress}/trustgame/trusteeside/investorreply.php");
         }
         else{
            echo "<br>";
            echo '<span style="color:#FF0000;">'."Your repay is bigger than {$trusteegetNum}".'</span>'.'<span style="color:#FF0000;">. Please enter your rapay and send again.</span>';
         }
      }
    }
  }

  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

*/
?>

<br>
<img src="smile2.png">


</body>
</html>
