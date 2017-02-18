<?php
/*
 * Plugin Name: Cancel Order
 * Description: For page 4.8.6
 * Author: Anthony Dela Paz
 */

function display_cancel() {
	echo <<<FORM
	<form method="post">
		Enter account id: <br>
		<input type="string" name="acc_id">
		<br>
		Enter transaction id: <br>
		<input type="string" name="trans_id">
		<br>
		<input type="submit" name="submit-cancel" value="Cancel order">
	</form>
FORM;
	if (isset($_POST["submit-cancel"])) {
		cancel();
	}
}
function cancel() {
	$self = $_SERVER["PHP_SELF"];
 	$ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
 	$ce_db->set_prefix("ce_");
 	$ce_db->show_errors();
 	$trans_account = $ce_db->get_var("SELECT account_id FROM ce_emissions_trade WHERE transaction_id = '" . $_POST["trans_id"] . "'");
 	if ($trans_account != $_POST["acc_id"]) {
 		echo "Transaction does not exist for this account";
 		return;
 	}
 	$ce_db->query("DELETE FROM ce_emissions_trade WHERE transaction_id ='" . $_POST["trans_id"] . "'");
 	echo "<form method='post'>";
	echo "	Order cancelled! <br>";
	echo "	Transaction id " . $_POST["trans_id"] . "has been cancelled.";
	echo "  <input type='submit' name='confirm-cancel' value='OK'>";
	echo "</form>";
}

add_shortcode('cancel_order', 'display_cancel');
?>