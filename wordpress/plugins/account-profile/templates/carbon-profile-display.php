<?php

/**
 * Display company carbon allowances and costs data
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php $carbon_data = get_carbon_data( $_POST['account_id'] ); ?>
<p>Showing profile for account ID: <?php echo esc_html($_POST['account_id']); ?></p>

<?php ob_start(); ?>
<!-- Show general user info -->
<div class="carbon-table-wrapper">
    <div class="carbon-table">
        <div class="profile-row">
            <h3 class="profile-row-data"></h3>
            <h3 class="profile-row-data">General Info</h3>
        </div>
<?php foreach ($carbon_data['general_fields'] as $col => $name) { ?>
        <div class="profile-row">
            <label class="profile-row-data"><?php echo esc_html($name); ?></label>
            <p class="profile-row-data"><?php echo esc_html($carbon_data['final']->$col); ?></p>
        </div>
    <?php } ?>
    </div>
</div>

<!-- Show all period measures -->
<div class="carbon-table-wrapper">
    <h3 class="profile-header">Period Summary</h3>
    <div class="carbon-table">
        <div class="profile-row">
            <div class="profile-row-data"></div>
            <div class="profile-row-data">Period 1</div>
            <div class="profile-row-data">Period 2</div>
            <div class="profile-row-data">Period 3</div>
            <div class="profile-row-data">Period 4</div>
            <div class="profile-row-data">Period 5</div>
        </div>

<?php foreach ($carbon_data['period_fields'] as $col => $name) { ?>
    <div class="profile-row">
        <label class='profile-row-data'><?php echo esc_html($name); ?></label>
    <?php foreach ($carbon_data['periods'] as $period) { ?>
        <p class="profile-row-data"><?php echo esc_html($period->$col); ?></p>
    <?php } ?>
    </div>
<?php } ?>
</div>
</div>

<!-- Show final measures -->
<div class="carbon-table-wrapper">
    <div class="carbon-table">
        <div class="profile-row">
            <h3 class="profile-row-data"></h3>
            <h3 class="profile-row-data">Final</h3>
        </div>
<?php foreach ($carbon_data['final_fields'] as $col => $name) { ?>
        <div class="profile-row">
            <label class="profile-row-data"><?php echo esc_html($name); ?></label>
            <p class="profile-row-data"><?php echo esc_html($carbon_data['final']->$col); ?></p>
        </div>
    <?php } ?>
    </div>
</div>

<?php echo ob_get_clean(); ?>
