
<!DOCTYPE html>
<html>
<head>
	<title>investor reply</title>
</head>
<body>

<?php 

	include "../trucontrol/trustgameinfo.php";
	$repaynum=bindec($_GET['code']);
	echo "You just repay £{$repaynum} to the investor."; 


?>

<br><br>
If you want to play again, please click button below.<br><br>
<input type="button" value="Play Again" onclick="location.href='http://<?php echo $gameserveraddress?>/trustgame/welcome.php'" >
<br><br>
<img src="smile2.png">

</body>
</html>