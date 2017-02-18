<?php
/*
 * Plugin Name: Sell Allowances
 * Description: For page 4.8.6
 * Author: Anthony Dela Paz
 */

 function display_form() {
	$min_price = 0;
	$max_price = 100;
	if(isset($_POST["permits"])) {
		$permits = $_POST["permits"];
	} else {
		$permits = 0;
	}
	if(isset($_POST["price"])) {
		$price = $_POST["price"];
	} else {
		$price = 0;
	}
	$self = $_SERVER["PHP_SELF"];
 	$ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
 	$ce_db->set_prefix("ce_");
 	$ce_db->show_errors();
	// $carbon_buy_price = $ce_db->get_var("SELECT MAX(allowances_buy_price) FROM ce_emissions_trade");
	// $cbp_formatted = number_format($carbon_buy_price, 2);
	// $carbon_sell_price = $ce_db->get_var("SELECT MAX(allowances_sell_price) FROM ce_emissions_trade");
	$csp_formatted = number_format($carbon_sell_price, 2);
	echo "Current carbon price: $" . $cbp_formatted;
	echo "<br>";
	// echo "Current carbon sell price: $" . $csp_formatted;
	echo <<<A_NAME
	<form onsubmit="return confirm('Submit order?');" method ="post">
	    Enter number of allowances: <br>
	    <input type="number" name="permits" min="0" value=$permits>
	    <br>Enter price: <br>
	    <input type="float" name="price" min=$min_price max=$max_price value=$price>
	    <br>
	    <br><input type="submit" name="submit-sell" value="Sell">
	    <input type="submit" name="submit-buy" value="Buy ">
	</form>
A_NAME;
// 	echo $_POST["permits"];
	// $confirming = false;
	// echo isset($_POST["submit-sell"]);
	if (isset($_POST["submit-sell"])) {
		//confirm_sale();
		send_sale();
	} elseif (isset($_POST["submit-buy"])) {
		send_buy();
	}
// 	echo $permits;
// 	echo <<<BREAK1
// 	<br>
// BREAK1;
// 	echo $price;
// 	echo $_POST["price"];


 }

 function confirm_sale() {
 	// send_sale();
	echo "<form method='post'>";
	echo "	Order submitted! <br>";
	echo "	Sell " . $_POST["permits"] . " allowances at $" . $_POST["price"] . "<br>";
	echo "</form>"
	// echo "  <input type='submit' name='confirm-sell' value='OK'>";
	// echo "</form>";

	// if (isset($_POST["confirm-sell"])) {
	// 	send_sale();
	// } elseif (isset($_POST["cancel-sell"])) {
	// 	// show_form();
	// 	echo "Cancelled!";
	// 	$confirming = false;
	// }
 }

 function confirm_buy() {
	echo "<form method='post'>";
	echo "	Order submitted! <br>";
	echo "	Buy " . $_POST["permits"] . " allowances at $" . $_POST["price"] . "<br>";
	// echo "  <input type='submit' name='confirm-buy' value='OK'>";
	echo "</form>";
 }

 function send_sale() {
 	$ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
 	$ce_db->set_prefix("ce_");
 	$ce_db->show_errors();
 		$prev_trans_id_str = $ce_db->get_var("SELECT transaction_id FROM ce_emissions_trade ORDER BY transaction_id DESC", 0, 0);
 	$new_S = "S" . strval(intval(substr($prev_trans_id_str, 15), 10) + 1);
 	$prev_trans_id_str = $ce_db->get_var("SELECT transaction_id FROM ce_emissions_trade ORDER BY transaction_id DESC", 0, 0);
 	$new_S = "S" . strval(intval(substr($prev_trans_id_str, 15), 10) + 1);
 	$ce_db->insert('ce_emission_trade', 
 			//data array
 			array(
 					'accept_partial' => 'y',
 					'account_id' => 'A00000',
 					'allowances_buy_amt' => $_POST["permits"],
 					'allowances_buy_price' => $_POST["price"],
 					'allowances_sell_amt' => 0,
 					'allowances_sell_price' => 0,
 					'buy_or_sell' => 'b',
 					'created_by' => 'AC',
 					'created_date' => '2016',
 					'game_period_id' => 'G00P00',
 					'game_start_date' => strval($ce_db->get_var("SELECT NOW();")),
 					'market_id' => 'Hello Market',
 					'original_transaction_id' => 'L00000-G00F00-S00000',
 					'status' => 'new',
 					'submit_datetime' => strval($ce_db->get_var("SELECT NOW();")),
 					'transaction_id' => 'L00000-G00F00-' . $new_S,
 					'updated_by' => 'AC',
 					'updated_date' => '2016',
 					
 					),
 			//format array
 			array(
 					'%s', //accept_partial
 					'%s', //account_id
 					'%d', //allowances_buy_amt
 					'%f', //allowances_buy_price
 					'%d', //allowances_sell_amt
 					'%f', //allowances_sell_price
 					'%s', //buy_or_sell
 					'%s', //created_by
 					'%s', //created_date
 					'%s', //game_period_id
 					'%s', //game_start_date
 					'%s', //market_id
 					'%s', //original_transaction_id
 					'%s', //status
 					'%s', //submit_datetime
 					'%s', //transaction_id
 					'%s', //updated_by
 					'%s' //updated_date
 					));
	confirm_sale();
 }
 

 function send_buy() {
 	// ce_emission_trade table -> transaction_id, account_id, market_id, year_start_date, game_period_id, buy_or_sell, allowances_buy_amt, allowances_buy_price, allowances_sell_amt, allowances_sell_price, accept_partial, original_transaction_id, submit_datetime, status, created_date, created_by, updated_date, updated_by
 	$self = $_SERVER["PHP_SELF"];
 	$ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
 	$ce_db->set_prefix("ce_");
 	$ce_db->show_errors();
 	$prev_trans_id_str = $ce_db->get_var("SELECT transaction_id FROM ce_emission_trade ORDER BY transaction_id DESC", 0, 0);
 	$new_S = "S" . strval(intval(substr($prev_trans_id_str, 15), 10) + 1);
 	$ce_db->insert('ce_emission_trade', 
 			//data array
 			array(
 					'accept_partial' => 'y',
 					'account_id' => 'A00000',
 					'allowances_buy_amt' => $_POST["permits"],
 					'allowances_buy_price' => $_POST["price"],
 					'allowances_sell_amt' => 0,
 					'allowances_sell_price' => 0,
 					'buy_or_sell' => 'b',
 					'created_by' => 'AC',
 					'created_date' => '2016',
 					'game_period_id' => 'G00P00',
 					'game_start_date' => strval($ce_db->get_var("SELECT NOW();")),
 					'market_id' => 'Hello Market',
 					'original_transaction_id' => 'L00000-G00F00-S00000',
 					'status' => 'new',
 					'submit_datetime' => strval($ce_db->get_var("SELECT NOW();")),
 					'transaction_id' => 'L00000-G00F00-' . $new_S,
 					'updated_by' => 'AC',
 					'updated_date' => '2016',
 					
 					),
 			//format array
 			array(
 					'%s', //accept_partial
 					'%s', //account_id
 					'%d', //allowances_buy_amt
 					'%f', //allowances_buy_price
 					'%d', //allowances_sell_amt
 					'%f', //allowances_sell_price
 					'%s', //buy_or_sell
 					'%s', //created_by
 					'%s', //created_date
 					'%s', //game_period_id
 					'%s', //game_start_date
 					'%s', //market_id
 					'%s', //original_transaction_id
 					'%s', //status
 					'%s', //submit_datetime
 					'%s', //transaction_id
 					'%s', //updated_by
 					'%s' //updated_date
 					));
	confirm_buy();
 }
 add_shortcode('sell_allowances', 'display_form');
 ?>