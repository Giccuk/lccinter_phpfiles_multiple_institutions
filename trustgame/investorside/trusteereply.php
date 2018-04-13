<!DOCTYPE html>
<html>
<head>
  <title>reply from trustee in Trust Game</title>
</head>
<body>

<?php 

 include "../trucontrol/trustgameinfo.php";
  /*------initial information--------------------------*/
  if ($_SERVER["REQUEST_METHOD"] == "POST"){

    if (isset($_POST["investoroffer"])&&!empty($_POST["investoroffer"])){

        /*-----------2. Create first agent--------------------*/
        $firstagent_state=CreateFirstagent($lccengineaddress,$institutionname,$gameprotocol_id,$firstagent_id,$firstagent_role);
        $interactionid_investorside=GetInteractionId($firstagent_state,$lccengineaddress,$institutionname); 

        if ($interactionid_investorside!=""){
          /*---------2.2 check firstagent state---------------*/
          $interactionpath="http://{$lccengineaddress}/interaction/user/manager/{$institutionname}/{$interactionid_investorside}";
          /*----------3. create second agent----------------*/
          CreateOtherAgent($lccengineaddress,$institutionname,$interactionid_investorside,$secondagent_id,$secondagent_role);
          sleep(1);
          /*------------3.1 check if all agents are created ---------------*/
          $allagentsstates_json=getrequest($interactionpath);
          $allagentsstates=json_decode($allagentsstates_json,true);

          if (count($allagentsstates["agents"])==2){

            //$firstagent_nextstep_1=AskAgentNextStep($lccengineaddress,$institutionname,$interactionid,$firstagent_id);

            $firstagent_response_1="e(invest({$_POST["investoroffer"]}, {$secondagent_id}), _)";  
            AnswerAgentNextStep($lccengineaddress,$institutionname,$interactionid_investorside,$firstagent_id,$firstagent_response_1);
            sleep(1);

            msgstorecsv("{$interactionid_investorside}","{$gameprotocol_id}","{$firstagent_id}","{$firstagent_role}","{$secondagent_id}","{$secondagent_role}","e(invest({$_POST["investoroffer"]}#{$secondagent_id}))","{$sourcefiledir}/gamemsgs.csv");
            mysql_insertmsgdata("{$interactionid_investorside}","{$gameprotocol_id}","{$firstagent_id}","{$firstagent_role}","{$secondagent_id}","{$secondagent_role}","e(invest({$_POST["investoroffer"]}#{$secondagent_id}))");

            //$secondagent_nextstep_1=AskAgentNextStep($lccengineaddress,$institutionname,$interactionid_investorside,$secondagent_id);

            $trusteerepay=gettrusteerepay($_POST["investoroffer"],$game_rate);
            $secondagent_response_1="e(repay({$trusteerepay}, {$firstagent_id}), _)";
            AnswerAgentNextStep($lccengineaddress,$institutionname,$interactionid_investorside,$secondagent_id,$secondagent_response_1);
            sleep(1);
            
            msgstorecsv("{$interactionid_investorside}","{$gameprotocol_id}","{$secondagent_id}","{$secondagent_role}","{$firstagent_id}","{$firstagent_role}","e(repay({$trusteerepay}#{$firstagent_id}))","{$sourcefiledir}/gamemsgs.csv");
            mysql_insertmsgdata("{$interactionid_investorside}","{$gameprotocol_id}","{$secondagent_id}","{$secondagent_role}","{$firstagent_id}","{$firstagent_role}","e(repay({$trusteerepay}#{$firstagent_id}))");

            //store player info
            $playerinfo_pid=$playerid;
            $playerinfo_prole=$firstagent_role;
            $playerinfo_interid=$interactionid_investorside;
            $playerinfo_filedir="{$sourcefiledir}/playerinfo.csv";
            store_playerinfo("{$playerinfo_pid}","{$playerinfo_prole}","{$playerinfo_interid}","{$playerinfo_filedir}");
            mysql_insertplayerinfodata("{$playerinfo_interid}","{$playerinfo_pid}","{$playerinfo_prole}");

            echo "The trustee has decided to repay £{$trusteerepay} to you.<br><br>";
          }
          else{
            echo "Failed to create the second agent. *_*<br><br>";
          }
        }
        else{
          echo "Failed to create new interaction. *_* <br><br>";
        }
    }
  }
?>

If you want to play again, please click button below.<br><br>
<input type="button" value="Play Again" onclick="location.href='http://<?php echo $gameserveraddress?>/trustgame/welcome.php'" >
<br><br>

<img src="smile2.png">

</body>
</html>
