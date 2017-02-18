<?php
/*
 * Plugin Name: Show Trades
 * Description: Displays all trades and their status for a given company.
 * Author: Anthony Dela Paz
 */
function prompt_company(){
	echo <<<A_NAME
	<form method="post">
	    Enter account id: <br>
	    <input type="text" name="acc-id" min="0">
	    <br><input type="submit" name="submit-acc" value="Submit">
	</form>
A_NAME;
	if (isset($_POST["submit-acc"])) {
		display_all_trades();
	}
}
function display_all_trades(){
	$self = $_SERVER["PHP_SELF"];
 	$ce_db = new wpdb(CE_USER, CE_PASSWORD, CE_NAME, CE_HOST);
 	$ce_db->set_prefix("ce_");
 	// $ce_db->show_errors();
 	// $result = $ce_db->get_row("SELECT * FROM ce_emission_trade", 0, 0);
 	// Put this in TABLE_HTML if css doesn't work

 	echo <<<TABLE_HTML
 	<table style="display: inline-block;
    padding: 30px 20px;
    background-color: rgb(248, 248, 248);
    margin-bottom: 20px;
    border-radius: 10px;
    -webkit-border-radius: 10px;">
    <tr>
    	<th>	</th>
    	<th>Transaction ID</th>
    	<th>Account ID</th>
    	<th>Market ID</th>
    	<th>Game Start Date</th>
    	<th>Game Period ID</th>
    	<th>Buy or Sell</th>
    	<th>Allowances Buy Amount</th>
    	<th>Allowances Buy Price</th>
    	<th>Allowances Sell Amount</th>
    	<th>Allowances Sell Price</th>
    	<th>Accept Partial</th>
    	<th>Original Transaction ID</th>
    	<th>Submit Datetime</th>
    	<th>Status</th>
    	<th>Created Date</th>
    	<th>Created By</th>
    	<th>Updated Date</th>
    	<th>Updated By</th>
    </tr>
TABLE_HTML;
	
	// $row = array(
	// 	"transaction_id" => NULL,
	// 	"account_id"=> NULL,
	// 	"market_id"=> NULL,
	// 	"game_start_date"=> NULL,
	// 	"game_period_id"=> NULL,
	// 	"buy_or_sell"=> NULL,
	// 	"allowances_buy_amt"=> NULL,
	// 	"allowances_buy_price"=> NULL,
	// 	"allowances_sell_amt"=> NULL,
	// 	"allowances_sell_price"=> NULL,
	// 	"accept_partial"=> NULL,
	// 	"original_transaction_id"=> NULL,
	// 	"submit_datetime"=> NULL,
	// 	"status"=> NULL,
	// 	"created_date"=> NULL,
	// 	"created_by"=> NULL,
	// 	"updated_date"=> NULL,
	// 	"updated_by"=> NULL
	// 	);
	$init = $ce_db->get_row("SELECT * FROM ce_emissions_trade")->num_rows;
	for ($j = 0; $j < ($ce_db->num_rows); $j++) {
		// $row = $arr->fetch_assoc();
		$row = $ce_db->get_row("SELECT * FROM ce_emission_trade WHERE account_id = \"" . $_POST["acc-id"] . "\"", ARRAY_N, $j);
		echo "<tr>";
		// echo "<td>" . $row["transaction_id"] . "</td>";
		// echo "<td>" . $row["account_id"] . "</td>";
		// echo "<td>" . $row["market_id"] . "</td>";
		// echo "<td>" . $row["game_start_date"] . "</td>";
		// echo "<td>" . $row["game_period_id"] . "</td>";
		// echo "<td>" . $row["buy_or_sell"] . "</td>";
		// echo "<td>" . $row["allowances_buy_amt"] . "</td>";
		// echo "<td>" . $row["allowances_buy_price"] . "</td>";
		// echo "<td>" . $row["allowances_sell_amt"] . "</td>";
		// echo "<td>" . $row["allowances_sell_price"] . "</td>";
		// echo "<td>" . $row["accept_partial"] . "</td>";
		// echo "<td>" . $row["original_transaction_id"] . "</td>";
		// echo "<td>" . $row["submit_datetime"] . "</td>";
		// echo "<td>" . $row["status"] . "</td>";
		// echo "<td>" . $row["created_date"] . "</td>";
		// echo "<td>" . $row["created_by"] . "</td>";
		// echo "<td>" . $row["updated_date"] . "</td>";
		// echo "<td>" . $row["updated_by"] . "</td>";
		for ($i = 0; $i < 18; $i++) {
			echo "<td>" . $row[$i] . "</td>";
		}
		// echo $row;
		echo "</tr>";
	}
	echo "</table>";

}

add_shortcode('show_trades', 'prompt_company');
?>