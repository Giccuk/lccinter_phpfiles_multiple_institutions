<!DOCTYPE html>
<html>
<head>
	<title>Get Investor's offer</title>
</head>
<body>

<form action="trusteereply.php" method="post">
	Yes, you choose to be the investor. Then how much do you want to offer to the trustee?<br><br>
		<select name="investoroffer">
			<!--<option disabled selected value> -- select an option -- </option>-->
			<?php
				include "../trucontrol/trustgameinfo.php";
				for ($i=1;$i<=$game_total;$i++){
					#$i_str=(string)$i;
					echo "<option value=$i>$i</option>";
				}
				$i=0;
			?>
		</select><br><br>
		<input type="submit"><br><br>

</form>

<img src="smile2.png">


</body>
</html>
