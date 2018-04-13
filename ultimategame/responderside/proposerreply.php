<!DOCTYPE html>
<html>
<head>
	<title>proposer reply</title>
</head>
<body>
<?php 
	
	#include 'responderinfo.php';
	include "../ulticontrol/ultimategameinfo.php"; 
	if ($_SERVER["REQUEST_METHOD"] != "POST"){
		#$interactionid_responderside=$_GET['interid'];
		$responderchoice_now=$_GET['code'];
		if ($responderchoice_now==1){
			echo "You accept the offer.";
		}else{
			echo "You reject the offer.";
		}
	}else{
		header("Location:http://{$gameserveraddress}/ultimategame/welcome.php");
		exit;
	}
#"/Applications/XAMPP/htdocs/lccgame_mysql_multiple_institutions/lccgame_mysql_multiple_institutions_short/ultimategame/welcome.php"

?>

<br><br>
If you want to play again, please click button below.<br><br>
<input type="button" value="Play Again" onclick="location.href='http://<?php echo $gameserveraddress?>/ultimategame/welcome.php'" >
<br><br>
<img src="bot.png">

</body>
</html>