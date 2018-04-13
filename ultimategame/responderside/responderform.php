<!DOCTYPE html>
<html>
<head>
	<title>Get proposer's offer</title>
</head>
<body>
<?php 
  #include 'responderinfo.php';
  include "../ulticontrol/ultimategameinfo.php"; 

  if ($_SERVER["REQUEST_METHOD"] != "POST"){
    $firstagent_state=CreateFirstagent($lccengineaddress,$institutionname,$gameprotocol_id,$firstagent_id,$firstagent_role);//create first agent 
    $interactionid_responderside=GetInteractionId($firstagent_state,$lccengineaddress,$institutionname);
    if ($interactionid_responderside!=""){//firstagent has been created successfully
        $interactionpath="http://{$lccengineaddress}/interaction/user/manager/{$institutionname}/{$interactionid_responderside}";
        CreateOtherAgent($lccengineaddress,$institutionname,$interactionid_responderside,$secondagent_id,$secondagent_role);//create second agent
        sleep(1);
        $allagentsstates_json=getrequest($interactionpath);
        $allagentsstates=json_decode($allagentsstates_json,true);
        if (count($allagentsstates["agents"])==2){//all two agents have been created successfully
        //$firstagent_nextstep_1=AskAgentNextStep($lccengineaddress,$institutionname,$interactionid_responderside,$firstagent_id);
          $proposeroffer_now=$proposeroffer;
          $firstagent_response_1="e(offernum({$proposeroffer_now}, richard), _)";  
          AnswerAgentNextStep($lccengineaddress,$institutionname,$interactionid_responderside,$firstagent_id,$firstagent_response_1);
          sleep(1);

          msgstorecsv("{$interactionid_responderside}","{$gameprotocol_id}","{$firstagent_id}","{$firstagent_role}","{$secondagent_id}","{$secondagent_role}","e(offernum({$proposeroffer_now}#{$secondagent_id}))","{$sourcefiledir}/gamemsgs.csv");
          mysql_insertmsgdata("{$interactionid_responderside}","{$gameprotocol_id}","{$firstagent_id}","{$firstagent_role}","{$secondagent_id}","{$secondagent_role}","e(offernum({$proposeroffer_now}#{$secondagent_id}))");

          //store interid 
          #writeinfovalue($interactionid_responderside,$proposeroffer_now,"{$sourcefiledir}/resinfo.txt");
          
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
    if (empty($_POST["responderchoice"])) {
      echo "<br>";
      echo '<span style="color:#FF0000;"> You did not choose. Please choose again. </span>';
    } 
    else{
        if (isset($_POST["responderchoice"])&&!empty($_POST["responderchoice"])){
          $postdata=$_POST["responderchoice"];
          $pattern="/(\w+)@(int\w+)@(\w+)/";
          preg_match($pattern,$postdata,$matches);
          $responderchoice_now=$matches[1];
          $interactionid_responderside=$matches[2];
          $proposeroffer_now=$matches[3];
          
          $secondagent_response_1="e(acceptornot({$responderchoice_now}, {$proposeroffer_now}), _)";
          AnswerAgentNextStep($lccengineaddress,$institutionname,$interactionid_responderside,$secondagent_id,$secondagent_response_1);
          sleep(1);

          msgstorecsv("{$interactionid_responderside}","{$gameprotocol_id}","{$secondagent_id}","{$secondagent_role}","{$firstagent_id}","{$firstagent_role}","e(acceptornot({$responderchoice_now}#{$proposeroffer_now}))","{$sourcefiledir}/gamemsgs.csv");
          mysql_insertmsgdata("{$interactionid_responderside}","{$gameprotocol_id}","{$secondagent_id}","{$secondagent_role}","{$firstagent_id}","{$firstagent_role}","e(acceptornot({$responderchoice_now}#{$proposeroffer_now}))");
          
          //store player info
          $playerinfo_pid=$playerid;
          $playerinfo_prole=$secondagent_role;
          $playerinfo_interid=$interactionid_responderside;
          $playerinfo_filedir="{$sourcefiledir}/playerinfo.csv";
          store_playerinfo("{$playerinfo_pid}","{$playerinfo_prole}","{$playerinfo_interid}","{$playerinfo_filedir}");
          mysql_insertplayerinfodata("{$playerinfo_interid}","{$playerinfo_pid}","{$playerinfo_prole}");

          if ($responderchoice_now=="accept"){
            $answer=1;
          }else{
            $answer=0;
          }
          header("Location:http://{$gameserveraddress}/ultimategame/responderside/proposerreply.php?code={$answer}");  
        }
    } 
  }


?>

<form action="responderform.php" method="post">
	
  Thanks for waiting! The proposer has decided to offer £<?php echo $proposeroffer_now;?> to you. <br>Please choose one from two options below.<br><br> 

  <input type="radio" name="responderchoice" value="accept<?php echo "@{$interactionid_responderside}@{$proposeroffer_now}";?>" >Accept the offer
  <input type="radio" name="responderchoice" value="reject<?php echo "@{$interactionid_responderside}@{$proposeroffer_now}";?>" >Reject the offer<br><br>
  <input type="submit" name="press" value="I have decided ! "><br><br>	

</form>
<?php
/*
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["responderchoice"])) {
      echo "<br>";
      echo '<span style="color:#FF0000;"> You don not choice. Please choice again. </span>';
    } 
    else{
      if (isset($_POST["responderchoice"])&&!empty($_POST["responderchoice"])){
        $secondagent_response_1="e(acceptornot({$_POST["responderchoice"]}, {$proposeroffer_now}), _)";
        AnswerAgentNextStep($lccengineaddress,$institutionname,$interactionid_responderside,$secondagent_id,$secondagent_response_1);
        
        sleep(1);

        msgstorecsv("{$interactionid_responderside}","{$gameprotocol_id}","{$secondagent_id}","{$secondagent_role}","{$firstagent_id}","{$firstagent_role}","e(acceptornot({$_POST["responderchoice"]}#{$proposeroffer_now}))");
        
        echo "+++++++++<br>{$interactionid_responderside}<br>===============";      
        //echo "You just ".$_POST["responderchoice"]." the proposer's offer.";
        #writeinfovalue($interactionid_responderside,$proposeroffer_now,"{$sourcefiledir}/resinfo.txt");
        #header("Location:http://{$gameserveraddress}/ultimategame/responderside/proposerreply.php?interid={$interactionid_responderside}&");
      }
    }
    */
?>
<img src="bot.png">


</body>
</html>