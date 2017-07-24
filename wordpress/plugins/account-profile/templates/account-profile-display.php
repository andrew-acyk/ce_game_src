<?php
/*
 *  Display profile data after verifying account ID.
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php $result = ce_show_profile($_POST['account_id']); ?>

<p>Showing profile for account ID: <?php echo esc_html($_POST['account_id']); ?></p>
<div class="profile-inner-wrapper">
    <h3 class="profile-header">Account</h3>
        <div class="profile-row">
            <label class="profile-row-header">Name</label>
            <p><?php echo esc_html($result['account_name']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
        <div class="profile-row">
            <label class="profile-row-header">Team Members</label>
            <p><?php echo esc_html($result['team_member']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
        <div class="profile-row">
            <label class="profile-row-header">Description</label>
            <p><?php echo esc_html($result['account_desc']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
</div>

<div class="profile-inner-wrapper">
    <h3 class="profile-header">Company</h3>
        <div class="profile-row">
            <label class="profile-row-header">Name</label>
            <p><?php echo esc_html($result['company_name']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
        <div class="profile-row">
            <label class="profile-row-header">Description</label>
            <p><?php echo esc_html($result['company_desc']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
</div>

<div class="profile-inner-wrapper">
    <h3 class="profile-header">Plant</h3>
        <div class="profile-row">
            <label class="profile-row-header">Name</label>
            <p><?php echo esc_html($result['plant_name']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
        <div class="profile-row">
            <label class="profile-row-header">Description</label>
            <p><?php echo esc_html($result['plant_desc']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
        <div class="profile-row">
            <label class="profile-row-header">Initial Production Unit</label>
            <p><?php echo esc_html($result['initial_production_unit']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
                <div class="profile-row">
            <label class="profile-row-header">One Time Production Increase</label>
            <p><?php echo esc_html($result['one_time_addl_prod_increase']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
        <div class="profile-row">
            <label class="profile-row-header">Period of Production Increase</label>
            <p><?php echo esc_html($result['period_of_prod_increase']); ?></p>
            <div class="profile-row-edit"></div>
        </div>
        <div class="profile-row">
            <label class="profile-row-header">Emission Equation</label>
            <p><?php echo esc_html($result['emissn_eq_given_units_calc_emissns']); ?></p>
            <div class="profile-row-edit"></div>
        </div>

</div>
