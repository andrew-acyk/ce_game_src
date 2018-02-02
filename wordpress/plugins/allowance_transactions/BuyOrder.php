<?php
/* Plugin Name: Buy_allowance
 Description: 4.8.5
 Author: Adish Jain
 Date: January 10th, 2018
 Version: 1.0
 */
function buy_allowance() {
	ob_start();
		if (isset($_POST['confirm'])) {
			confirmation_sent();
		} else {
		  	$correct_user = validate_user();
			if ($correct_user) {
				show_curr_allowances();
			} else {
			  	if (isset($_POST['validated_id'])) {
					echo "Sorry, but you may only view your own profile";
				}
		  	}
		}
	return ob_get_clean();
}

function show_curr_allowances() {
	  	echo <<<HTML

	  	<form method = "post">
	  	Please enter the number of allowances you would like to buy:
		<input type = "number" name = "allowances_to_buy" min = "0" max = "100000">
		<br>
		<br>

		Please enter the price you are willing to pay per allowance:
		<input type = "price" name = "price_to_pay" min = "0" max "100000">

	  	<input type = "hidden" name = "validated_id" value = "true">
	  	<input type = "submit" name = "submit_buy_allowance" value = "Submit Buy Request">
	  	</form>
HTML;

		if (isset($_POST['submit_buy_allowance'])) {
			display_confirm();

		}
	}

function display_confirm() {
	$allowances_to_buy = $_POST['allowances_to_buy'];
    $price_to_pay = $_POST['price_to_pay'];
	echo <<<HTML
	<br>
	<br>
	<form method = "post">
	Are you sure you want to buy $allowances_to_buy allowances for $price_to_pay dollars each?
	<input type = "hidden" name = "allowances_to_buy" value = $allowances_to_buy>
	<input type = "hidden" name = "price_to_pay" value = $price_to_pay>
	<input type = "submit" name = "confirm" value = "Confirm">
	<input type = "submit" name = "cancel" value = "Cancel">
	</form>
HTML;

}
function confirmation_sent() {
	#collecting all our variables
  	$ce_db = access_db();
  	$curr_data = $_COOKIE['ce_info'];
  	$pieces = explode("|" , $curr_data);
  	$account_id = $pieces[0];
  	$wp_login_id = $pieces[1];
	$game_start_date = $pieces[2];
	$game_id = $pieces[3];
	$period_id = $pieces[4];
	$game_period_id = $pieces[5];
	$plant_id = $pieces[6];
  	$carbon_price_last_perd = $pieces[7];

  	$get_next_seq_num_query = "SELECT parameter_value FROM ce_configuration_parameter WHERE parameter_name = 'ce_transaction_next_seq_num'";
  	$sequenceNum = $ce_db -> get_var($get_next_seq_num_query);

  	$transaction_id = 'A0000' . $account_id . '-G0' . $game_id . 'P0' . $period_id .'F01-' . 'S000000' . $sequenceNum;
  	$date_submitted = date('Y-m-d H:i:s');

  	$allowances_to_buy = $_POST['allowances_to_buy'];
    $price_to_pay = $_POST['price_to_pay'];


  	#insert buy order into db
  	$insert_buy_order_values = array('transaction_id' => $transaction_id,
									 'account_id' => $account_id,
									 'market_id' => 'California',
									 'game_start_date' => $game_start_date,
									 'game_id' => $game_id,
									 'game_period_id' => $game_period_id,
									 'buy_or_sell' =>'b',
									 'allowances_buy_amt' => $allowances_to_buy,
									 'allowances_buy_price' => $price_to_pay,
									 'allowances_sell_amt' => 0,
									 'allowances_sell_price' => 0,
									 'executed_amt' => 0,
									 'executed_price' => 0,
									 'executed_datetime' => 0,
									 'match_with_transaction' => 0,
									 'sys_completn_charge_perc' => 0,
									 'accept_partial' => 'y',
									 'original_transaction_id' => $transaction_id,
									 'submit_datetime' => $date_submitted,
									 'status' => 'new',
									 'comment' => 'test',
									 'created_date' => $date_submitted,
									 'created_by' => 'proc_tradg_realtime_bid',
									 'updated_date' => $date_submitted,
									 'updated_by' => 'proc_tradg_realtime_bid');
		$ce_db -> insert('ce_emissions_trade', $insert_buy_order_values);


  	#update get_next_seq_num in ce_configuration_parameter table
  	$ce_db -> update('ce_configuration_parameter', array('parameter_value' => $sequenceNum + 1), array('parameter_name' => 'ce_transaction_next_seq_num'));


  	#check sell queue for matching order (no splitting, only totally_completed orders on both buy and sell side)
    $get_sell_orders_query = "SELECT * FROM ce_emissions_trade WHERE buy_or_sell = 's'
								AND account_id != $account_id
								AND allowances_sell_price <= $price_to_pay
								AND allowances_sell_amt = $allowances_to_buy
								AND status = 'queued'"; #are these the right conditions?
  	$sell_order = $ce_db -> get_row($get_sell_orders_query, 'ARRAY_N');

  	if (empty($sell_order)) {
		$ce_db -> update('ce_emissions_trade', array('status' => 'queued'), array('transaction_id' => $transaction_id));
	  	echo 'Buy order waits to be executed.<br/>';
	  	echo "Transaction id: " . $transaction_id . '<br/>';
	  	echo "Account id: " . $account_id . '<br/>';
			echo "Game Period id: " . $game_period_id . '<br/>';
	    echo "Number of requested allowances: " . $allowances_to_buy . '<br/>';
	    echo "Price per allowance: " . $price_to_pay . '<br/>';
	    echo "Date submitted: " . $date_submitted . '<br/>';
	    echo "Status: queued";
	} else {
	  	$ce_db -> update('ce_emissions_trade', array('status' => 'totally_completed', 'match_with_transaction' => $sell_order[0], 'executed_amt' => $allowances_to_buy, 'executed_price' => $price_to_pay), array('transaction_id' => $transaction_id)); #update buy
	  	$ce_db -> update('ce_emissions_trade', array('status' => 'totally_completed','match_with_transaction' => $transaction_id, 'executed_amt' => $allowances_to_buy, 'executed_price' => $price_to_pay), array('transaction_id' => $sell_order[0]));  #update sell
	    echo "Buy order completed!" . '<br/>';
	  	echo "Transaction id: " . $transaction_id . '<br/>';
	  	echo "Account id: " . $account_id . '<br/>';
			echo "Game Period id: " . $game_period_id . '<br/>';
	    echo "Number of requested allowances: " . $allowances_to_buy . '<br/>';
	    echo "Price per allowance: " . $price_to_pay . '<br/>';
	    echo "Date submitted: " . $date_submitted . '<br/>';
	    echo "Status: Totally completed";
	}

}

add_shortcode('sell_allowances', 'buy_allowance');

?>
