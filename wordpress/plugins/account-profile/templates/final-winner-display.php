<?php

/**
 * Display overall winner and final scores ranking
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php $final_scores = get_final_scores_and_winner( $_POST['account_id'] ); ?>

<?php ob_start(); ?>

<p>Showing ranking for game ID: <?php echo esc_html($final_scores['winner_on_game']->game_id); ?> and game start date: <?php echo esc_html($final_scores['winner_on_game']->game_start_date); ?></p>

<!-- Show final winner of game -->
<div class="carbon-table-wrapper" id="is-game-winner">
    <h3 class="profile-header">Overall Winner</h3>
    <div class="carbon-table">
	    <div class="profile-row">
		<?php foreach ($final_scores['column_names'] as $col => $name) { ?>
		    <div class="profile-row-data"><?php echo esc_html($name); ?></div>
		<?php } ?>
		</div>
        <div class="profile-row">
	    <?php foreach ($final_scores['column_names'] as $col => $name) { ?>
		    <p class='profile-row-data'><?php echo esc_html($final_scores['winner_on_game']->$col); ?></p>
		<?php } ?>
		</div>
	</div>
</div>

<!-- Show ranking of all teams ordered by plant ID -->
<div class="carbon-table-wrapper">
    <h3 class="profile-header">Ranking by Model (Plant)</h3>
    <div class="carbon-table">
        <div class="profile-row">
        <?php foreach ($final_scores['column_names'] as $col => $name) { ?>
            <div class="profile-row-data"><?php echo esc_html($name); ?></div>
        <?php } ?>
        </div>

	<?php foreach ($final_scores['query_result'] as $team) {
		$is_model_winner = false;
		if ( in_array($team, $final_scores['winners_on_model']) ) { 
			$is_model_winner = true; ?>
			<div class="profile-row is-model-winner">
		<?php } else { ?>
			<div class="profile-row">
		<?php } 
		foreach ($final_scores['column_names'] as $col => $name) { 
			if ( $col === 'rank_on_game' && ! $is_model_winner ) { ?>
				<p class='profile-row-data'>-</p>
				<?php continue; ?>
			<?php } if ( $col === 'normalized_costs_per_unit_percentile' && ! $is_model_winner ) { ?>
				<p class='profile-row-data'>-</p>
				<?php continue; ?>
	        <?php } ?>
	        <p class='profile-row-data'><?php echo esc_html($team->$col); ?></p>
		<?php } ?>
		</div>
	<?php } ?>
	</div>
</div>

<?php echo ob_get_clean(); ?>
