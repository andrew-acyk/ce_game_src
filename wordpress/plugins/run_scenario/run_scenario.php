<?php
/* Plugin Name: Run_Scenario
 Description: 4.8.3
 Author: Albert Hu
 Version: 1.0
 */
 
  

function run_scenario() {
	ob_start();
  	//run this line of code iff somebody has submitted a scenario
  	if (isset($_POST['enter'])) {
		response(); 
	} else {
	  $correct_user = validate_user();
	  if ($correct_user) {
		  $curr_data = display_curr_data();
		  choose_scenario($curr_data);
	  } else {
		if (isset($_POST['validated_id'])) {
		  echo "Sorry, but you may only view your own profile";
			  }
		  }
	  }
	  return ob_get_clean();
}

//returns true if the userID is the same as the userID stored in the cookie.
function validate_user() {
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

  	$post_action = esc_url($_SERVER['REQUEST_URI']);

	if (isset($_POST['validated_id']) && $_POST['validated_id'] == 'true') {
		$_POST['validated_id'] = 'true';
		return true;
  	} else if (isset($_POST['input'])) {
  		if ($_POST['input'] == $account_id) {
			$_POST['validated_id'] = 'true';
			return true;
		} else {
			$_POST['validated_id'] = 'false';
		  		echo <<<HTML
		<p>Please enter the account ID with which you wish to play scenario:</p>
		<h4>Hint: the accountID is 3</h4>
			<form name = "validation" method = "post" action = $post_action>
				<input type = "text" name = "input">
				<input type = "submit" name = "identity" value = "Enter">
			</form>
			<br><br><br>
HTML;
			return false;
		}
	} else {
		echo <<<HTML
		<p>Please enter the account ID with which you wish to play scenario:</p>
		<h4>Hint: the accountID is 3</h4>
			<form name = "validation" method = "post" action = $post_action>
				<input type = "text" name = "input">
				<input type = "submit" name = "identity" value = "Enter">
			</form>
			<br><br><br>
HTML;
		
	}
}


//goes into database and pulls out the current record for the company.
//uses 'do_nothing' because that is where the original data is
//returns an array with "period_start_emissions_ton" , "period_end_emissions_ton" , "allowances_allocated" , "period_end_emissions_cap" as its keys.  the values stored at these indices are the values pulled from database.
function display_curr_data() {
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

	
  	$ce_db = access_db();
    $tbl = 'carbonexch_period_summary';

  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$result = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);


  	$period_start_emissions_ton = $result['period_start_emissions_ton'];
	$period_end_emissions_ton = $result['period_end_emissions_ton'];
	$allowances_allocated = $result['allowances_allocated'];
	$period_end_emissions_cap = $result['period_end_emissions_cap'];
	$game_start_date = $result['game_start_date'];
	$plant_id = $result['plant_id'];
	$period_id = $result['game_period_id'];
  	echo <<<HTML
  	<div class="carbon-table-wrapper" style = "width: 100%">
		<h3 class = "profile-header"> Current Information </h3>
		<div class = "profile-row">Period Start Emissions Level:<span style = "float: right;">$period_start_emissions_ton</span></div>
		<div class = "profile-row">Period End Emissions Level:<span style = "float: right;">$period_end_emissions_ton</span></div>
		<div class = "profile-row">Period End Emissions Cap:<span style = "float: right;">$period_end_emissions_cap</span></div>
		<div class = "profile-row">Allowances Allocated:<span style = "float: right;">$allowances_allocated</span></div>
		<div class = "profile-row">Plant ID:<span style = "float: right;">$plant_id</span></div>
		<div class = "profile-row">Period ID:<span style = "float: right;">$period_id</span></div>
		<div class = "profile-row">Game Start Date:<span style = "float: right;">$game_start_date</span></div>
	</div>


HTML;

	$return_vals = array('period_start_emissions_ton' => $period_start_emissions_ton, 
		'period_end_emissions_ton' => $period_end_emissions_ton, 'allowances_allocated' => $allowances_allocated, 
		'period_end_emissions_cap' => $period_end_emissions_cap, 'game_start_date' => $game_start_date, 'plant_id' => $plant_id, 
		'period_id' => $period_id);

	return $return_vals;
}

function choose_scenario($curr_data) {
	//given game period id, account id, year start date.
  	$period_start_emissions_ton = $curr_data['period_start_emissions_ton'];
	$period_end_emissions_ton = $curr_data['period_end_emissions_ton'];
	$allowances_allocated = $curr_data['allowances_allocated'];
	$period_end_emissions_cap = $curr_data['period_end_emissions_cap'];
	$game_start_date = $curr_data['game_start_date'];
	$plant_id = $curr_data['plant_id'];
	$period_id = $curr_data['period_id'];


	//This counts how many scenarios the user has run in the database for this game period.  Max is 6 scenarios.
	$ce_db = access_db();
	$tbl = 'carbonexch_scenario_play';

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

	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$result = $ce_db->get_results(sprintf($query, $ce_db->prefix, $tbl), OBJECT);

  	$number_of_results = count($result);
  	$scenarios_left_to_run = 6 - $number_of_results;
  	$scenario_name = "scenario";

  	if ($scenarios_left_to_run > 1) {
  		$scenario_name = "scenarios";
  	}

  	echo "<br>";
  	echo "You are only allowed to run six scenarios per game period, and you have run $number_of_results so far.";
  	echo "<br>";
  	echo "To see which scenarios you have run so far, please go to the <a href = 'http://54.183.29.82/wordpress/index.php/2016/07/09/select-scenario-for-carbon-trading-4-8-4/'>Select Scenario screen.</a>";
  	echo "<br>";
  	echo "You have $scenarios_left_to_run $scenario_name left to run.";

  	if ($scenarios_left_to_run == 0) {
	    echo "<br>";

  		echo "You are only allowed to run six scenarios per game period, and you have already run all six.";
	    echo "<br>";
  		echo "Please go <a href = 'http://54.183.29.82/wordpress/index.php/2016/07/09/select-scenario-for-carbon-trading-4-8-4/'>here</a> to choose which scenario to run for this game period.";
  		echo "<br>";
  	}
  
  	else {


		$excess_emissions = $period_end_emissions_cap - $period_start_emissions_ton;
		$message;

		if ($excess_emissions == 0) {
			$message = 'You meet the emissions cap.';
		  	display_scenario_choices_no_seventh_option($message);
		} else if ($excess_emissions > 0) {
			$message = 'You are under the emissions cap.';
		  	display_scenario_choices_with_seventh_option($message);
		} else {
			$message = 'You are over the emissions cap.';
		  	display_scenario_choices_no_seventh_option($message);
		}
	}

}

function display_scenario_choices_with_seventh_option($message) {
	echo <<<HTML
	<br><br>
	<p>$message</p>
	<h3> Choose what you want to do: </h3>
	<form method = "post">
		<input type = "radio" name = "scenario_to_run" value = "choice1">Do nothing.  You will have to pay penalty costs if emissions are over the cap.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice2">Keep emissions level and perform abatement to cover the excess.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice3">Keep emissions level and buy allowances only.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice4">Keep emissions level, perform abatement and buy allowances simultaneously to cover the excess.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice5">Reduce emissions (production units) to meet cap.  This constitutes you reducing the amount of units you produce.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice6">Perform abatement with higher target level and sell any extra allowances.<br>
  		<input type = "radio" name = "scenario_to_run" value = "choice7">Sell all excess allowances.<br>
		<input type = "hidden" name = "validated_id" value = "true">
		<br> <br>
		<input type = "submit" name = "enter" value = "Run Scenario">
	</form>
	<br>
	</div>
HTML;
}

function display_scenario_choices_no_seventh_option($message) {
	echo <<<HTML
	<br><br>
	<p>$message</p>
	<h3> Choose what you want to do: </h3>
	<form method = "post">
		<input type = "radio" name = "scenario_to_run" value = "choice1">Do nothing.  You will have to pay penalty costs if emissions are over the cap.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice2">Keep emissions level and perform abatement to cover the excess.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice3">Keep emissions level and buy allowances only.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice4">Keep emissions level, perform abatement and buy allowances simultaneously to cover the excess.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice5">Reduce emissions (production units) to meet cap.  This constitutes you reducing the amount of units you produce.<br>
		<input type = "radio" name = "scenario_to_run" value = "choice6">Perform abatement with higher target level and sell any extra allowances.<br>
  
		<input type = "hidden" name = "validated_id" value = "true">
		<br> <br>
		<input type = "submit" name = "enter" value = "Run Scenario">
	</form>
	<br>
	</div>
HTML;
}


/*<-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <------------------------------------------------Response code------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
*/


function response() {
  	$ce_db = access_db();
  	$tbl = 'carbonexch_scenario_play';
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
	if (!isset($_POST['scenario_to_run']) && isset($_POST['enter'])) {
  		response_empty();
	} else {
		switch ($_POST['scenario_to_run']) {
			case 'choice1':
				response1($ce_db, $tbl);
				break;
			case 'choice2':
				response2($ce_db, $tbl);
				break;
			case 'choice3':
				response3($ce_db, $tbl);
				break;
			case 'choice4':
				response4($ce_db, $tbl);
				break;
			case 'choice5':
				response5($ce_db, $tbl);
				break;
			case 'choice6':
				response6($ce_db, $tbl);
				break;
			case 'choice7':
		  		response7($ce_db, $tbl);
		  		break;
		} 
  	}
}

function response_empty() {
	echo "You didn't choose a scenario to run.  Please choose one of the choices.";
	return_prev_screen();
}




function response1($ce_db, $tbl) {
	$ce_db = $ce_db;
	$tbl = $tbl;
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


  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND scenario_choice = 'do_nothing' AND game_period_id = '$game_period_id'";
  	$check = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);



  	$summary_query = "SELECT * from ce_carbonexch_period_summary WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$summary = $ce_db->get_row($summary_query, ARRAY_A, 0);


  	if (!is_null($check)) {
  		echo "You already ran this scenario and cannot run it again.  You can run a different scenario.";
  	} else {
  		$penalty_query = "SELECT * FROM ce_configuration_parameter WHERE parameter_name = 'ce_penalty_excd_emiss_cap_perc'";
  		$penalty = $ce_db->get_row($penalty_query, ARRAY_A)['parameter_value'] / 100;

  		$period_start_prod_unit = $summary['period_start_prod_unit'];
  		$period_start_emissions_ton = $summary['period_start_emissions_ton'];
  		$period_end_prod_unit = $summary['period_end_prod_unit'];
  		$period_end_emissions_ton = $summary['period_end_emissions_ton'];
  		$allowances_allocated = $summary['allowances_allocated'];
  		$period_end_emissions_cap = $summary['period_end_emissions_cap'];
  		$allowances_in_bank = $summary['allowances_in_bank'];
  		$initial_allowns_can_be_used = $summary['initial_allowns_can_be_used'];

  		$period_scenario_id = generate_period_scenario_id();


  		$emissions_over_allowns_can_be_used = $period_start_emissions_ton - $period_end_emissions_cap;
  		$costs_penalty = $emissions_over_allowns_can_be_used * $penalty * $carbon_price_last_perd;
  		$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
  		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;
  		$total_costs = $costs_allocated_allowances + $costs_penalty + $costs_allowances_in_bank;
  		$total_costs_per_unit = $total_costs / $period_end_prod_unit;
  		$data_to_enter = array(
  		'account_id' => $account_id,
  		'plant_id' => $plant_id,
  		'game_id' => $game_id,
  		'game_start_date' => $game_start_date,
  		'game_period_id' => $game_period_id,
  		'period_scenario_id' => $period_scenario_id,

  		'scenario_choice' => 'do_nothing',
  		'period_start_emissions_ton' => $period_start_emissions_ton,
  		'period_end_emissions_ton' => $period_end_emissions_ton,


  		'period_start_prod_unit' => $period_start_prod_unit,
  		'period_end_prod_unit' => $period_end_prod_unit,

  		'allowances_allocated' => $allowances_allocated,
  		'period_end_emissions_cap' => $period_end_emissions_cap,
  		'allowances_in_bank' => $allowances_in_bank,
  		'initial_allowns_can_be_used' => $initial_allowns_can_be_used,
  		'current_carbon_price' => $carbon_price_last_perd,

  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => 0,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => 0,
  		'costs_trading' => 0,
  		'costs_abatement' => 0,
  		'costs_penalty' => $costs_penalty,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs,
  		'total_costs_per_unit' => $total_costs_per_unit);

  		$insert = $ce_db -> insert('ce_carbonexch_scenario_play', $data_to_enter);
  		if ($insert) {
  			echo "Inserted scenario 'do_nothing' into database.";
  		}
  	}
  	return_prev_screen();
}

function response2($ce_db, $tbl) {
	$ce_db = $ce_db;
	$tbl = $tbl;
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



  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND scenario_choice = 'fix_prod_lvl_perform_upgd' AND game_period_id = '$game_period_id'";
  	$check = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);	

  	$summary_query = "SELECT * from ce_carbonexch_period_summary WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$summary = $ce_db->get_row($summary_query, ARRAY_A);

  	if (!is_null($check)) {
  		echo "You already ran this scenario and cannot run it again.  You can run a different scenario.";
  	} else {
  		$period_start_prod_unit = $summary['period_start_prod_unit'];
  		$period_start_emissions_ton = $summary['period_start_emissions_ton'];
  		$period_end_prod_unit = $summary['period_end_prod_unit'];
  		$period_end_emissions_ton = $summary['period_end_emissions_ton'];
  		$allowances_allocated = $summary['allowances_allocated'];
  		$period_end_emissions_cap = $summary['period_end_emissions_cap'];
  		$allowances_in_bank = $summary['allowances_in_bank'];
  		$initial_allowns_can_be_used = $summary['initial_allowns_can_be_used'];

  		$period_scenario_id = generate_period_scenario_id();

  		$emissions_over_allowns_can_be_used = $period_start_emissions_ton - $period_end_emissions_cap;
  		$costs_abatement = get_abatement_cost_given_allowances($period_start_emissions_ton, 
  								$period_end_emissions_cap, $plant_id);
    	$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
  		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;
    	$total_costs = $costs_allocated_allowances + $costs_abatement + $costs_allowances_in_bank;

  		$total_costs_per_unit = $total_costs / $period_end_prod_unit;


  		$data_to_enter = array(
  		'account_id' => $account_id,
  		'plant_id' => $plant_id,
  		'game_id' => $game_id,
  		'game_start_date' => $game_start_date,
  		'game_period_id' => $game_period_id,
  		'period_scenario_id' => $period_scenario_id,

  		'scenario_choice' => 'fix_prod_lvl_perform_upgd',
  		'period_start_emissions_ton' => $period_start_emissions_ton,
  		'period_end_emissions_ton' => $period_end_emissions_ton,


  		'period_start_prod_unit' => $period_start_prod_unit,
  		'period_end_prod_unit' => $period_end_prod_unit,

  		'allowances_allocated' => $allowances_allocated,
  		'period_end_emissions_cap' => $period_end_emissions_cap,
  		'allowances_in_bank' => $allowances_in_bank,
  		'initial_allowns_can_be_used' => $initial_allowns_can_be_used,
  		'current_carbon_price' => $carbon_price_last_perd,

  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => 0,
  		'costs_trading' => 0,
  		'costs_abatement' => $costs_abatement,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
   		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs,
  		'total_costs_per_unit' => $total_costs_per_unit);

  		$insert = $ce_db -> insert('ce_carbonexch_scenario_play', $data_to_enter);
  		if ($insert) {
  			echo "Inserted this into database.";

  		}
	}
	return_prev_screen();
}


function response3($ce_db, $tbl) {
	$ce_db = $ce_db;
	$tbl = $tbl;
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

  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND scenario_choice = 'fix_emiss_lvl_buy_allowns' AND game_period_id = '$game_period_id'";
  	$check = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);

  	$summary_query = "SELECT * from ce_carbonexch_period_summary WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$summary = $ce_db->get_row($summary_query, ARRAY_A);	

  	if (!is_null($check)) {
  		echo "You already ran this scenario and cannot run it again.  You can run a different scenario.";
  	} else {
		$period_start_prod_unit = $summary['period_start_prod_unit'];
  		$period_start_emissions_ton = $summary['period_start_emissions_ton'];
  		$period_end_prod_unit = $summary['period_end_prod_unit'];
  		$period_end_emissions_ton = $summary['period_end_emissions_ton'];
  		$allowances_allocated = $summary['allowances_allocated'];
  		$period_end_emissions_cap = $summary['period_end_emissions_cap'];
  		$allowances_in_bank = $summary['allowances_in_bank'];
  		$initial_allowns_can_be_used = $summary['initial_allowns_can_be_used'];

  		$period_scenario_id = generate_period_scenario_id();


  		$emissions_over_allowns_can_be_used = $period_start_emissions_ton - $period_end_emissions_cap;

  		$costs_trading = $emissions_over_allowns_can_be_used * $carbon_price_last_perd;
    	$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
   		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;
    	$total_costs = $costs_allocated_allowances + $costs_trading + $costs_allowances_in_bank;

  		$total_costs_per_unit = $total_costs / $period_end_prod_unit;

  		$data_to_enter = array(
  		'account_id' => $account_id,
  		'plant_id' => $plant_id,
  		'game_id' => $game_id,
  		'game_start_date' => $game_start_date,
  		'game_period_id' => $game_period_id,
  		'period_scenario_id' => $period_scenario_id,

  		'scenario_choice' => 'fix_emiss_lvl_buy_allowns',
  		'period_start_emissions_ton' => $period_start_emissions_ton,
  		'period_end_emissions_ton' => $period_end_emissions_ton,


  		'period_start_prod_unit' => $period_start_prod_unit,
  		'period_end_prod_unit' => $period_end_prod_unit,

  		'allowances_allocated' => $allowances_allocated,
  		'period_end_emissions_cap' => $period_end_emissions_cap,
  		'allowances_in_bank' => $allowances_in_bank,
  		'initial_allowns_can_be_used' => $initial_allowns_can_be_used,
  		'current_carbon_price' => $carbon_price_last_perd,


  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => 0,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => $emissions_over_allowns_can_be_used,
  		'costs_trading' => $costs_trading,
  		'costs_abatement' => 0,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs,
  		'total_costs_per_unit' => $total_costs_per_unit);

  		$insert = $ce_db -> insert('ce_carbonexch_scenario_play', $data_to_enter);
  		if ($insert) {
  			echo "Inserted this into database.";
  		}
	}
	return_prev_screen();
}

function response4($ce_db, $tbl) {
	$ce_db = $ce_db;
	$tbl = $tbl;
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

  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND scenario_choice = 'fix_prod_buy_allowns_perf_upgd' AND game_period_id = '$game_period_id'";
  	$check = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);

  	$summary_query = "SELECT * from ce_carbonexch_period_summary WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$summary = $ce_db->get_row($summary_query, ARRAY_A);		
  	if (!is_null($check)) {
  		echo "You already ran this scenario and cannot run it again.  You can run a different scenario.";
  	} else {
		$period_start_prod_unit = $summary['period_start_prod_unit'];
  		$period_start_emissions_ton = $summary['period_start_emissions_ton'];
  		$period_end_prod_unit = $summary['period_end_prod_unit'];
  		$period_end_emissions_ton = $summary['period_end_emissions_ton'];
  		$allowances_allocated = $summary['allowances_allocated'];
  		$period_end_emissions_cap = $summary['period_end_emissions_cap'];
  		$allowances_in_bank = $summary['allowances_in_bank'];
  		$initial_allowns_can_be_used = $summary['initial_allowns_can_be_used'];

  		$period_scenario_id = generate_period_scenario_id();

  		$emissions_over_allowns_can_be_used = $period_start_emissions_ton - $period_end_emissions_cap;
  		$allowns_lvl_corr_to_cur_price = get_allowances_marginal_abatement($carbon_price_last_perd, $plant_id);

    	$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
   		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;

  		$allowns_needed_fr_trading = 0;
  		$allowances_needed_from_abatement = 0;
  		$costs_trading = 0;
  		$costs_abatement = 0;

  		if ($emissions_over_allowns_can_be_used > 0) {
  			if ($allowns_lvl_corr_to_cur_price < $period_start_emissions_ton) {
  				//cheaper to buy 100% of allowances needed to meet cap
  				$allowns_needed_fr_trading = $emissions_over_allowns_can_be_used;
  				$costs_trading = $allowns_needed_fr_trading * $carbon_price_last_perd;


  			} else if ($allowns_lvl_corr_to_cur_price > $period_end_emissions_cap) {
  				//cheaper to abate 100% of allowances needed to meet cap
  				$allowances_needed_from_abatement = $emissions_over_allowns_can_be_used;
  				$costs_abatement =  get_abatement_cost_given_allowances($period_start_emissions_ton, 
  								$period_end_emissions_cap, $plant_id);

  			} else {
  				//somewhere in the middle, need to abate AND buy allowances

  				//perform abatement to $allowns_lvl_corr_to_cur_price;

  				$allowances_needed_from_abatement = $allowns_lvl_corr_to_cur_price - $period_start_emissions_ton;
  				$costs_abatement = get_abatement_cost_given_allowances($period_start_emissions_ton, 
  								$allowns_lvl_corr_to_cur_price, $plant_id);

  				//buy the remaining allowances needed
  				$allowns_needed_fr_trading = $period_end_emissions_cap - $allowns_lvl_corr_to_cur_price;
  				$costs_trading = $allowns_needed_fr_trading * $carbon_price_last_perd;

  			}
  		}

   		$total_costs = $costs_allocated_allowances + $costs_trading + $costs_abatement + $costs_allowances_in_bank;

  		$total_costs_per_unit = $total_costs / $period_end_prod_unit;

  		$data_to_enter = array(
  		'account_id' => $account_id,
  		'plant_id' => $plant_id,
  		'game_id' => $game_id,
  		'game_start_date' => $game_start_date,
  		'game_period_id' => $game_period_id,
  		'period_scenario_id' => $period_scenario_id,

  		'scenario_choice' => 'fix_prod_buy_allowns_perf_upgd',
  		'period_start_emissions_ton' => $period_start_emissions_ton,
  		'period_end_emissions_ton' => $period_end_emissions_ton,


  		'period_start_prod_unit' => $period_start_prod_unit,
  		'period_end_prod_unit' => $period_end_prod_unit,

  		'allowances_allocated' => $allowances_allocated,
  		'period_end_emissions_cap' => $period_end_emissions_cap,
  		'allowances_in_bank' => $allowances_in_bank,
  		'initial_allowns_can_be_used' => $initial_allowns_can_be_used,
  		'current_carbon_price' => $carbon_price_last_perd,
  		'allowns_lvl_corr_to_cur_price' => $allowns_lvl_corr_to_cur_price,


  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => $allowances_needed_from_abatement,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => $allowns_needed_fr_trading,
  		'costs_trading' => $costs_trading,
  		'costs_abatement' => $costs_abatement,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs,
  		'total_costs_per_unit' => $total_costs_per_unit);


  		$insert = $ce_db -> insert('ce_carbonexch_scenario_play', $data_to_enter);
  		if ($insert) {
  			echo "Inserted this into database.";
  		}
	}
	return_prev_screen();
}

function response5($ce_db, $tbl) {
	$ce_db = $ce_db;
	$tbl = $tbl;
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

  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND scenario_choice = 'decrease_emiss_only' AND game_period_id = '$game_period_id'";
  	$check = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);	

  	$summary_query = "SELECT * from ce_carbonexch_period_summary WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$summary = $ce_db->get_row($summary_query, ARRAY_A);

  	if (!is_null($check)) {
  		echo "You already ran this scenario and cannot run it again.  You can run a different scenario.";
  	} else {
		$period_start_prod_unit = $summary['period_start_prod_unit'];
  		$period_start_emissions_ton = $summary['period_start_emissions_ton'];
  		$period_end_prod_unit = $summary['period_end_prod_unit'];
  		$period_end_emissions_ton = $summary['period_end_emissions_ton'];
  		$allowances_allocated = $summary['allowances_allocated'];
  		$period_end_emissions_cap = $summary['period_end_emissions_cap'];
  		$allowances_in_bank = $summary['allowances_in_bank'];
  		$initial_allowns_can_be_used = $summary['initial_allowns_can_be_used'];

  		$emissions_over_allowns_can_be_used = $period_start_emissions_ton - $period_end_emissions_cap;
   		$period_scenario_id = generate_period_scenario_id();


   		$new_emissions_level = $period_end_emissions_cap;
   		$new_prod_units = get_units_given_emissions($new_emissions_level, $plant_id);
   		$allowns_needed_fr_trading = 0;
   		$costs_trading = 0;

    	$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
   		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;



   		$total_costs = $costs_trading + $costs_allowances_in_bank + $costs_allocated_allowances;


  		$total_costs_per_unit = $total_costs / $new_prod_units;

  		$data_to_enter = array(
  		'account_id' => $account_id,
  		'plant_id' => $plant_id,
  		'game_id' => $game_id,
  		'game_start_date' => $game_start_date,
  		'game_period_id' => $game_period_id,
  		'period_scenario_id' => $period_scenario_id,

  		'scenario_choice' => 'decrease_emiss_only',
  		'period_start_emissions_ton' => $period_start_emissions_ton,
  		'period_end_emissions_ton' => $new_emissions_level,


  		'period_start_prod_unit' => $period_start_prod_unit,
  		'period_end_prod_unit' => $new_prod_units,

  		'allowances_allocated' => $allowances_allocated,
  		'period_end_emissions_cap' => $period_end_emissions_cap,
  		'allowances_in_bank' => $allowances_in_bank,
  		'initial_allowns_can_be_used' => $initial_allowns_can_be_used,
  		'current_carbon_price' => $carbon_price_last_perd,


  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => 0,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => 0,
  		'costs_trading' => 0,
  		'costs_abatement' => 0,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs,
  		'total_costs_per_unit' => $total_costs_per_unit);


  		$insert = $ce_db -> insert('ce_carbonexch_scenario_play', $data_to_enter);
  		if ($insert) {
  			echo "Inserted this into database.";
  		}
  	}
	return_prev_screen();
}


function response6($ce_db, $tbl) {
	if (isset($_POST['target_abatement_level'])&& empty($_POST['target_abatement_level'])) {
		echo "Please enter your new abatement level(can't be zero)";
	  	return_prev_screen();

	} else if (isset($_POST['target_abatement_level'])) {
		$ce_db = $ce_db;
		$tbl = $tbl;
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

   		$period_scenario_id = generate_period_scenario_id();

		$summary_query = "SELECT * from ce_carbonexch_period_summary WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  		$summary = $ce_db->get_row($summary_query, ARRAY_A);

		$period_start_prod_unit = $summary['period_start_prod_unit'];
  		$period_start_emissions_ton = $summary['period_start_emissions_ton'];
  		$period_end_prod_unit = $summary['period_end_prod_unit'];
  		$period_end_emissions_ton = $summary['period_end_emissions_ton'];
  		$allowances_allocated = $summary['allowances_allocated'];
  		$period_end_emissions_cap = $summary['period_end_emissions_cap'];
  		$allowances_in_bank = $summary['allowances_in_bank'];
  		$initial_allowns_can_be_used = $summary['initial_allowns_can_be_used'];


		$target_abatement_level = $_POST['target_abatement_level'];
		$emissions_over_allowns_can_be_used = $period_start_emissions_ton - $period_end_emissions_cap;

		$allowances_needed_from_abatement = $target_abatement_level - $initial_allowns_can_be_used;

		$allowns_needed_fr_trading = $emissions_over_allowns_can_be_used - $allowances_needed_from_abatement;




		$costs_abatement = get_abatement_cost_given_allowances($target_abatement_level, $initial_allowns_can_be_used, $plant_id);
		$costs_trading = $allowns_needed_fr_trading * $carbon_price_last_perd;
    	$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
   		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;
   		$total_costs = $costs_abatement + $costs_trading + $costs_allocated_allowances + $costs_allowances_in_bank;

  		$total_costs_per_unit = $total_costs / $period_end_prod_unit;

		$data_to_enter = array(
  		'account_id' => $account_id,
  		'plant_id' => $plant_id,
  		'game_id' => $game_id,
  		'game_start_date' => $game_start_date,
  		'game_period_id' => $game_period_id,
  		'period_scenario_id' => $period_scenario_id,

  		'scenario_choice' => 'adj_tgt_sel_allowns_perf_upgd',
  		'period_start_emissions_ton' => $period_start_emissions_ton,
  		'period_end_emissions_ton' => $period_end_emissions_ton,


  		'period_start_prod_unit' => $period_start_prod_unit,
  		'period_end_prod_unit' => $period_end_prod_unit,

  		'allowances_allocated' => $allowances_allocated,
  		'period_end_emissions_cap' => $period_end_emissions_cap,
  		'allowances_in_bank' => $allowances_in_bank,
  		'initial_allowns_can_be_used' => $initial_allowns_can_be_used,
  		'current_carbon_price' => $carbon_price_last_perd,
  		'tgt_allowns_lvl_for_abatmt' => $target_abatement_level,


  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => $allowances_needed_from_abatement,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => $allowns_needed_fr_trading,
  		'costs_trading' => $costs_trading,
  		'costs_abatement' => $costs_abatement,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs,
  		'total_costs_per_unit' => $total_costs_per_unit);

  		$insert = $ce_db -> insert('ce_carbonexch_scenario_play', $data_to_enter);
  		if ($insert) {
  			echo "Inserted into database.";
  		}
  		return_prev_screen();
	} else {
		calculate_choice_six();
	}
}

function calculate_choice_six() {
	$ce_db = access_db();
    $tbl = 'carbonexch_period_summary';
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


  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id";
  	$result = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);
  	$period_start_emissions_ton = $result['period_start_emissions_ton'];
	$min = $period_start_emissions_ton + 1;

	echo<<<HTML
		<form name = "adjust_abatemt_lvl_sell_allowns_perform_upgde" method = "post">
			Enter a target level for the number of allowances (min $min):
			<br>
			<input type = "number" name = "target_abatement_level" min = $min>

			<input type = "hidden" name = "scenario" value = "run6">
			<input type = "hidden" name = "validated_id" value = "true">
			<input type = "hidden" name = "scenario_to_run" value = "choice6">
			<input type = "hidden" name = "enter" value = "run scenario">

			<input type = "submit" name = "run_scenario" value = "Submit">
		</form>
HTML;
}


function response7($ce_db, $tbl) {
	$ce_db = $ce_db;
	$tbl = $tbl;
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

  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND scenario_choice = 'sell_extra_allowns' AND game_period_id = '$game_period_id'";
  	$check = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, 0);	

  	$summary_query = "SELECT * from ce_carbonexch_period_summary WHERE account_id = $account_id AND game_period_id = '$game_period_id'";
  	$summary = $ce_db->get_row($summary_query, ARRAY_A);

  	if (!is_null($check)) {
  		echo "You already ran this scenario and cannot run it again.  You can run a different scenario.";
  	} else {
		$period_start_prod_unit = $summary['period_start_prod_unit'];
  		$period_start_emissions_ton = $summary['period_start_emissions_ton'];
  		$period_end_prod_unit = $summary['period_end_prod_unit'];
  		$period_end_emissions_ton = $summary['period_end_emissions_ton'];
  		$allowances_allocated = $summary['allowances_allocated'];
  		$period_end_emissions_cap = $summary['period_end_emissions_cap'];
  		$allowances_in_bank = $summary['allowances_in_bank'];
  		$initial_allowns_can_be_used = $summary['initial_allowns_can_be_used'];

  		$emissions_over_allowns_can_be_used = $period_start_emissions_ton - $period_end_emissions_cap;
   		$period_scenario_id = generate_period_scenario_id();



    	$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
   		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;

   		//$allowns_needed_fr_trading will be negative because we are selling allowances
  		$allowns_needed_fr_trading = $period_end_emissions_cap - $initial_allowns_can_be_used;
  		$allowances_needed_from_abatement = 0;
  		$costs_trading = $allowns_needed_fr_trading * $carbon_price_last_perd;
  		$costs_abatement = 0;
  		$period_end_emissions_ton = $period_end_emissions_cap;

  		$total_costs = $costs_allocated_allowances + $costs_allowances_in_bank + $costs_abatement + $costs_trading;

  		$total_costs_per_unit = $total_costs / $period_end_prod_unit;

  		$data_to_enter = array(
  		'account_id' => $account_id,
  		'plant_id' => $plant_id,
  		'game_id' => $game_id,
  		'game_start_date' => $game_start_date,
  		'game_period_id' => $game_period_id,
  		'period_scenario_id' => $period_scenario_id,

  		'scenario_choice' => 'sell_extra_allowns',
  		'period_start_emissions_ton' => $period_start_emissions_ton,
  		'period_end_emissions_ton' => $period_end_emissions_ton,


  		'period_start_prod_unit' => $period_start_prod_unit,
  		'period_end_prod_unit' => $period_end_prod_unit,

  		'allowances_allocated' => $allowances_allocated,
  		'period_end_emissions_cap' => $period_end_emissions_cap,
  		'allowances_in_bank' => $allowances_in_bank,
  		'initial_allowns_can_be_used' => $initial_allowns_can_be_used,
  		'current_carbon_price' => $carbon_price_last_perd,


  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => $allowances_needed_from_abatement,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => $allowns_needed_fr_trading,
  		'costs_trading' => $costs_trading,
  		'costs_abatement' => $costs_abatement,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs,
  		'total_costs_per_unit' => $total_costs_per_unit);


  		$insert = $ce_db -> insert('ce_carbonexch_scenario_play', $data_to_enter);
  		if ($insert) {
  			echo "Inserted this into database.";
  		}
  	}
	return_prev_screen();
}

function return_prev_screen() {
	echo <<<HTML
	<form name = "ok" method = "post">
	<input type = "hidden" name = "validated_id" value = "true">
	<input type = "submit" value = "Run another scenario">
	</form>
HTML;
}


function generate_period_scenario_id() {
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



	$tbl = 'ce_configuration_parameter';
	$ce_db = access_db();
	$query = 'SELECT * FROM `%s` WHERE parameter_name = "ce_scenario_next_seq_num"';
	$result = $ce_db->get_row(sprintf($query,$tbl), ARRAY_A, 0);
	$scenario_id = $result['parameter_value'];
	$new_scenario_id = $scenario_id + 1;
	$update_success = $ce_db -> update($tbl, array('parameter_value' => $new_scenario_id), array('parameter_name' => 'ce_scenario_next_seq_num'));

	$period_scenario_id = strval($period_id) . "-" . strval($scenario_id);
	return $period_scenario_id;
}


add_shortcode('scenario', 'run_scenario');

/*<-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <------------------------------------------------Package code-------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
  <-------------------------------------------------------------------------------------------------------------------->
*/

# takes in a number of production units and returns the number 
# of emissions the particular plant will produce.
function get_num_emissions($prod_units, $plant_id) {
	switch ($plant_id) {
		case 1:
			return 0.7 * pow($prod_units, 1.431);
			break;
		case 2:
			return 1.2 * pow($prod_units, 1.453);
			break;
		case 3:
			return 0.8 * pow($prod_units, 1.299);
			break;
		case 4:
			return pow($prod_units, 1.144);
			break;
		case 5:
			return 1.12 * pow($prod_units, 1.178);
			break;
		default: 
			return -1;
			break;
	}
}

# returns the number of units that would create the number of emissions entered in to the function
function get_units_given_emissions($emissions, $plant_id) {
	switch ($plant_id) {
		case 1:
			$exp = (1/1.431) * log($emissions / 0.7);
			return pow(2.7183, $exp);
			break;
		case 2:
			$exp = (1/1.178) * log($emissions / 1.12);
			return pow(2.7183, $exp);
			break;
		case 3:
			$exp = (1/1.144) * log($emissions);
			return pow(2.7183, $exp);
			break;
		case 4:
			$exp = (1/1.299) * log($emissions / 0.8);
			return pow(2.7183, $exp);
			break;
		case 5:
			$exp = (1/1.453) * log($emissions / 1.2);
			return pow(2.7183, $exp);
			break;
		default:
			return -1;
			break;
	}
}

# returns the cost of abatement for abating some number of allowances
function get_price_for_abatement_given_allowances($allowances, $plant_id) {
	switch ($plant_id) {
		case 1:
			return 0.5 * (pow($allowances, 0.29));
			break;
		case 2:
			return 2.15 * (pow($allowances, 0.184));
			break;
		case 3:
			return 1.15 * (pow($allowances, 0.22));
			break;
		case 4:
			return 1.05 * (pow($allowances, 0.24));
			break;
		case 5:
			return 0.75 * (pow($allowances, 0.23));
			break;
		default:
			return -1;
			break;
	}
}

# takes in a carbon price and returns the allowance level corresponding to current carbon price
function get_allowances_marginal_abatement($carbon_price, $plant_id) {
	switch ($plant_id) {
		case 1:
			$exp = (1 / 0.29) * log($carbon_price / 0.5);
			return pow(2.7183, $exp);
			break;
		case 2:
			$exp = (1 / 0.184) * log($carbon_price / 2.15);
			return pow(2.7183, $exp);
			break;
		case 3:
			$exp = (1 / 0.22) * log($carbon_price / 1.15);
			return pow(2.7183, $exp);
			break;
		case 4:
			$exp = (1 / 0.24) * log($carbon_price / 1.05);
			return pow(2.7183, $exp);
			break;
		case 5:
			$exp = (1 / 0.23) * log($carbon_price / 0.75);
			return pow(2.7183, $exp);
			break;
		default:
			return -1;
			break;
	}
}

# takes in target and initial allowances, and returns
# the cost of abatement from initial to target for
# a specific power plant
function get_abatement_cost_given_allowances($target, $init, $plant_id) {
	switch($plant_id) {
		case 1:
			$initial_cost = pow($init, 1.29);
			$target_cost = pow($target, 1.29);
			$diff = $target_cost - $initial_cost;
			return (0.5 / 1.29) * $diff;
		case 2:
			$initial_cost = pow($init, 1.184);
			$target_cost = pow($target, 1.184);
			$diff = $target_cost - $initial_cost;
			return (2.15 / 1.184) * $diff;
		case 3:
			$initial_cost = pow($init, 1.22);
			$target_cost = pow($target, 1.22);
			$diff = $target_cost - $initial_cost;
			return (1.15 / 1.22) * $diff;
		case 4:
			$initial_cost = pow($init, 1.24);
			$target_cost = pow($target, 1.24);
			$diff = $target_cost - $initial_cost;
			return (1.05 / 1.24) * $diff;
		case 5:
			$initial_cost = pow($init, 1.23);
			$target_cost = pow($target, 1.23);
			$diff = $target_cost - $initial_cost;
			return (0.75 / 1.23) * $diff;
		default:
			return -1;
			break;
	}
}

function get_allowances_given_total_abatement_cost($total_cost, $allowances, $plant_id) {
	switch ($plant_id) {
		case 1:
			$exp = (1 / 1.29) * log((1.29 / 0.5) * ($total_cost + ((0.5 / 1.29) * pow($allowances, 1.29))));
			return pow(2.7183, $exp);
			break;
		case 2:
			$exp = (1/1.184) * log((1.184 / 2.15) * ($total_cost + ((2.15 / 1.184) * pow($allowances, 1.184))));
			return pow(2.7183, $exp);
			break;
		case 3:
			$exp = (1/1.22) * log((1.22 / 1.15) * ($total_cost + ((1.15 / 1.22) * pow($allowances, 1.22))));
			return pow(2.7183, $exp);
			break;
		case 4:
			$exp = (1/1.24) * log((1.24 / 1.05) * ($total_cost + ((1.05 / 1.24) * pow($allowances, 1.24))));
			return pow(2.7183, $exp);
			break;
		case 5:
			$exp = (1/1.23) * log((1.23 / 0.75) * ($total_cost + ((0.75 / 1.23) * pow($allowances, 1.23))));
			return pow(2.7183, $exp);
			break;
		default:
			return -1;
			break;
	}
}
?>
