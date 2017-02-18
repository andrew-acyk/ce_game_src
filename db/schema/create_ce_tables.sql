-- Last updated: 7/3/2016

-- Table of account, either team or individual
create table ce_account (
account_id varchar(30) not null, -- Annnnn
account_name varchar(50) not null,
account_desc varchar(1000),
wp_login_id varchar(30) not null, -- Lnnnnn
team_member varchar(1000), -- members in the team
Retire_flag char (1),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- Need session table where Lnnnnn-YYMMDD-HHMMSS-GnnPnn is defined
-- Also keep session parameters

-- Table of company.  It is also an alias to the team.  It owns plants used in carbon trading.
create table ce_company (
--account_id varchar(30) not null, -- Annnnn
company_id varchar(30) not null, -- alphanumeric id
company_name varchar(50) not null,
company_desc varchar(1000),
Retire_flag char (1),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- Table of plant.  It contains the following properties and profiles: production level,
-- yearly emission in ton per 1000 units produced, 
-- abatment costs in numbers per ton (or numbers per 1000 ton) of emission reduced.
-- Each plant has one time production increase to force each team on changing strategy.
-- The one time increase is staggered so that the system can become screate table quickly.
create table ce_plant (
plant_id varchar(30) not null, -- alphanumeric id
plant_name varchar(50) not null,
plant_desc varchar(1000),
Asset_or_liability char (1),
initial_production_unit number, -- units produced
Emission_equation varchar(1000), -- fn_em() emission in tons per 1000 units of production
Abatment_cost_equation varchar(1000), -- fn_ac() cost per ton (or cost per 1000 tons) of emission reduced, may use lookup table instead
label_cost_curve_lookup varchar(10), -- label used in ce_marginal_abatment_cost_lookup table.
one_time_prod_increase number, -- in units
year_of_prod_increase number, -- nth year
weight_factor_for_ranking number, -- normalize the top seats of each model to the same level.
Retire_flag char (1),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

create table ce_account_company_plant_map (
account_id varchar(30) not null, -- Annnnn
company_id varchar(30) not null, -- alphanumeric id
plant_id varchar(30) not null -- alphanumeric id
)

-- This lookup table contains data points of different marginal abatment cost curves.
-- The data points are represented by 3-tuples.  The first element of the tuple is
-- the label of the cost curve.  There are multiple cost curves.  One for each plant.
-- the second element is the CO2 emission in tons.  The third element is the total costs
-- representing the area underneath the marginal abatment cost curve.
create table ce_marginal_abatment_cost_lookup (
label varchar (10) not null,
emission_ton number not null,
cost_dollor number
)

create Table ce_configuration_parameter (
Parameter_name varchar(30) not null,
Parameter_desc varchar(1000),
Parameter_label varchar(30), -- same label used in equation
Category varchar(30),
Parameter_data_type varchar(10),
Parameter_value varchar(30),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

create table ce_carbon_market_price (
Market_id varchar(30) not null, -- alphanumeric id
Market_name varchar(50) not null,
Price_per_permit_ton number, -- one permit is one ton of carbon emission
Price_date date,
Price_curve_trd_inflat_factor number, -- use for compensating the price curve trend
-- May put system_purchase_charge in ce_firguration_parameter table
system_purchase_charge number, -- fee charged by system if system completes the transaction,
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

---------------------

-- For now, we have three games: Trading, Sequestration (max ca reductn for each question), 
-- Carbon Footprint (min ca footprint for each question/stage of life)
create table ce_game (
game_id varchar(30) not null, -- Gnn
game_name varchar(50) not null,
game_desc varchar(1000),
Optimization_level varchar(10), -- game, question
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- Records of top winners of each company model
Create Table ce_winner_board (
Account_id varchar(30) not null, -- Annnnn
Year_start_date date,
Game_id varchar(30) not null, -- Gnn
plant_id varchar(30), -- alphanumeric id
total_costs_per_unit_percentile number, -- can have fraction
Winner_rank_game number, -- ranking among the top winners of each model.
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- For playing the scenario on each period of the carbon exchange game
-- Up to 5 records each for different scenario for each team in each of 5 periods
-- Production levels are the same for the teams playing the same model
-- Each team can view only its own records
Create Table ce_carbonexch_scenario_play (
Account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- alphanumeric id
Game_id varchar(30) not null, -- Gnn
year_start_date date,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
period_scenario_id varchar(30) not null, -- an unique id, a period can have up to 5 scenarios, alphanumeric id
period_scenario_desc varchar(1000), -- description of the scenario
scenario_choice varchar(30), -- 5 fixed choices: do_nothing, fix_prod_perform_upgrade, fix_prod_buy_permits, decrease_prod_only, sell_permits_perform_upgrade
period_start_prod_unit number,
period_end_prod_unit number,
ave_emission_ton number,-- read from emission equation at average prod unit
permits_allocated number,
period_end_emission_quota number,
emission_covered_by_permit number,
emission_covered_by_abatment number,
permit_needed_from_trading number,
costs_trading number, -- trade permits for the excessive emissions
costs_abatement number, -- reduce excessive emission by abatement
costs_penalty number, -- pay penalty on the excessive emissions
total_costs_per_unit_scenario number, -- add three costs and divided by production units
Pick_this_scenario char (1),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- For showing each period of the carbon exchange game
-- 1 record for each team in each of 5 periods
-- Production levels are the same for the teams palying the same model
-- Each team can view only its own records
Create Table ce_carbonexch_period_summary (
Account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- alphanumeric id
Game_id varchar(30) not null, -- Gnn
year_start_date date,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
period_desc varchar(1000), -- description of the period, including scenario picked
period_start_prod_unit number,
period_end_prod_unit number,
ave_emission_ton number, -- read from emission equation at average prod_unit
permits_allocated number, -- initial allocation at the beginning of each period
period_end_emission_quota number,
emission_covered_by_permit number,
emission_covered_by_abatment number, -- cannot be greater than period_end_emission_quota or permits_allocated whatever is higher
permit_needed_from_trading number,
costs_trading number, -- costs for permit_needed_from_trading
costs_abatement number, -- abatement costs
costs_penalty number, -- pay penalty on the excessive emissions
total_costs_per_unit_period number, -- add three costs and divided by production units
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- For showing team results over multiple periods.
-- also comparing the team with previous team with the same plant model
-- 1 record for each team cumulating all the costs for all 5 periods.
-- Records can be viewed by all teams
Create Table ce_carbonexch_team_summary (
Account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- alphanumeric id
Game_id varchar(30) not null, -- Gnn
year_start_date date, 
game_start_prod_unit number,
game_end_prod_unit number,
total_costs_game number, -- sum up total_costs of 5 periods
total_costs_per_unit_tcpu number, -- total_costs_game divided by production units
-- next two attributes move to final_summary
--adjusted_costs_per_unit_atcpu number, -- total_costs_per_unit_game after adjusted to the trend of price curve.
--total_costs_per_unit_percentile number, -- compare with previous teams using the same
--model and calculate in percentile
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- For showing all team results over all 5 periods
-- also comparing the all the teams across different models using their percentiles 
-- 1 record for each team.  Records can be viewed by all teams
Create Table ce_carbonexch_final_summary (
Game_id varchar(30) not null, -- Gnn
year_start_date date,
account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- alphanumeric id
total_costs_per_unit_tcpu number, -- total_costs_game divided by production units
adjusted_costs_per_unit_atcpu number, -- total_costs_per_unit_tcpu after adjusted to the trend of price price.
total_costs_per_unit_percentile number, -- compare with previous teams on the same model and calculate in percentile
rank_on_model number, -- ranking of each company model
rank_on_game number, -- ranking among the top winners of each model (percentile * weight_factor_for_ranking), if need 1 winner
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- Collection of team scores based on the same plant model.  Add the latest data points to the whole population samples
Create table ce_plant_model_data_points (
plant_id varchar(30) not null, -- alphanumeric id
account_id varchar(30) not null, -- Annnnn
adjusted_costs_per_unit_atcpu number
)

-- Calculate team scores based on the same plant model
Create table ce_plant_model_statistics (
plant_id varchar(30) not null, -- alphanumeric id
number_of_samples number,
average_of_atcpu number,
std_dev number
)

-----------------------

-- Need Flow Chart table where Fnn is defined

-- Show all the transactions for the game from begin to end including splitted orders
-- Each team can view only its own transactions
Create Table ce_carbon_trade (
Transaction_id varchar(30) not null, -- Lnnnnn-GnnFnn-Snnnnn, split id: Lnnnnn-GnnFnn-Snnnnn-n
Account_id varchar(30) not null, -- Annnnn
Market_id varchar(30) not null, -- alphanumeric id
Year_start_date date,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
Ccredit_bid_amt number, -- always pos, cannot buy liability
Ccredit_bid_price number, -- buy any at this price or lower, can buy in multiple bids at different price
Ccredit_ask_amt number, 
Ccredit_ask_price number, -- all amt sold at this price only
Accept_partial char (1),
-- When an order is splitted, a new record is created with the untraded amount, status partial, and original_transaction_id
-- The current record is updated with traded amount, partially_completed status, and trade date
Original_transaction_id varchar(30), -- for split trade only, keep track of the original transaction id.
Submit_datetime date,
Status varchar(10), -- new, queued, partial, partially_completed, totally_completed,
-- new, search immediately in the database for match.  If no match, enter queue for periodic executions.
-- queued, In queue for periodic execution.  Queue is FIFO.  If not the whole amt was executed, the remaining amt would stay in the queue for next execution.
-- partial, a partial amt has been executed.  The record is splitted into two.  The current record is updated with traded amount, partially_completed status, and trade date.  The new record has the untraded amt, status partial, and original_transaction_id.  It will stay in the queue for next execution.
-- partially_completed.  When a record is splitted, the current record is updated with traded amount, partially_completed status and trade date
-- totally_completed, the entire order has been executed, the last splitted record is marked with totally_completed
-- system_completed, any orders that are not completed during the normal trading time will be completed by the system with system_purchase_charge.
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- For calculating order execution price summary for each transactoin in each period
-- Each team can view only its own transactions
Create Table ce_carbon_trade_details (
Transaction_id varchar(30) not null, -- Lnnnnn-GnnFnn-Snnnnn
Account_id varchar(30) not null, -- Annnnn
Market_id varchar(30) not null, -- alphanumeric id
Year_start_date date,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
Ccredit_bid_amt number, -- always pos, cannot buy liability
Ccredit_bid_price number, -- buy any at this price or lower, can buy in multiple bids at different price
Ccredit_ask_amt number, 
Ccredit_ask_price number, -- all amt sold at this price only
Accept_partial char (1),
Submit_datetime date,
-- save up to 5 different sub-transactions
transaction_id0 varchar(30), -- Lnnnnn-GnnFnn_Snnnnn-0
amt0_completed number,
amt0_price number,
transaction_id1 varchar(30),
amt1_completed number,
amt1_price number,
transaction_id2 varchar(30),
amt2_completed number,
amt2_price number,
transaction_id3 varchar(30),
amt3_completed number,
amt3_price number,
transaction_id4 varchar(30),
amt4_completed number,
amt4_price number,
average_price number, -- total costs of all sub-transactions / total amount when completed
remaining_amt_in_queue number,
queue_position number, -- when the order at the front of the queue is completed, queue_position will be changed
Status varchar(10), -- new, queued, partial, partially_completed, totally_completed, system_completed
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)

-- For showing final execution summary of every transaction for all teams up to this point
-- Each team can view only its own transactions
Create Table ce_carbon_trade_team_summary (
Transaction_id varchar(30) not null, -- Lnnnnn-GnnFnn-Snnnnn
Account_id varchar(30) not null, -- Annnnn
Market_id varchar(30) not null, -- alphanumeric id
Year_start_date date,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
Ccredit_bid_amt number, -- always pos, cannot buy liability
Ccredit_bid_price number, -- buy any at this price or lower, can buy in multiple bids at different price
actual_buy_price number,
Ccredit_ask_amt number, 
Ccredit_ask_price number, -- all amt sold at this price only
actual_sell_price number,
Submit_datetime date,
complete_datetime date,
Status varchar(10), -- new, queued, partial, partially_completed, totally_completed, system_completed
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)