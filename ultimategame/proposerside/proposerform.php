<!DOCTYPE html>
<html>
<head>
	<title>Get proposer's offer</title>
	<script>
	function validateForm(){
		var gametotal=10;
		//var gametotal=<?php include "../ulticontrol/ultimategameinfo.php"; echo $game_total;?>;
	    var x = document.forms["myForm"]["proposeroffer"].value;
	    if(x=="" || isNaN(x)||x<1||x>gametotal) {
			alert("please input a number from 1 to 10.");
	        return false;
	    }
	}
	</script>
</head>
<body>
	<p>Now you are a proposer. How much do you want to offer to the responser?</p>
	<form action="responderreply.php" onsubmit="return validateForm()" method="post">
		<input type="text" name="proposeroffer">
		<input type="submit">
	</form>
	<br><br>
	<img src="bot.png">
</body>
</html>
