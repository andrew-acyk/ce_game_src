<?php
/* Plugin Name: login
 Description: Log in to your account
 Author: Albert Hu
 Version: 0.0
 */

function access_db() {
	$ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST); 
  	$ce_db->set_prefix('ce_');
  	$ce_db->show_errors();
  
  	return $ce_db;
}


function check_logged_in() {
	if (is_user_logged_in()) {
		if (!isset($_COOKIE['ce_info'])) {
			$db = access_db();
			$wp_login_id = wp_get_current_user()->user_email;
			$query = "select acct.account_id, acct.wp_login_id, map.game_id, map.company_id, map.plant_id, 
    				gm.game_start_date, substr(gm.game_period_id, locate(gm.game_period_id, '-')+1) as period_id, gm.game_period_id, gm.carbon_price_last_perd
					from ce_account acct, ce_account_company_plant_map map, ce_game gm
					where acct.account_id = map.account_id
					and map.game_id = gm.game_id
					and acct.wp_login_id = '$wp_login_id'";

			$result = $db->get_results($query);
			if (empty($result)) {
				echo "You must create a game account to continue.  Please contact an administrator.";
			} else {

			/* Values stored in cookie: account_id, wp_login_id, game_id, period_id, company id, 
			 * plant_id, game_start_date, game_period_id.
			 * Values are separated with '|'
			 * To get the values, you can just explode('|', cookie), which will give you an array
			 */
			$result = $result[0];

			$account_id = $result->account_id;
			$wp_login_id = $result->wp_login_id;
			$game_start_date = $result->game_start_date;
			$game_id = $result->game_id;
			$period_id = substr($result->period_id, -1);
			$game_period_id = $result->game_period_id;
			$plant_id = $result->plant_id;
			$carbon_price_last_perd = $result->carbon_price_last_perd;



			$cookie_info = $account_id . "|" . $wp_login_id . "|" . $game_start_date . "|" . $game_id . "|" . $period_id . "|" . $game_period_id . "|" . $plant_id . "|" . $carbon_price_last_perd;
			setcookie('ce_info', $cookie_info, time() + 24 * 60 * 60, '/');
			}
		}
	     //FOR DEBUGGING PURPOSES
			// $db = access_db();
			// $username = wp_get_current_user()->user_email;
			// $query = "select acct.account_id, acct.wp_login_id, map.game_id, map.company_id, map.plant_id, 
   //  				gm.game_start_date, substr(gm.game_period_id, locate(gm.game_period_id, '-')+1) as period_id, gm.game_period_id, gm.carbon_price_last_perd
			// 		from ce_account acct, ce_account_company_plant_map map, ce_game gm
			// 		where acct.account_id = map.account_id
			// 		and map.game_id = gm.game_id
			// 		and acct.wp_login_id = '$username'";
	  // 		$result = $db->get_results($query);
	  // 		$result = $result[0];
	  // 		print_r($result);
	  // 		print_r($username);
	  // 		print_r($_COOKIE['ce_info']);
	} else {
	  	if ($_SERVER['PHP_SELF'] != '/wordpress/wp-login.php') {

	  		// deletes any cookies that are present if user logs out
	  		if (isset($_COOKIE['ce_info'])) {
	  			setcookie('ce_info', "", time() - 3600, '/');
	  		}
			wp_redirect('http://54.183.29.82/wordpress/wp-login.php');
			exit();
	  	}
	}
}


add_action('init', 'check_logged_in');


 ?>