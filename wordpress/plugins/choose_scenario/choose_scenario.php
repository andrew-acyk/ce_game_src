<?php
/* Plugin Name: Choose_Scenario
 Description: 4.8.4
 Author: Albert Hu
 Version: 1.0
 */
function submit_scenario() {
	ob_start();
	if (isset($_POST['confirm'])) {
		mark_scenario_as_chosen();
	} else if (isset($_POST['submit'])) {
		$number_of_choices = display_curr_data_484();
	} else {
	  	$correct_user = validate_user();
		if ($correct_user) {
			$number_of_choices = display_curr_data_484();
		} else {
		  	if (isset($_POST['validated_id'])) {
				echo "Sorry, but you may only view your own profile";
			}
	  	}
	}
	return ob_get_clean();
}



function display_curr_data_484() {
  	$ce_db = access_db();
    $tbl = 'carbonexch_scenario_play';

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

  	$result = $ce_db->get_results(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A);
    //returns the result as an object, get each row by indexing into $result
    $number_of_results = count($result);

    $display_values = array("placeholder", "scenario_choice", "period_start_prod_unit", "period_end_prod_unit", "period_start_emissions_ton", "period_end_emissions_ton", "allowances_allocated", "initial_allowns_can_be_used", 
    	"tgt_allowns_lvl_for_abatmt", "period_end_emissions_cap",  
    	"emissns_covered_by_abatmt", "emissns_covered_by_allowns", "allowns_needed_fr_trading", "costs_trading",
    	"costs_abatement", "costs_allocated_allowances", "costs_allowances_in_bank", "costs_penalty" , "total_costs", "total_costs_per_unit");
  
  	$col_names = array("Choice", "Period Scenario Description", "Start Production Units", "End Production Units", "Period Start Emission Level", "Period End Emission Level",
  		"Allowances Allocated", "Allowances That Can be Used", "Target Allowances Level", "Emission Cap", "Emissions Covered By Abatement", "Emissions Covered By Allowances", "Allowances Bought (+) / Sold (-) From Trading", "Trading Costs", 
  		"Abatement Costs", "Allocated Allowances Costs", "Allowances in Bank Costs", "Penalty", "Total Costs", "Total Costs Per Unit");
  
  	$scenario_choices = array("do_nothing", "fix_prod_lvl_perform_upgd", "fix_emiss_lvl_buy_allowns", "fix_prod_buy_allowns_perf_upgd", 
							"decrease_emiss_only", "adj_tgt_sel_allowns_perf_upgd", "sell_extra_allowns");
					   
  	echo <<<HTML
  		<div class = "carbon-table-wrapper" style = "width: 100%">
		<h3 class = "profile-header"> Current Period Information </h3>
		<div class = "profile-row">Plant ID:<span style = "float: right;">$plant_id</span></div>
		<div class = "profile-row">Game Period Start Date:<span style = "float: right;">$game_start_date</span></div>
		</div>
HTML;

	
	if ($number_of_results == 0) {
		echo "You haven't run any scenarios yet for this period.  Please select some scenarios to run in the <a href = 'http://54.183.29.82/wordpress/index.php/2016/07/09/play-scenario-on-carbon-trading-4-8-3/'>Play Scenario on Carbon Trading section</a> of our website.";
	} else {
		echo "<div class = 'carbon-table-wrapper'>";
		echo "<h3 class = 'profile-header'>Scenarios That Have Been Run</h3>";
		echo "<div class = 'carbon-table'>";
		$first_row = 1;
		for ($counter = 0; $counter < count($display_values); $counter++) {
			echo  "<div class = 'profile-row'>";
			$row = $col_names[$counter];
		  	echo "<div class = 'profile-row-data'>$row</div>";
		  	for ($ctr = 0; $ctr < $number_of_results; $ctr++) {
		  		if ($first_row == 1) {
		  			$val = $ctr + 1;
		  			echo "<div class = 'profile-row-data'>$val</div>";
		  		} else {
			  		$curr = $result[$ctr];
			  		$display = $display_values[$counter];
			  		$get = $curr[$display];
			  		if (in_array($get, $scenario_choices)) {
			  			$get = replace_scenario_name($get);
			  		}
			  		echo "<div class = 'profile-row-data'>$get</div>";
			  	}

		  	}
		  	$first_row = 0;
		  	echo "</div>";
		}

		echo "</div>";
	  	echo "</div>";
	  	echo "<br>";
	  	choose_scenario_484($number_of_results);

	}
	return $number_of_results;
}


function replace_scenario_name($to_replace) {
  	switch ($to_replace) {
		case 'do_nothing':
			return 'Do Nothing';
			break;
		case 'fix_prod_lvl_perform_upgd':
			return 'Abate Only';
			break;
		case 'fix_emiss_lvl_buy_allowns':
			return 'Buy Allowances Only';
			break;
		case 'fix_prod_buy_allowns_perf_upgd':
			return 'Buy Allowances and Abate';
			break;
		case 'decrease_emiss_only':
			return 'Lower Emission Level';
			break;
		case 'adj_tgt_sel_allowns_perf_upgd':
			return 'Abate and Sell Allowances';
			break;
		case 'sell_extra_allowns':
			return 'Sell Extra Allowances';
			break;
  	}
}

function choose_scenario_484($max_choices) {
	$scenario_number = "";

	if (isset($_POST['scenario_number'])) {
		$scenario_number = $_POST['scenario_number'];
	}
	echo <<<HTML
	Please choose the scenario that you will run:
	<form  name = "choose_scenario" method = "post">
	<input type = "number" name = "scenario_number" min = "1" max = $max_choices value = $scenario_number>
	<input type = "submit" name = "submit" value = "Select this scenario">
	</form>
HTML;

	if (isset($_POST['submit'])) {
		if (!empty($_POST['scenario_number'])) {
			confirm_484();
		} else {
			return_to_484();
		}
	}
}

function confirm_484() {
	if (isset($_POST['submit']) && !empty($_POST['scenario_number'])) {
		$scenario_number = $_POST['scenario_number'];
		echo <<<HTML
		Are you sure you want to run scenario $scenario_number?
		<form name = "confirm_scenario_choice" method = "post">
		<input type = "hidden" name = "scenario_number" value = $scenario_number>
		<input type = "submit" name = "confirm" value = "Confirm">
		<input type = "submit" name = "cancel" value = "Cancel">
HTML;
	}
}

function mark_scenario_as_chosen() {
	$ce_db = access_db();
    $tbl = 'carbonexch_scenario_play';

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

  	$check_query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND game_period_id = $period_id AND Pick_this_scenario = 'y'";
  	$check = $ce_db->get_row(sprintf($check_query, $ce_db->prefix, $tbl), ARRAY_A, 0);
  	if (!is_null($check)) {
  		echo "You already chose a scenario to run for this game period.";
  	} else {
  		$selected = $_POST['scenario_number'] - 1;

	  	$query = "SELECT * FROM `%s%s` WHERE account_id = $account_id AND game_period_id = $period_id";

	  	$result = $ce_db->get_row(sprintf($query, $ce_db->prefix, $tbl), ARRAY_A, $selected);
	  	$result_period_scenario_id = $result['period_scenario_id'];

	  	$sql_data = array('Pick_this_scenario' => 'y');
	  	$sql_where = array('period_scenario_id' => $result_period_scenario_id);
	  	$update_db = $ce_db -> update('ce_' . $tbl, $sql_data, $sql_where);

	  	if ($update_db) {
	  		echo "You can use scenario " . $_POST['scenario_number'] . "for the next trading period.";
	  	}
	}
}
  
function return_to_484() {
	echo "Please enter a scenario to run.";
}

add_shortcode('select', 'submit_scenario');
?>