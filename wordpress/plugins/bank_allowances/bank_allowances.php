<?php
/* Plugin Name: Bank_Allowances
 Description: 4.8.4.5
 Author: Albert Hu
 Version: 1.0
 */

function bank_allowance() {
	ob_start();
		if (isset($_POST['confirm'])) {
			response_4845();
		} else {
		  	$correct_user = validate_user();
			if ($correct_user) {
				show_current_allowances();
			} else {
			  	if (isset($_POST['validated_id'])) {
					echo "Sorry, but you may only view your own profile";
				}
		  	}
		}	
	return ob_get_clean();
}

function show_current_allowances() {
	$ce_db = access_db();
	$tbl = 'carbonexch_period_summary';
	$curr_data = $_COOKIE['ce_info'];
	$pieces = explode("|" , $curr_data);
	$account_id = $pieces[0];
	$account_name = $pieces[1];
	$game_id = $pieces[2];
	$period_id = $pieces[3];
	$company_id = $pieces[4];
	$plant_id = $pieces[5];
	$game_start_date = $pieces[6];
	$game_period_id = $pieces[7];

    $check_query = "SELECT * FROM ce_carbonexch_scenario_play WHERE account_id = $account_id AND game_period_id = $period_id";
    $check = $ce_db->get_row($check_query, ARRAY_A);
   	if (!empty($check)) {
   		echo "You may not bank allowances because you've already started running scenarios.  Please run all the scenarios you wish to run <a href = 'http://54.183.29.82/wordpress/index.php/2016/07/09/play-scenario-on-carbon-trading-4-8-3/'>here</a> and then choose a scenario to stick with <a href = 'http://54.183.29.82/wordpress/index.php/2016/07/09/select-scenario-for-carbon-trading-4-8-4/'>here</a>";
   	} else {
	  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND game_period_id = $period_id";
	  	$result = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);
	  	$total_number_of_allowances = $result['initial_allowns_can_be_used'];

	  	$current = 0;
	  	if (isset($_POST['submit_bank_allowance'])) {
	  		$current = $_POST['submit_bank_allowance'];
	  	}

	  	echo <<<HTML

	  	<h5 style = "display: inline-block">Current amount of allowances that can be used:</h2>
	  	<span style = "float: right">$total_number_of_allowances</span>
	  	<br>
	  	<form method = "post">
	  	Please enter the number of allowances you would like to bank for the next period (max = $total_number_of_allowances):
	  	<br>
		<br>
	  	<input type = "number" name = "allowances_to_bank" min = "0" max = $total_number_of_allowances value = $current>
	  	<input type = "hidden" name = "validated_id" value = "true">
	  	<input type = "submit" name = "submit_bank_allowance" value = "Submit Bank Request">
	  	</form>
HTML;

		if (isset($_POST['submit_bank_allowance'])) {
			display_confirmation();

		}
	}
}

function display_confirmation() {
	$allowances_to_bank = $_POST['allowances_to_bank'];
	echo <<<HTML
	<br>
	<br>
	<form method = "post">
	Are you sure you want to bank $allowances_to_bank allowances?
	<input type = "hidden" name = "allowances_to_bank" value = $allowances_to_bank>
	<input type = "submit" name = "confirm" value = "Confirm">
	<input type = "submit" name = "cancel" value = "Cancel">
	</form>
HTML;

}
function response_4845() {
	$allowances_to_bank = $_POST['allowances_to_bank'];
	$ce_db = access_db();
	$curr_data = $_COOKIE['ce_info'];
	$pieces = explode("|" , $curr_data);
	$account_id = $pieces[0];
	$account_name = $pieces[1];
	$game_id = $pieces[2];
	$period_id = $pieces[3];
	$company_id = $pieces[4];
	$plant_id = $pieces[5];
	$game_start_date = $pieces[6];
	$game_period_id = $pieces[7];

  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND game_period_id = $period_id";
  	$result = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);
  	$total_number_of_allowances = $result['initial_allowns_can_be_used'];
  	$previous_banked_allowances = $result['allowances_in_bank'];

  	$new_total_number_of_allowances = $total_number_of_allowances - $allowances_to_bank;
  	$new_banked_allowances = $previous_banked_allowances + $allowances_to_bank;

  	$sql_where = array('account_id' => $account_id, 'game_period_id' => $period_id);
  	$sql_data = array('allowances_in_bank' => $new_banked_allowances, 'initial_allowns_can_be_used' => $new_total_number_of_allowances);
	$update_db = $ce_db -> update('ce_' . $tbl, $sql_data, $sql_where);

	if ($update_db) {
	  	echo "Successfully banked $allowances_to_bank allowances.  The number of allowances banked is now $new_banked_allowances and the number of allowances that you can use is $new_total_number_of_allowances.";
	}
}

add_shortcode('bank', 'bank_allowance');

?>