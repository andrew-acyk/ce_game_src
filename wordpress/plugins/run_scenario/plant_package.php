<?php


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
