-- Last updated: 8/20/2016

use ce_schema;

-- Table of account, either team or individual
drop table ce_account;

create table ce_account (
account_id varchar(30) not null, -- Annnnn
account_name varchar(50) not null,
account_desc varchar(1000),
wp_login_id varchar(30) not null, -- Lnnnnn
team_member varchar(1000), -- members in the team
retire_flag char (1),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id)
);

create unique index uidx_ce_account_acct_name on ce_account(account_name);

create unique index uidx_ce_account_wp_login on ce_account(wp_login_id);

-- Need session table where Lnnnnn-YYMMDD-HHMMSS-GnnPnn is defined
-- Also keep session parameters

-- Table of company. It is also an alias to the team. It owns plants used in carbon trading. 
drop table ce_company;

CREATE TABLE ce_company(
-- account_id varchar(30) not null, -- Annnnn 
company_id VARCHAR( 30 ) NOT NULL , -- numeric id
company_name VARCHAR( 50 ) NOT NULL ,
company_desc VARCHAR( 1000 ) ,
retire_flag CHAR( 1 ) ,
created_date DATE NOT NULL ,
created_by VARCHAR( 30 ) NOT NULL ,
updated_date DATE NOT NULL ,
updated_by VARCHAR( 30 ) NOT NULL,
primary key (company_id)
);

create unique index uidx_ce_company_comp_name on ce_company(company_name);

-- Table of plant.  It contains the following properties and profiles: production level,
-- yearly emission in ton per 1000 units produced, 
-- abatement costs in numbers per ton (or numbers per 1000 ton) of emission reduced.
-- Each plant has one time production increase to force each team on changing strategy.
-- The one time increase is staggered so that the system can become screate table quickly.
drop table ce_plant;

create table ce_plant (
plant_id varchar(30) not null, -- numeric id
plant_name varchar(50) not null,
plant_desc varchar(1000),
asset_or_liability char (1),
initial_production_unit int, -- units produced
emission_equation varchar(1000), -- fn_em() emission in tons per 1000 units of production
marginal_abatement_cost_eq varchar(1000), -- fn_ac() cost per ton (or cost per 1000 tons) of emission reduced, may use lookup table instead
label_cost_curve_lookup varchar(10), -- label used in ce_marginal_abatement_cost_lookup table.
one_time_prod_increase int, -- in units
period_of_prod_increase int, -- nth year
weight_factor_for_ranking float, -- normalize the top seats of each model to the same level.
retire_flag char (1),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (plant_id)
);

create unique index uidx_ce_plant_plant_name on ce_plant(plant_name);

drop table ce_account_company_plant_map;

-- Everything is partitioned by game.  Multiple games can be playing at the same time.
-- Each game can have multiple teams.  Each team can have one or more companies.  Each company can have one or more plants
create table ce_account_company_plant_map (
game_id varchar(30) not null,
account_id varchar(30) not null, -- Annnnn
company_id varchar(30) not null, -- numeric id
plant_id varchar(30) not null, -- numeric id
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id, company_id, plant_id)
);

-- This lookup table contains data points of different marginal abatement cost curves.
-- The data points are represented by 3-tuples.  The first element of the tuple is
-- the label of the cost curve.  There are multiple cost curves.  One for each plant.
-- the second element is the CO2 emission in tons.  The third element is the total costs
-- representing the area underneath the marginal abatement cost curve.
drop table ce_marginal_abatement_cost_lookup;

create table ce_marginal_abatement_cost_lookup (
label varchar (10) not null,
emissions_ton int not null,
costs_dollar float,
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (emissions_ton, label)
);

drop table ce_configuration_parameter;

create Table ce_configuration_parameter (
parameter_name varchar(30) not null,
parameter_desc varchar(1000),
parameter_label varchar(30), -- same label used in equation
category varchar(30),
parameter_data_type varchar(10),
parameter_value varchar(1000),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (parameter_name)
);

/* Some of the configuration parameters are:
   (categories: screen, scenario, model, dashboard, carbon_exchange, server_process, trading, gen, ??)
parameter_name, parameter_desc, parameter_label, category, parameter_data_type, parameter_value
-----------------------------------------------------------------------------------------------
NOT COMPLETE
'current_period', 'Identify current period, changed by system', , 'server_process', 'integer', '1'
'scenario_seq_num', 'Sequence number for scenario.  Read and increment by 1', , 'scenario', 'integer', '1'
'flow_chart_realtime_bid', 'Id of flow chart Realtime Bid Request', , 'server_process', 'integer', '1'
'flow_chart_realtime_ask', 'Id of flow chart Realtime Ask Request', , 'server_process', 'integer', '2'
'flow_chart_recurent_job', 'Id of flow chart Recurrent Job', , 'server_process', 'integer', '3'
'flow_chart_system_completion', 'Id of flow chart System Completion After Expiration', , 'server_process', 'integer', '4'
'transaction_seq_num', 'Sequence number for transaction.  Read and increment by 1', , 'trading', 'integer', '1'
'penalty_on_exceeding_emissions_cap_perc', 'Penalty on exceeding emission cap in percentage', , 'carbon_exchange', 'integer', '0??'
'recurrent_job_interval_min', 'Interval in mins between recurrent trade execution', , 'server_process', 'integer', '15'
'system_completion_aft_expire_min', 'System completion starts in mins after trading expiration in each period', , 'server_process', 'integer', '3'
'reduction_in_emissions_cap_period_perc', 'Reduction in emissions cap in percentage for each period', , 'carbon_exchange', 'float', '5.0'
'allowances_allocated_every_period_perc', 'Allowances allocated in percentage at the beginning of each period', , 'carbon_exchange', 'float', '90.0'
'allowances_on_banking_perc', 'Maximum allowances in percentage that can be banked', , 'carbon_exchange', 'float', '0.5'
'verify_account_id', 'Verify account_id for security reason', , 'screen', 'char', 'y'
'trading_split_algorithm', 'Algorithm used in making split on trading. Choices: trade_first, best_match', , 'trading', 'char', 'trade_first'
'id_loaded_to_cookie', 'ids that are saved to cookie.  Cookie expires every day.  User needs to login the next day.  The period_id will be incremented by 1', , 'screen', 'char', 'account_id, game_start_date, game_id, period_id, plant_id'
'price_curve_trd_inflat_factor', 'factor for adjusting carbon prices because of inflation, in % reduction/day since the first game_start_date', ' ', 'model', 'float', ' '
'system_completion_charge', 'charge if the trading was completed by system, fixed %', ' ', 'trading', 'float', '0.0'
*/

-- create unique index uidx_ce_config_para_name on ce_configuration_parameter(parameter_name);

drop table ce_carbon_market_price;

create table ce_carbon_market_price (
market_id varchar(30) not null, -- numeric id
market_name varchar(50) not null,
price_per_allowance_ton float, -- one permit is one ton of carbon emission
price_date date not null,
-- move to ce_configuration_parameter table
-- price_curve_trd_inflat_factor float, -- use for compensating the price curve trend
-- move to ce_configuration_parameter table
-- system_purchase_charge float, -- fee charged by system if system completes the transaction,
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (price_date, market_id)
);

-- create unique index uidx_ce_carbon_price_market_date on ce_carbon_market_price(market_id, price_date);

--

-- For now, we have three game types: Trading, Sequestration (max ca reductn for each question), 
-- Carbon Footprint (min ca footprint for each question/stage of life)
-- We can have multiple games running at the same time.
-- Each game has unique game_id, corresponding game_start_date, and changing game_period_id.
-- Every game in ce_game must belong to trading game_type.
drop table ce_game;

create table ce_game (
game_id varchar(30) not null, -- Gnn
game_name varchar(50) not null,
game_desc varchar(1000),
game_type varchar(30) not null, -- 'trading
game_start_date date not null,
market_id varchar(30),
game_period_id varchar(30), -- changing when game is in progress
optimization_level varchar(10), -- game, question
carbon_price_yesterday float, -- Save yesterday's carbon price here instead of ce_configuration_parameter table.  Different games will have different carbon price.
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (game_id)
);

create unique index uidx_ce_game_name on ce_game(game_name);

-- Records of top winners of each company model
drop table ce_winner_board;

Create Table ce_winner_board (
account_id varchar(30) not null, -- Annnnn
game_start_date date not null,
game_id varchar(30) not null, -- Gnn
plant_id varchar(30) not null, -- numeric id
normalized_costs_per_unit_percentile float, -- can have fraction
winner_rank_game int, -- ranking among the top winners of each model.
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id, game_start_date, game_id, plant_id)
);

-- For playing the scenario on each period of the carbon exchange game
-- Up to 5 records each for different scenario for each team in each of 5 periods
-- Production levels are the same for the teams playing the same model
-- Each team can view only its own records
drop table ce_carbonexch_scenario_play;

Create Table ce_carbonexch_scenario_play (
account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- numeric id
game_id varchar(30) not null, -- Gnn
game_start_date date not null,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn (concaternation of game_id + '-' + current_period)
period_scenario_id varchar(30) not null, -- an unique id, a period can have up to 5 scenarios, numeric id
period_scenario_desc varchar(1000), -- description of the scenario
scenario_choice varchar(30), -- 7 fixed choices: do_nothing, fix_prod_lvl_perform_upgd, fix_prod_lvl_buy_allowns, --fix_prod_buy_allowns_perf_upgd, decrease_emiss_only, --adj_tgt_sel_allowns_perf_upgd, sell_extra_allowns
period_start_prod_unit int,
period_start_emissions_ton int,
period_end_prod_unit int,
period_end_emissions_ton int,
ave_emissions_ton int,-- read from emission equation at average prod unit
allowances_allocated int,-- one permit per ton of emission
period_end_emissions_cap int,-- in tons
allowances_in_bank int, -- permits carried over to the next period
allowances_can_be_used int, -- permits can be used in this period
adjust_start_level_for_abatement int,
emissions_over_allocated_allowances int,
emissions_covered_by_abatement int,
emissions_covered_by_allowances int,
allowances_needed_from_trading int,
costs_trading float, -- trade permits for the excessive emissions
costs_abatement float, -- reduce excessive emission by abatement
costs_penalty float, -- pay penalty on the excessive emissions
costs_allocated_allowances float, -- monetary value of allocated allowance, can be zero for now
costs_allowances_liquidation float, -- liquidation of permits in reserve, negative value
costs_trades_in_queue float, -- market value of all pending trades in queue, zero in scenario
total_costs float, -- add six costs
total_costs_per_unit_period_tcpup float, -- add six costs divided by units at the end of each period
pick_this_scenario char (1),
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (period_scenario_id) -- concaternation of current_period + '-' + scenario_seq_num in ce_configuration_parameter
);

create unique index uidx_scpy_acct_sdate_gperd_scid on ce_carbonexch_scenario_play(account_id, game_start_date, game_period_id, period_scenario_id);

-- For showing each period of the carbon exchange game
-- 1 record for each team in each of 5 periods
-- Production levels are the same for the teams palying the same model
-- Each team can view only its own records
drop table ce_carbonexch_period_summary;

Create Table ce_carbonexch_period_summary (
account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- numeric id
game_id varchar(30) not null, -- Gnn
game_start_date date not null,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
period_desc varchar(1000), -- description of the period, including scenario picked
period_start_prod_unit int,
period_start_emissions_ton int,
period_end_prod_unit int,
period_end_emissions_ton int,
ave_emissions_ton int, -- read from emission equation at average prod_unit
allowances_allocated int, -- initial allocation at the beginning of each period, one permit per ton of emission
period_end_emissions_cap int, -- in tons
allowances_in_bank int, -- permits carried over to the next period
allowances_can_be_used int, -- permits can be used in this period
adjust_start_level_for_abatement int,
emissions_over_allocated_allowances int,
emissions_covered_by_abatement int,
emissions_covered_by_allowances int, -- cannot be greater than period_end_emission_quota or permits_allocated whatever is higher
allowances_needed_from_trading int,
costs_trading float, -- costs for permit_needed_from_trading
costs_abatement float, -- abatement costs
costs_penalty float, -- pay penalty on the excessive emissions
costs_allocated_allowances float, -- monetary value of allocated allowance, can be zero for now
costs_allowances_liquidation float, -- liquidation of permits in reserve, negative value
costs_trades_in_queue float, -- market value of all pending trades in queue
total_costs float, -- add six costs
total_costs_per_unit_period_tcpup float, -- add six costs divided by units at the end of each period, running total of market value on each period
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id, game_start_date, game_period_id)
);

/* obsolete same as total_costs_per_unit_period_tcpup on the last period
-- For showing team results over multiple periods.
-- also comparing the team with previous team with the same plant model
-- 1 record for each team only for the last period.
-- Records can be viewed by all teams
Create Table ce_carbonexch_team_period_summary (
Account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- numeric id
Game_id varchar(30) not null, -- Gnn
game_start_date date, 
game_period_id varchar(30) not null,
period_start_prod_unit number,
period_end_prod_unit number,
total_costs_period_tcp number, -- from ce_carbonexch_period_summary table
total_costs_per_unit_tcpu number, -- tcp divided by production units
-- next two attributes move to final_summary
-- adjusted_costs_per_unit_atcpu number, -- total_costs_per_unit_game after adjusted to the trend of price curve.
-- total_costs_per_unit_percentile number, -- compare with previous teams using the same
-- model and calculate in percentile
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null
)
*/

-- For showing all team results over all 5 periods
-- also comparing the all the teams across different models using their percentiles 
-- 1 record for each team.  Records can be viewed by all teams
drop table ce_carbonexch_final_summary;

Create Table ce_carbonexch_final_summary (
game_id varchar(30) not null, -- Gnn
game_start_date date not null,
account_id varchar(30) not null, -- Annnnn
plant_id varchar(30) not null, -- numeric id
total_costs_per_unit_period_tcpup float, -- total_costs_game divided by production units
adjusted_costs_per_unit_acpu float, -- total_costs_per_unit_period_tcpup after adjusted to the trend of price price.
adjusted_costs_per_unit_percentile_acpup float, -- compare with previous teams on the same model and calculate in percentile
normalized_costs_per_unit_percentile float, -- multiply acpup with weight_factor_for_ranking, calculated only for the winner of each group
rank_on_model int, -- ranking of each company model, for every team
rank_on_game int, -- ranking among the top winners of each model (percentile * weight_factor_for_ranking), if need 1 winner, rank only among winners of each group
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id, game_start_date, game_id)
);

-- Collection of team scores based on the same plant model.  Add the latest data points to the whole population samples
 drop table ce_plant_model_data_points;

Create table ce_plant_model_data_points (
plant_id varchar(30) not null, -- numeric id
account_id varchar(30) not null, -- Annnnn
game_id varchar(30) not null,
game_start_date date not null,
adjusted_costs_per_unit_acpu float,
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (plant_id, account_id, game_start_date)
);

-- Calculate team scores based on the same plant model
drop table ce_plant_model_statistics;

Create table ce_plant_model_statistics (
plant_id varchar(30) not null, -- numeric id
number_of_samples int,
average_of_atcpu float,
std_dev float,
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (plant_id)
);

--

-- Need Flow Chart table where Fnn is defined

-- Show all the transactions for the game from begin to end including splitted orders
-- 1 record per transaction or split
-- Each team can view only its own transactions
drop table ce_emission_trade;

Create Table ce_emission_trade (
transaction_id varchar(30) not null, -- Annnnn-GnnFnn-Snnnnn, split id: Annnnn-GnnFnn-Snnnnn-n, concatenation of account_id + '-' + game_id + '-' + flow_char_id + '-' + 'transaction_seq_num' in ce_configuration_parameter + '-' + split number
account_id varchar(30) not null, -- Annnnn
market_id varchar(30) not null, -- numeric id
game_id varchar(30) not null,
game_start_date date not null,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
buy_or_sell char(1), -- 'b', 's'
allowances_buy_amt int, -- always pos, cannot buy liability
allowances_buy_price float, -- buy any at this price or lower, can buy in multiple bids at different price
allowances_sell_amt int, 
allowances_sell_price float, -- all amt sold at this price only
executed_amt int,
executed_price float,
system_completion_charge float,
accept_partial char (1),
-- When an order is splitted, a new record is created with the untraded amount, status partial, and original_transaction_id
-- The current record is updated with traded amount, partially_completed status, and trade date
original_transaction_id varchar(30), -- for split trade only, keep track of the original transaction id.
submit_datetime date,
status varchar(10), -- new, queued, partial, partially_completed, totally_completed,
-- new, search immediately in the database for match.  If no match, enter queue for periodic executions.
-- queued, In queue for periodic execution.  Queue is FIFO.  If not the whole amt was executed, the remaining amt would stay in the queue for next execution.
-- partial, a partial amt has been executed.  The record is splitted into two.  The current record is updated with traded amount, partially_completed status, and trade date.  The new record has the untraded amt, status partial, and original_transaction_id.  It will stay in the queue for next execution.
-- partially_completed.  When a record is splitted, the current record is updated with traded amount, partially_completed status and trade date
-- totally_completed, the entire order has been executed, the last splitted record is marked with totally_completed
-- system_completed, any orders that are not completed during the normal trading time will be completed by the system with system_purchase_charge.
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id, game_start_date, game_period_id, transaction_id)
);

-- For calculating order execution price summary for each transactoin in each period
-- 1 record combines all the splits
-- Each team can view only its own transactions
drop table ce_emission_trade_details;

Create Table ce_emission_trade_details (
transaction_id varchar(30) not null, -- Annnnn-GnnFnn-Snnnnn, concatenation of account_id + '-' + game_id + '-' + flow_char_id + '-' + 'transaction_seq_num' in ce_configuration_parameter
account_id varchar(30) not null, -- Annnnn
market_id varchar(30) not null, -- numeric id
game_id varchar(30) not null,
game_start_date date not null,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
buy_or_sell char(1), -- 'b', 's'
allowances_buy_amt int, -- always pos, cannot buy liability
allowances_buy_price float, -- buy any at this price or lower, can buy in multiple bids at different price
allowances_sell_amt int, 
allowances_sell_price float, -- all amt sold at this price only
accept_partial char (1),
submit_datetime date,
-- save up to 5 different sub-transactions
transaction_id0 varchar(30), -- Lnnnnn-GnnFnn_Snnnnn-0
amt0_completed int,
amt0_price float,
transaction_id1 varchar(30),
amt1_completed int,
amt1_price float,
transaction_id2 varchar(30),
amt2_completed int,
amt2_price float,
transaction_id3 varchar(30),
amt3_completed int,
amt3_price float,
transaction_id4 varchar(30),
amt4_completed int,
amt4_price float,
average_buy_price float, -- total costs of all sub-transactions / total amount when completed
average_sell_price float, -- sell price could be lower if it was completed by system after the end of the period
system_completion_charge float,
remaining_amt_in_queue int,
queue_position int, -- when the order at the front of the queue is completed, queue_position will be changed
Status varchar(10), -- new, queued, partial, partially_completed, totally_completed, system_completed
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id, game_start_date, game_period_id, transaction_id)
);

-- For showing final execution summary of every transaction for all teams up to this point
-- Each team can view only its own transactions
drop table ce_emission_trade_team_summary;

Create Table ce_emission_trade_team_summary (
transaction_id varchar(30) not null, -- Annnnn-GnnFnn-Snnnnn, concatenation of account_id + '-' + game_id + '-' + flow_char_id + '-' + 'transaction_seq_num' in ce_configuration_parameter
account_id varchar(30) not null, -- Annnnn
market_id varchar(30) not null, -- numeric id
game_id varchar(30) not null,
game_start_date date not null,
game_period_id varchar(30) not null, -- a game can have many periods GnnPnn
buy_or_sell char(1), -- 'b', 's'
allowances_buy_amt int, -- always pos, cannot buy liability
allowances_buy_price float, -- buy any at this price or lower, can buy in multiple bids at different price
average_buy_price float, -- average price
allowances_sell_amt int, 
allowances_sell_price float, -- all amt sold at this price only
average_sell_price float,
system_completion_charge float,
submit_datetime date,
complete_datetime date,
Status varchar(10), -- new, queued, partial, partially_completed, totally_completed, system_completed
created_date date not null,
created_by varchar(30) not null,
updated_date date not null,
updated_by varchar(30) not null,
primary key (account_id, game_start_date, game_period_id, transaction_id)
);