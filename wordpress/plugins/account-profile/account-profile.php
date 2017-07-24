<?php
/*
 * Plugin Name: Show Account Profile 
 * Description: Page 4.8.1 
 * Author: Soohee Lee
 */

/*
 * Hook template to a page, not a post
 * Commented out since we are using posts for now.
 *
add_filter( 'page_template', 'profile_page_template' );

function profile_page_template( $page_template ) {
    if ( is_page( 91 ) || is_page( 152) ) {
        $page_template = dirname( __FILE__ ) . '/templates/show-profile.php';
    }
    return $page_template;
}
 */

/**
 * Register and enqueue javascript and css files.
 *
 * This function hooks on to the Wordpress wp_enqueue_scripts
 * action hook.
 */
function soohee_scripts() {

    wp_enqueue_style( 'ce_account-profile-style',  plugin_dir_url( __FILE__ ) . 'css/profile.css' );

}
add_action( 'wp_enqueue_scripts', 'soohee_scripts' );




/**
 * Get carbon exchange summary data from ce_schema
 *
 * This function is called from templates/account-profile-display.php and used to
 * display data on page 4.8.1.
 *
 * @param       string      $account_id     The account ID of user.
 * @return      array       $result         Array containing user account, company,
 *                                          and plant query result.
 */
function ce_show_profile( $account_id ) {

    $ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
    $account_id = sanitize_text_field( $account_id );
    $result = $ce_db->get_row(
        $ce_db->prepare(
            "
            SELECT a. * , c. * , p. *
            FROM ce_account AS a, ce_company AS c, ce_plant AS p, ce_account_company_plant_map as m
            WHERE m.account_id = %s
                  AND m.account_id = a.account_id
                  AND m.company_id = c.company_id
                  AND m.plant_id = p.plant_id
            ", 
            $account_id 
        ), ARRAY_A
    );
    if ($result) {
        return $result;
    }
}


/**
 * Get carbon exchange summary data from ce_schema
 *
 * This function is called from templates/carbon-profile-display.php and used to
 * display data on page 4.8.2.
 *
 * @param       string      $account_id     The account ID of user.
 * @return      array       $output         Array containing period and final summary data.
 */
function get_carbon_data( $account_id ) {
    $ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
    $account_id = sanitize_text_field( $account_id );
    $periods = array();

    $general_fields = array(
        'account_id'      => 'account ID',
        'plant_id'        => 'plant ID',
        'game_id'         => 'game ID',
        'game_start_date' => 'game start date');

    $period_fields = array(
        'period_start_prod_unit'             => 'start production unit',
        'period_end_prod_unit'               => 'end production unit',
        'allowances_allocated'               => 'allowances allocated',
        'period_end_emissions_cap'           => 'period end emissions cap',
        'allowances_in_bank'                 => 'banked allowances',
        'initial_allowns_can_be_used'        => 'allowances available',
        'tgt_allowns_lvl_for_abatmt'         => 'target level for abatement, if any',
        'emissns_ov_allowns_can_be_used'     => 'emissions over allocated allowances',
        'emissns_covered_by_abatmt'          => 'emissions covered by abatement',
        'emissns_covered_by_allowns'         => 'emissions covered by allowances',
        'allowns_needed_fr_trading'          => 'allowances needed from trading',
        'costs_trading'                      => 'costs of trading',
        'costs_abatement'                    => 'costs of abatement',
        'costs_penalty'                      => 'costs of penalty',
        'costs_allowances_in_bank'           => 'costs of banked allowances',
	    'costs_allocated_allowances'         => 'costs of period initial allocated allowances',
	  	'costs_trades_in_queue'              => 'costs of trades in waiting',
	    'total_costs'                        => 'total costs',
        'total_costs_per_unit_period_tcpup'  => 'total costs per unit',
		);

    $final_fields = array(
	    'adjusted_costs_per_unit_acpu'             => 'adjusted costs per unit',
        'adjusted_costs_per_unit_percentile_acpup' => 'adjusted costs per unit in percentile',
        'normalized_costs_per_unit_percentile'     => 'normalized costs per unit in percentile',
        'rank_on_model'                            => 'rank on same model',
        'rank_on_game'                             => 'rank on game',
	);


    $cookie_value = $_COOKIE["ce_info"];
    $game_start_date = explode('|', $cookie_value)[2];
    $game_id = explode('|', $cookie_value)[3];

    for ($i = 1; $i < 6; $i++) {
        $periods[$i] = $ce_db->get_row(
            $ce_db->prepare(
                "
                SELECT * 
                FROM `ce_carbonexch_period_summary` 
                WHERE account_id = %s
                      AND game_period_id = %s
                      AND game_id = %s
                      AND game_start_date = %s
                ", 
                $account_id, $i, $game_id, $game_start_date
            )
        );
    }

    $final = $ce_db->get_row(
        $ce_db->prepare(
            "
            SELECT * 
            FROM `ce_carbonexch_final_summary` 
            WHERE account_id = %s
                  AND game_id = %s
                  AND game_start_date = %s
            ", 
            $account_id, $game_id, $game_start_date
        )
    );

    $output = array(
        'general_fields' => $general_fields,
        'period_fields'  => $period_fields,
        'final_fields'   => $final_fields,
        'periods'        => $periods,
        'final'          => $final 
    );

    return $output;
}

/**
 * Get final scores and winner data for page 4.8.7.
 *
 * This function is called from templates/final-winner-display.php and used to
 * display data on page 4.8.7.
 *
 * @param       string      $account_id     The account ID of user.
 * @return      array       $output         Array containing final scores data.
 */
function get_final_scores_and_winner( $account_id ) {
    $ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
    $ce_db->show_errors();
    $account_id = sanitize_text_field( $account_id );

    $final_scores_column_names = array(
        'account_id'                               => 'account ID',
        'plant_id'                                 => 'plant ID',
        'rank_on_model'                            => 'rank on model',
        'rank_on_game'                             => 'rank on game',
        'period_start_prod_unit'                   => 'start production unit',
        'period_end_prod_unit'                     => 'end production unit',
        'total_costs_per_unit_period_tcpup'        => 'total costs per unit final period',
        'adjusted_costs_per_unit_percentile_acpup' => 'total costs per unit in percentile',
        'normalized_costs_per_unit_percentile'     => 'normalized costs per unit in percentile'
    );

    $cookie_value = $_COOKIE["ce_info"];
    $game_start_date = explode('|', $cookie_value)[2];
    $game_id = explode('|', $cookie_value)[3];

    $final_scores_query_result = $ce_db->get_results(
	  $ce_db->prepare(
        "
        SELECT f.*, p1.period_start_prod_unit, p5.period_end_prod_unit, p5.total_costs_per_unit_period_tcpup
        FROM `ce_carbonexch_final_summary` f
        LEFT JOIN `ce_carbonexch_period_summary` p1
                  ON f.account_id = p1.account_id
        LEFT JOIN `ce_carbonexch_period_summary` p5
                  ON f.account_id = p5.account_id
        WHERE f.game_id = p1.game_id 
              AND f.game_id = %s
              AND f.game_start_date = %s
              AND p1.game_id = f.game_id
              AND p1.game_start_date = f.game_start_date
              AND p5.game_id = p1.game_id 
              AND p1.game_period_id = '1'
              AND p5.game_period_id = '5'
        ", 
		$game_id, $game_start_date
      )
    );

    $winner_on_game = $ce_db->get_row(
	  $ce_db->prepare(
        "
        SELECT f.*, p1.period_start_prod_unit, p5.period_end_prod_unit, p5.total_costs_per_unit_period_tcpup
        FROM `ce_carbonexch_final_summary` f
        LEFT JOIN `ce_carbonexch_period_summary` p1
                  ON f.account_id = p1.account_id
        LEFT JOIN `ce_carbonexch_period_summary` p5
                  ON f.account_id = p5.account_id
        WHERE f.game_id = p1.game_id 
              AND f.game_id = %s
              AND f.game_start_date = %s
              AND p1.game_id = f.game_id
              AND p1.game_start_date = f.game_start_date
              AND p5.game_id = p1.game_id 
              AND p1.game_period_id = '1'
              AND p5.game_period_id = '5'
              AND f.rank_on_game = '1'
        ", $game_id, $game_start_date
	  )
    );

    $winners_on_model = array();

    foreach ($final_scores_query_result as $team) {
        if ($team->rank_on_model === '1') {
            $winners_on_model[] = $team;
        }
    }

    $output = array(
        'column_names'     => $final_scores_column_names,
        'query_result'     => $final_scores_query_result,
        'winner_on_game'   => $winner_on_game,
        'winners_on_model' => $winners_on_model
        );

    return $output;
}

?>
