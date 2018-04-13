<?php
    $sourcefiledir="/Applications/XAMPP/htdocs/lccgame_mysql_multiple_institutions";
    $gameserveraddress="localhost/lccgame_mysql_multiple_institutions";
    $lccengineaddress="localhost:8888";
    $institutionname="game_institution";
    $gameprotocol_id="ultimategame";
    $defaultinst="game_institution";

    $game_total=10;

    $firstagent_id="peter";   
    $firstagent_role="proposer({$game_total})";

    $secondagent_id="richard";
    $secondagent_role="responder({$game_total})";

    $proposeroption=range(1,$game_total);
    $proposeroption_rand=array_rand($proposeroption);
    $proposeroffer=$proposeroption[$proposeroption_rand];

    $responderoption=array("reject","accept");
    $randresponderoption=array_rand($responderoption);
    $responderchoice=$responderoption[$randresponderoption];

    $playerid=14;

  //0.2
  function getrequest($getpath){
    $curlrequest = curl_init();
    curl_setopt($curlrequest, CURLOPT_URL, $getpath);
    curl_setopt($curlrequest, CURLOPT_HEADER,0); 
    curl_setopt($curlrequest, CURLOPT_RETURNTRANSFER,true);
    $out_json=curl_exec($curlrequest);
    curl_close($curlrequest);

    return $out_json;
  }

  function postrequest($postpath,$postdata){
    $curlrequest=curl_init();
    curl_setopt($curlrequest, CURLOPT_URL,$postpath);
    curl_setopt($curlrequest, CURLOPT_POSTFIELDS, $postdata); 
    curl_setopt($curlrequest, CURLOPT_RETURNTRANSFER,true);
    $reply_json=curl_exec($curlrequest);
    curl_close($curlrequest);

    return $reply_json;
  }

  //1.
  function CreateInstitution($serverpath,$institutionname){
    $create_institution_path="http://{$serverpath}/create_institution";
    $institutionname_json=json_encode(array("name"=>$institutionname));
    $institutionstate=postrequest($create_institution_path,$institutionname_json);

    return $institutionstate;

  }

  //2.
  function CreateFirstagent($serverpath,$institutionname,$game_protocolid,$firstagent_id,$firstagent_role){
    $agent=array(
        "template"=>array(
        "protocol_id"=>$game_protocolid,
        "agents"=>array(
          array(
            "agent_id"=>$firstagent_id,
            "roles"=>array(array("role"=>$firstagent_role))
            )
          )
        ),
        "data"=>array()
      );
    $create_firstagent_path="http://{$serverpath}/institution/create/user/manager/{$institutionname}";
    $firstagent_json=json_encode($agent);
    $reply_json=postrequest($create_firstagent_path,$firstagent_json);

    return $reply_json;
  }

  function GetInteractionId($firstagent_state,$serverpath,$institutionname){
    $reply=json_decode($firstagent_state,true);
    $subject=$reply["path"];
    $pattern="/http:\/\/{$serverpath}\/interaction\/user\/manager\/{$institutionname}\/(\w+)/";
    preg_match($pattern,$subject,$matches);
    $interactionid=$matches[1];

    return $interactionid;
  }

//3.
  function CreateOtherAgent($serverpath,$institutionname,$interactionid,$otheragent_id,$otheragent_role){
    $agent=array(
        "template"=>array(
          "agent_id"=>$otheragent_id,
          "roles"=>array(array("role"=>$otheragent_role))
          ),
        "data"=>array()
    );
    $create_otheragent_path="http://{$serverpath}/interaction/create/user/manager/{$institutionname}/{$interactionid}";
    $secondagent_json=json_encode($agent);
    postrequest($create_otheragent_path,$secondagent_json);
  }

//4.
  function AskAgentNextStep($serverpath,$institutionname,$interactionid,$agentid){
    $agent_path="http://{$serverpath}/agent/user/manager/{$institutionname}/{$interactionid}/{$agentid}";
    $out=json_decode(getrequest($agent_path),true);
    $nextsteps=$out["next_steps"];

    return $nextsteps;
  }

//5.
  function AnswerAgentNextStep($serverpath,$institutionname,$interactionid,$agentid,$response){
      $answer_path="http://{$serverpath}/agent/elicited/user/manager/{$institutionname}/{$interactionid}/{$agentid}";
      $answer_data=array("elicited"=>$response);
      postrequest($answer_path,json_encode($answer_data));
  }


//6.
  function msgstorecsv($interid,$protocolid,$msgsenderid,$msgsenderrole,$msgreceiverid,$msgreceiverrole,$msgbody,$csvfile){
      //$csv_header=array('msgsenderid','msgsenderrole','msgreceiverid','msgreceiverrole','msgbody');
    $inputdata=array($interid,$protocolid,$msgsenderid,$msgsenderrole,$msgreceiverid,$msgreceiverrole,$msgbody);
    for ($x=0;$x<sizeof($inputdata);$x++){
      $inputdata[$x]=str_replace(',', '#', $inputdata[$x]);
    }
    $data=array("{$inputdata[0]},{$inputdata[1]},{$inputdata[2]},{$inputdata[3]},{$inputdata[4]},{$inputdata[5]},{$inputdata[6]}");
    $fp=fopen("{$csvfile}",'a');
    foreach ($data as $row) {
      fputcsv($fp, explode(',',$row));
    }
    fclose($fp);
  }

  //7.
  function store_playerinfo($playerid,$playerrole,$interid,$storefiledir){
    $inputdata=array($playerid,$playerrole,$interid);
    for ($x=0;$x<sizeof($inputdata);$x++){
      $inputdata[$x]=str_replace(',', '#', $inputdata[$x]);
    }
    $data=array("{$inputdata[0]},{$inputdata[1]},{$inputdata[2]}");
    $fp=fopen("{$storefiledir}",'a');
    foreach ($data as $row) {
      fputcsv($fp, explode(',',$row));
    }
    fclose($fp);
  }

//8.
  function mysql_insertmsgdata($interid,$protocolid,$msgsenderid, $msgsenderrole,$msgreceiverid,$msgreceiverrole, $msgbody){

      $servername = "localhost:3306";
      $username = "host";
      $password = "host";
      $dbname = "lccgame";
      $tablename="gamemsgs";

      $pattern="/int(\d+)/";
      $subject=$interid;
      preg_match($pattern,$subject,$matches);
      $interid_int=(int)$matches[1];

      $sql_exist="select * from {$tablename}";
      $sql_createdb="CREATE TABLE {$tablename} (gameid INT(11) AUTO_INCREMENT, interid INT(11),protocolid VARCHAR(255) , msgsenderid VARCHAR(255) , msgsenderrole VARCHAR(255) , msgreceiverid VARCHAR(255) , msgreceiverrole VARCHAR(255), msgbody VARCHAR(255),PRIMARY KEY(gameid))";
      $sql_insert="INSERT INTO {$tablename} (interid, protocolid, msgsenderid, msgsenderrole, msgreceiverid, msgreceiverrole, msgbody) VALUES ('{$interid_int}', '{$protocolid}', '{$msgsenderid}', '{$msgsenderrole}', '{$msgreceiverid}', '{$msgreceiverrole}', '{$msgbody}')";

      $conn = new mysqli($servername, $username, $password, $dbname);
      if ($conn->query($sql_exist)){
        $conn->query($sql_insert);
      }else{
        if ($conn->query($sql_createdb)){
          $conn->query($sql_insert);
        }else{
          echo '<br>'."failed to create db";
        }
      }
      $conn->close();
  }

//9.
  function mysql_insertplayerinfodata($interid,$userid,$playerrole){

      $servername = "localhost:3306";
      $username = "host";
      $password = "host";
      $dbname = "lccgame";
      $tablename="playerinfo";

      $pattern="/int(\d+)/";
      $subject=$interid;
      preg_match($pattern,$subject,$matches);
      $interid_int=(int)$matches[1];

      $userid_int=(int)$userid;

      $sql_exist="select * from {$tablename}";
      $sql_createtable="CREATE TABLE {$tablename} ( gameid INT(11) NOT NULL AUTO_INCREMENT,interid INT(11), userid INT(11), playerrole VARCHAR(255),PRIMARY KEY(gameid))";
      $sql_insert="INSERT INTO {$tablename} (interid, userid, playerrole ) VALUES ('{$interid_int}','{$userid}', '{$playerrole}')";

      $conn = new mysqli($servername, $username, $password, $dbname);
      if ($conn->query($sql_exist)){
        $conn->query($sql_insert);
      }else{
        if ($conn->query($sql_createtable)){
          $conn->query($sql_insert);
        }else{
          echo '<br>'."failed to create db";
        }
      }
      $conn->close();
  }
    /*//0.0.1
    function writeinfovalue($interid,$otherinfo,$infofile){
      $keydata_txt="{$interid}@{$otherinfo}\n";
      $fp=fopen("{$infofile}",'a');
      fwrite($fp, $keydata_txt);
      fclose($fp);
    }
    function getinfovalue($interid,$infofile){
      $fp=fopen("{$infofile}",'r');
      if ($fp){
        while (($line = fgets($fp)) !== false) {
          $pattern="/(int\w+)@(\w+)/";
          preg_match($pattern,$line,$matches);
          if($matches[1]==$interid){
            $infovalue=$matches[2];
          }
        }
        fclose($fp);
        return $infovalue;
      }
      else{
        echo "Fail to open id file.";
      }
    }*/

  /*----------------0. check whether the server is ready-------------
  echo "0. Check initial state<br><br>";
  echo getrequest("http://{$localhost_path}/institutions");echo '<br><br>';

  /*--------------1. create an institution------------------
  echo "1. Create an institution<br><br>";
  CreateInstitution($localhost_path,$institutionname);
  
  /*----------1.1check if the new institution exists
  echo "1.1 Check if institution exists:<br><br>";
  echo getrequest("http://{$localhost_path}/institutions");echo '<br><br>'; 

  /*-----------2. Create first agent--------------------
  echo "2. Create first Agent <br><br>";
  $interactionid=CreateFirstagent($localhost_path,$institutionname,$game_protocolid,$firstagent_id,$firstagent_role);

  /*---------2.2 check firstagent state---------------
  echo "2.2 Check if firstagent exists<br><br>'";
  $interactionpath="http://{$localhost_path}/interaction/user/manager/{$institutionname}/{$interactionid}";
  var_dump(getrequest($interactionpath));echo"<br><br>";

  /*----------3. add second agent----------------
  echo "3. Create second agent<br><br>";
  CreateOtherAgent($localhost_path,$institutionname,$interactionid,$secondagent_id,$secondagent_role);

  /*------------3.1 check if all agents are created ---------------
  echo "3.1 Check if agents all exist:<br><br>";
  var_dump(getrequest($interactionpath)); echo '<br><br>';
  sleep(1);

  /*------------4. ask for first agent's next step --------------------
  echo "4. Ask for first agent's next_step <br><br>";
  $firstagent_nextstep_1=AskAgentNextStep($localhost_path,$institutionname,$interactionid,$firstagent_id);
  var_dump($firstagent_nextstep_1);echo"<br><br>";

 /*---get the body of next_step
 
 $pattern="#(e|i)\(((\w+)\((\w+)\,\s(\w+)\))\,\s\_\)#";
  preg_match($pattern,$next_step_set[0],$matches);
  var_dump($matches);

  */

  /*---------5. answer firstagent---------------
  echo "5. Answer first agnt<br><br>";
  AnswerAgentNextStep($localhost_path,$institutionname,$interactionid,$firstagent_id,$firstagent_response_1);
  sleep(1);

  /*---------6. get second agnet's nextstep---------------------------------
  echo "6. Get second agnet's next step<br><br>";
  $secondagent_nextstep_1=AskAgentNextStep($localhost_path,$institutionname,$interactionid,$secondagent_id);
  var_dump($secondagent_nextstep_1);echo"<br><br>";

  /*--------7. answer second agent--------------------------------
  echo "7. Answer second agent<br><br>";
  AnswerAgentNextStep($localhost_path,$institutionname,$interactionid,$secondagent_id,$secondagent_response_1);
  sleep(1);

  /*--------8 check next step------------------------
  echo "8. Check next step<br><br>";
  $out_json=AskAgentNextStep($localhost_path,$institutionname,$interactionid,$firstagent_id);
  sleep(1);
  $out_json2=AskAgentNextStep($localhost_path,$institutionname,$interactionid,$secondagent_id);
  sleep(1);
  var_dump($out_json);echo"<br><br>";
  var_dump($out_json2);echo"<br><br>";

  */

?>
