<?php

$interactionid_proposerside=1119;
$gameprotocol_id="ultimategame";
$firstagent_id="peter";
$firstagent_role="proposer(10)";
$secondagent_id="richard";
$secondagent_role="responder(10)";
$proposeroffer="3";
$msgbody="e(offernum({$proposeroffer}#{$secondagent_id}))";

$interid=$interactionid_proposerside;
$protocolid=$gameprotocol_id;
$msgsenderid=$firstagent_id;
$msgsenderrole=$firstagent_role;
$msgreceiverid=$secondagent_id;
$msgreceiverrole=$secondagent_role;


function insertData_DPO($sql){
	$servername = "localhost:3306";
	$username = "host";
	$password = "host";
	$dbname = "lccgame";
	try {
	    $conn = new PDO("mysql:host={$servername};dbname={$dbname}", $username, $password);
	    // set the PDO error mode to exception 
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    // use exec() because no results are returned
	    $conn->exec($sql);
	    #echo "New record created successfully<br>";
	}catch(PDOException $e){
	    #echo $sql . "<br>" . $e->getMessage();
	}	
}

function tableExits($servername,$username,$password,$dbname,$table){
	try{
		$conn = new PDO("mysql:host={$servername};dbname={$dbname}", $username, $password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql="select * from '{$table}'";
		$conn->exec($sql);
		return 1;
	}catch(PDOException $e){
		#echo "Table $table does not exit".$e->getMessage();
		return 0;
	}
}

function createTable($servername,$username,$password,$dbname,$sql){
	try {
	    $conn = new PDO("mysql:host={$servername};dbname={$dbname}", $username, $password);
	    // set the PDO error mode to exception
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    // sql to create table
	    // use exec() because no results are returned
	    $conn->exec($sql);
	    #echo "Table MyGuests created successfully";
	}catch(PDOException $e){
    	#echo $sql . "<br>" . $e->getMessage();
	}
}

#msgtodb($interactionid_proposerside,$gameprotocol_id,$firstagent_id,$firstagent_role,$secondagent_id,$secondagent_role,$msgbody);

$exitflag=tableExits("localhost:3306","host","host","lccgame","gamemsgs");
echo $exitflag;

if ($exitflag==1){
	insertdata("INSERT INTO gamemsgs (interid, protocolid, msgsenderid, msgsenderrole, msgreceiverid, msgreceiverrole, msgbody) VALUES ('101', '{$protocolid}', '{$msgsenderid}', '{$msgsenderrole}', '{$msgreceiverid}', '{$msgreceiverrole}', '{$msgbody}')");
	echo "inserted data int101";

}else{
	createTable("localhost:3306","host","host","lccgame",
		"CREATE TABLE gamemsgs (    
			interid INT NOT NULL AUTO_INCREMENT, 
		    protocolid VARCHAR(30) NOT NULL,
		    msgsenderid VARCHAR(30) NOT NULL,
		    msgsenderrole VARCHAR(30) NOT NULL,
		    msgreceiverid VARCHAR(30) NOT NULL,
		    msgreceiverrole VARCHAR(30) NOT NULL,
		    msgbody VARCHAR(30) NOT NULL,
		    PRIMARY KEY(interid)
	    )");
	insertdata("INSERT INTO gamemsgs (interid, protocolid, msgsenderid, msgsenderrole, msgreceiverid, msgreceiverrole, msgbody) VALUES ('100', '{$protocolid}', '{$msgsenderid}', '{$msgsenderrole}', '{$msgreceiverid}', '{$msgreceiverrole}', '{$msgbody}')");
	echo "created table and insertdata int100";
}





?>