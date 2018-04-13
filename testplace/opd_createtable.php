<?php

$servername = "localhost:3306";
$username = "host";
$password = "host";
$dbname = "lccgame";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // sql to create table
    $sql = "CREATE TABLE gamemsgs (
	    interid INT NOT NULL AUTO_INCREMENT, 
	    protocolid VARCHAR(30) NOT NULL,
	    msgsenderid VARCHAR(30) NOT NULL,
	    msgsenderrole VARCHAR(30) NOT NULL,
	    msgreceiverid VARCHAR(30) NOT NULL,
	    msgreceiverrole VARCHAR(30) NOT NULL,
	    msgbody VARCHAR(30) NOT NULL,
	    PRIMARY KEY(interid)
    )";

    // use exec() because no results are returned
    $conn->exec($sql);
    echo "Table MyGuests created successfully";
}catch(PDOException $e){
    echo $sql . "<br>" . $e->getMessage();
}

$conn = null;

?>