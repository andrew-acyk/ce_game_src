<?php
/*
 * Plugin name: Calculations
 * Description: Perform all calculations for Run_scenario
 * Author: Albert Hu
 */

function extra_calc_scenario_two($base, $goal) {
	if ($base >= $goal) {
		return 0;
	} else {
		$ce_db = access_db();

		$diff = $goal - $base;
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

		$query = "SELECT * FROM ce_game WHERE game_id = $game_id AND game_period_id = $game_period_id";
		$result = $ce_db->get_results($query, ARRAY_A);
		$carbon_price = $result["carbon_price_last_perd"];


	}
}
?>

