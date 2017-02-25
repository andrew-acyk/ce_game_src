<?php

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
  	if (isset($_POST['reset_db']) && isset($_POST['enter'])) {
		clear_db();
	} else if (!isset($_POST['scenario_to_run']) && isset($_POST['enter'])) {
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

  	// print_r($summary);

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
  		$costs_penalty = $emissions_over_allowns_can_be_used * $penalty;
  		$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
  		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;
  		$total_costs = $costs_allocated_allowances + $costs_penalty + $costs_allowances_in_bank;

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
  		'tgt_allowns_lvl_for_abatmt' => $period_start_emissions_ton,

  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => 0,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => 0,
  		'costs_trading' => 0,
  		'costs_abatement' => 0,
  		'costs_penalty' => $costs_penalty,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs);

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
  		'tgt_allowns_lvl_for_abatmt' => $period_end_emissions_ton,

  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => 0,
  		'costs_trading' => 0,
  		'costs_abatement' => $costs_abatement,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
   		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs);

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
  		'tgt_allowns_lvl_for_abatmt' => 0,


  		'emissns_ov_allowns_can_be_used' => $emissions_over_allowns_can_be_used,
  		'emissns_covered_by_abatmt' => 0,
  		'emissns_covered_by_allowns' => $initial_allowns_can_be_used,
  		'allowns_needed_fr_trading' => $emissions_over_allowns_can_be_used,
  		'costs_trading' => $costs_trading,
  		'costs_abatement' => 0,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs);

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

  		if ($period_end_emissions_cap <= $period_start_emissions_ton) {
  			//do nothing
  			//you are already done because you have more allowances than the cap
  		} else {
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
  		'total_costs' => $total_costs);


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


   		$new_emissions_level = $period_end_emissions_ton;
   		$new_prod_units = $period_end_prod_unit;
   		$allowns_needed_fr_trading = 0;
   		$costs_trading = 0;

    	$costs_allocated_allowances = $initial_allowns_can_be_used * $carbon_price_last_perd;
   		$costs_allowances_in_bank = $allowances_in_bank * $carbon_price_last_perd;

   		if ($period_start_emissions_ton > $period_end_emissions_cap) {
	   		$new_emissions_level = $period_end_emissions_cap;
	   		$new_prod_units = get_units_given_emissions($new_emissions_level, $plant_id);
	   		$allowns_needed_fr_trading = ($period_start_emissions_ton - $period_end_emissions_cap) * -1;
	   		$costs_trading = $allowns_needed_fr_trading * $carbon_price_last_perd;
   		}

   		$total_costs = $costs_trading + $costs_allowances_in_bank + $costs_allocated_allowances;

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
  		'allowns_needed_fr_trading' => $allowns_needed_fr_trading,
  		'costs_trading' => $costs_trading,
  		'costs_abatement' => 0,
  		'costs_penalty' => 0,
  		'costs_allocated_allowances' => $costs_allocated_allowances,
  		'costs_allowances_in_bank' => $costs_allowances_in_bank,
  		'total_costs' => $total_costs);


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
  		'total_costs' => $total_costs);

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


	echo<<<HTML
		<form name = "adjust_abatemt_lvl_sell_allowns_perform_upgde" method = "post">
			Enter a target level for the number of allowances:
			<br>
			<input type = "number" name = "target_abatement_level" min = "0">

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
  		'total_costs' => $total_costs);


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

function clear_db() {
    $ce_db = access_db();
  	$delete = $ce_db->query("TRUNCATE ce_carbonexch_scenario_play");
    if ($delete) {
    	echo "Cleared entire 'ce_carbonexch_scenario_play' database";
    }
}

function show_post() {
  print_r($_POST);
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

add_shortcode('scenario', 'response');

?>
