<?php
/*
 * Plugin name: Show Winner
 * Description: For page 4.8.7
 * Author: Anthony Dela Paz
 */
function show_winners() {
	echo <<<A_NAME
	<form action="confirm-allowances.php" method="post">
	    Enter number of allowances: <br>
	    <input type="number" name="permits" min="0">
	    <br>Enter price: <br>
	    <input type="number" name="price" min="0" max="">
	    <br><input type="submit" value="Submit order to sell allowances">
	</form>
A_NAME;
	$permits = $_POST["permits"];
	$price = $_POST["price"];
// 	echo $_POST["permits"];
	echo $permits;
	echo <<<BREAK1
	<br>
BREAK1;
	echo $price;
// 	echo $_POST["price"];


 }
 add_shortcode('show_winner', 'show_winners');
?>