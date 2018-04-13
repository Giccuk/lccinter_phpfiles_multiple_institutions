<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<script>
function validateForm(){
	//var gametotal=10;
	var gametotal=<?php include "../ultimategame/ulticontrol/ultimategameinfo.php"; echo $game_total;?>;
    var userinput = document.forms["myForm"]["proposeroffer"].value;
    var printresult="please input a number from 1 to."+userinput;
    if(isNaN(userinput)||userinput<1||userinput>gametotal) {
    	var print=
		alert(printresult);
        return false;
    }else if (parseInt(userinput) != userinput){
    	alert("please input a number from ");
    	return false;
    }
}
</script>
</head>
<body>

<p>How much do you want to offer?</p>
<p id="warning"></p>
<form name="myForm" action="testshow.php" onsubmit="return validateForm()" method="post">
<input type="text" name="proposeroffer">
<input type="submit" value="submit">
</form>

</body>
</html>