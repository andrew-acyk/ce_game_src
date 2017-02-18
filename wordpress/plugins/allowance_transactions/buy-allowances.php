<?php
/*
 * Plugin Name: Buy Allowances
 * Description: 4.8.5 Trading screen for buying (bid) Carbon Credits.
           - Pick "submit bid order on carbon permits" from menu.
           - Enter login id or account name.
             It verifies the identity of the user using the session info in the cookie.
           - Enter number of permits you want to bid and price. 
           - Click "submit bid order" button to bid permits.  The page asks for confirmation on "bid", "price", and "amount".
           - Click "confirm" to continue.
           - The page checks the "ask" queue for matching "ask" order.
           - If found, execute the bid order.  It puts a message "bid order completed".
             It also shows: transaction_id, account_id, year_start_date, game_period_id,
             ccredit_bid_amt, ccredit_bid_price, submit_datetime, and status.
             Click on the "ok" button to close the screen.
           - If not found, put bid order on the "bid" queue for later scheduled execution.  It puts a message "bid order waits to be executed".
             It also shows: transaction_id, account_id, year_start_date, game_period_id,
             ccredit_bid_amt, ccredit_bid_price, submit_datetime, and status.
             Click on the "ok" button to close the screen.
           - The page uses table ce_carbon_trade
 * Author: Anthony Dela Paz
 */
function test_JS() {
	echo <<<JS_TEST
	<script>
	function saySomething(n) {
		window.alert(n);
	}
	</script>
  <form method = 'post'>
  Enter number of allowances: <br>
      <input type="number" name="permits" min="0" value=$permits>
  </form>
	<button type="button" onclick="saySomething($permits)">Say Hello!</button>
JS_TEST;
}

add_shortcode('buy_allowances', 'test_JS');

// function cookie() {
// 	$cookie_key = 'buy_cookies';

// 	if(!isset($_COOKIE[$cookie_key])) {
// 		if (is_user_logged_in() && $user_id = get_current_user_id()) {
// 			setcookie($cookie_key, $user_id, time()+3600);
// 			add_shortcode('buy_allowances', 'show_form');
// 		} else {
// 			add_shortcode('buy_allowances', 'prompt_login');
// 		}
		
// 	}
// 	else {
// 		if (!is_user_logged_in()) {
// 			add_shortcode('buy_allowances', 'prompt_login');
// 		} else {
// 			add_shortcode('buy_allowances', 'show_form');
// 		}
// 	}
// }

// function prompt_login() {
// 	$redirect = wp_login_url(get_permalink());
// 	echo '<p>';
// 	echo "<a href='{$redirect}' title='Login'>Login</a> to see your profile</p>";
// }

// function confirm_order() {
// 	send_order();
// 	echo "<form method='post'>";
// 	echo "	Confirm order <br>";
// 	echo "	Do you want to buy " . $_POST["permits"] . " allowances at $" . $_POST["price"] . "?<br>";
// 	echo "  <input type='submit' name='confirm-buy' value='OK'>";
// 	echo "  <input type='submit' name='cancel-buy' value='Cancel'>";
// 	echo "</form>";

// 	if (isset($_POST["confirm-buy"])) {
// 		echo "Confirmed!";
// 	} elseif (isset($_POST["cancel-buy"])) {
// 		// show_form();
// 		echo "Cancelled!";
// 	}
//  }

//  function send_order() {
//  	// ce_emission_trade table -> transaction_id, account_id, market_id, year_start_date, game_period_id, buy_or_sell, allowances_buy_amt, allowances_buy_price, allowances_sell_amt, allowances_sell_price, accept_partial, original_transaction_id, submit_datetime, status, created_date, created_by, updated_date, updated_by
//  	$self = $_SERVER["PHP_SELF"];
//  	$ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
//  	$ce_db->set_prefix("ce_");
//  	$ce_db->show_errors();
//  	$ce_db->insert('ce_emission_trade', 
//  			//data array
//  			array(
//  					'transaction_id' => 'L00000-G00F00-S00000',
//  					'account_id' => 'A00000',
//  					'market_id' => 'Hello Market',
//  					'year_start_date' => '2016',
//  					'game_period_id' => 'G00P00',
//  					'buy_or_sell' => 'b',
//  					'allowance_buy_amt' => 20,
//  					'allowance_buy_price' => 1.60,
//  					'allowance_sell_amt' => 0,
//  					'allowance_sell_price' => 0,
//  					'accept_partial' => 'n',
//  					'original_transaction_id' => 'L00000-G00F00-S00000',
//  					'submit_datetime' => '',
//  					'status' => 'new',
//  					'created_date' => '2016',
//  					'created_by' => 'AC',
//  					'updated_date' => '2016',
//  					'updated_by' => 'AC'
//  					),
//  			//format array
//  			array(
//  					'%s', //transaction_id
//  					'%s', //account_id
//  					'%s', //market_id
//  					'%s', //year_start_date
//  					'%s', //game_period_id
//  					'%s', //buy_or_sell
//  					'%d', //allowances_buy_amt
//  					'%f', //allowances_buy_price
//  					'%d', //allowances_sell_amt
//  					'%f', //allowances_sell_price
//  					'%s', //accept_partial
//  					'%s', //original_transaction_id
//  					'%s', //submit_datetime
//  					'%s', //status
//  					'%s', //created_date
//  					'%s', //created_by
//  					'%s', //updated_date
//  					'%s' //updated_by
//  					));
//  }

//  function show_form() {
// 	echo <<<A_NAME
// 	<form method="post">
// 	    Enter number of allowances: <br>
// 	    <input type="number" name="permits" min="0">
// 	    <br>Enter price: <br>
// 	    <input type="number" name="price" min="0" max="">
// 	    <br><input type="submit" name="submit-buy" value="Buy allowances">
// 	</form>
// A_NAME;
// 	if (isset($_POST["submit-buy"]) ) {
// 		confirm_order();
// 		// echo "<form method='post'>";
// 		// echo "	Confirm order <br>";
// 		// echo "	Do you want to buy " . $_POST["permits"] . " allowances at $" . $_POST["price"] . "?<br>";
// 		// echo "  <input type='submit' name='confirm-buy' value='OK'>";
// 		// echo "  <input type='submit' name='cancel-buy' value='Cancel'>";
// 		// echo "</form>";

// 		// if (isset($_POST["confirm-buy"])) {
// 		// 	echo "Confirmed!";
// 		// } elseif (isset($_POST["cancel-buy"])) {
// 		// 	// show_form();
// 		// 	echo "Cancelled!";
// 	}
//  }
//  // add_shortcode('buy_allowances', 'show_form');
//  add_action('init', 'cookie');
 ?>