<?php








/* Plugin Name: cancel_order
 Description: 4.8.8
 Author: Suhas Rao
 Version: 1.0
 */
/*function suhas_scripts() {

    wp_enqueue_style( 'ce_cancel-style',  plugin_dir_url( __FILE__ ) . 'css/profile.css' );

}
add_action( 'wp_enqueue_scripts', 'suhas_scripts' );*/

function cancel_order_func() {
	ob_start();
  if (isset($_POST['confirm'])) {
	execute_cancel();
 }
 else {
	$correct_user = validate_user();
	if ($correct_user) {
		showAllTransactions();
	} else {
		if (isset($_POST['validated_id'])) {
			echo "Sorry, but you may only view your own profile";
		}
	}
  }
	return ob_get_clean();
}

function showAllTransactions() {
	$ce_db = access_db();
  	$curr_data = $_COOKIE['ce_info'];
  	$pieces = explode("|" , $curr_data);
  	$account_id = $pieces[0];
    $account_id = sanitize_text_field($account_id);
  /*$insert_buy_order_values = array('transaction_id' => 'testid4', 
									 'account_id' => '3', 
									 'market_id' => 'California', 
									 'game_start_date' => '2016-09-26', 
									 'game_id' => '1', 
									 'game_period_id' => '1-3', 
									 'buy_or_sell' =>'s', 
									 'allowances_buy_amt' => 0, 
									 'allowances_buy_price' => 0, 
									 'allowances_sell_amt' => 0, 
									 'allowances_sell_price' => 0, 
									 'executed_amt' => 0, 
									 'executed_price' => 0, 
									 'executed_datetime' => 0, 
									 'match_with_transaction' => 0, 
									 'sys_completn_charge_perc' => 0, 
									 'accept_partial' => 'y', 
									 'original_transaction_id' => 'testid4', 
									 'submit_datetime' => 0, 
									 'status' => 'queued', 
									 'comment' => 'test',
									 'created_date' => 0,
									 'created_by' => 'proc_tradg_realtime_bid', 
									 'updated_date' => 0,
									 'updated_by' => 'proc_tradg_realtime_bid');
	$ce_db -> insert('ce_emissions_trade', $insert_buy_order_values);
  	$insert_buy_order_values['transaction_id'] = 'testid5';
	$ce_db -> insert('ce_emissions_trade', $insert_buy_order_values);*/
	$result = $ce_db->get_results(
		$ce_db->prepare(
			"
			SELECT transaction_id, buy_or_sell, allowances_buy_amt, allowances_buy_price, allowances_sell_amt, allowances_sell_price, executed_amt, executed_price
			FROM ce_emissions_trade
			WHERE account_id = %s AND status='queued'",
		  $account_id
		  ), ARRAY_A
		);
	
  	$headers = '<th>' . implode('</th><th>', array_keys($result[0])) . '</th>';
  	$headers .= '<th>Cancel Order?</th>';
  	$rows = '<tr>';
  	$counter = 0;
  	$ids = array();
	foreach ($result as $row){
	  $currid = $row['transaction_id'];
	  $rows .= '<td>' . implode('</td><td>',array_map('htmlentities',$row)) . '</td>';
	  $rows .= '<td><input type="checkbox" name="' . $currid . '"/></td></tr><tr>';
	  array_push($ids, $currid);
	}
	$rows = substr($rows, 0, -4);
  	$idString = implode(',', $ids);
  	echo <<<HTML
	<form method = "post">
	<table>
	  <thead>
		$headers
	  </thead>
	  <tbody>
	$rows
	  </tbody>
	</table>
	<input type = "hidden" name = "idList" value = $idString>
	<input type = "submit" name = "submit_cancel_order" value = "Submit Cancel Orders">
	</form>
HTML;

	if (isset($_POST['submit_cancel_order'])) {
		confirm_cancel();
	}

}

function confirm_cancel() {
  	$ids = explode(',', $_POST['idList']);
  $postedids = array();
  if ($ids) {
  	$askstring = 'Are you sure you want to cancel the order(s) with the following transaction ids: ';
  	foreach ($ids as $id) {
	  if (isset($_POST[$id])) {
		array_push($postedids, $id);
		$askstring .= $id . ', ';
		}
	}
	$askstring = substr($askstring, 0, -2);
	$idString = implode(',', $postedids);
	echo <<<HTML
	<br>
	<br>
	<form method = "post">
	$askstring
	<input type = "hidden" name = "postedidList" value = $idString>
	<input type = "submit" name = "confirm" value = "Confirm">
	<input type = "submit" name = "cancel" value = "Cancel">
	</form>
HTML;
  }
}

function execute_cancel() {
  $postedids = explode(',',$_POST['idlist']);
  $ce_db = access_db();
  $confirmation = 'The orders with the following transaction_ids have successfully been canceled: ';
  foreach ($postedids as $id) {
	$ce_db -> delete('ce_emissions_trade', array('transaction_id' => $id));
	$confirmation .= $id . ', ';
}
  $confirmation = substr($confirmation, 0, -2);
  echo $confirmation;
}

add_shortcode('cancel_order', 'cancel_order_func');
  
?>



