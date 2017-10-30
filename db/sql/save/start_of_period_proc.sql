/*
Start of the period process (developer: Andrew) :
Tables/data to be set at the start of the period:
Ce_carbonexch_period_summary, 
ce_game.game_period_id, allow_play_scenario=’y’, allow_trade_allowances = ‘y’

- update game_period_id and carbon_price_last_perd, set allow_play_scenarios = ‘y’, allow_trade_allowances = ‘y’ in ce_game table
- create record in period_summary table for the next period.
  set the value of the following fields:
  account_id, plant_id, game_id, game_start_date, game_period_id,
  period_desc, period_start_prod_unit, period_start_emissions_ton,
  period_end_prod_unit, period_end_emissions_ton, ave_emissions_ton,
  allowances_allocated, emissions_over_allocated_allowances, period_end_emissions_cap, created_date,
  created_by, updated_date, updated_by 

Dynamic sql in template:
This is a template used in generating dynamic sql file.
The template is developed from stored procedure sql file (contains dynamic sql).
It contains the dynamic sql statements with variables enclosed 
in square brackets.  Each dynamic sql template has matching configuration file.
The configuration file contain values corresponding to the variables in
the dynamic sql statements.
At run time, the shell script updates the values in the configuration file.
It then generate the dynamic sql file by substituing variables in the template
with values from the configuration.  After that, it invokes mysql and runs
with the dynamic sql file.
This implementation is different from dynamic sql in general because
the shortcoming of mysql.  Mysql does not support string concatenation
outside of select statement.  So there is no way of constructing sql statement
outside of select statement.  So shell script is used for this purpose.
*/

drop procedure if exists ce_schema.start_of_period_proc;

delimiter //
-- p_game_start_date format YYYY-MM-DD
create procedure ce_schema.start_of_period_proc (
    in p_game_id varchar(30), 
    in p_cur_period_id varchar(30),
    in p_game_start_date varchar(30),
    in p_debugl integer
)
begin    
    declare v_case_error_float_str varchar(10) default '-9999.0';
    declare v_case_error_int_str varchar(10) default '-9999';
    declare v_case_error_str_str varchar(15) default 'INCORRECT_VALUE';
    declare v_fail_return integer default 1;
    declare v_success_return integer default 0;        
    
    declare v_sql varchar(65500);
    declare v_next_period_num integer;
    declare v_next_period_id varchar(30);
    declare v_game_start_date_str varchar(10);
    declare v_next_game_period_id varchar(30);
    declare v_cur_game_period_id varchar(30);
    declare v_game_period_one_id varchar(30);
    declare v_carbon_price_last_period float;
    declare v_emissions_cap_perd_perc float;
    declare v_allowns_allocated_perd_perc float;
    declare v_proc_name_start_of_perd varchar(30);
    
    declare v_fetch_finish integer;    
    declare exit handler for sqlexception select 'sqlexception encountered';
    declare continue handler for not found set v_fetch_finish = 1;    
    
    /*
    set v_sql = 
        'select date_format(''2016-09-26'', ''%Y-%m-%d'')';
    set @sql = v_sql;
    if p_debugl = 1
    then
        select concat('v_sql: ', v_sql);
    else
        prepare stmt from @sql;
        execute stmt;
        -- catch exception by exit handler
        deallocate prepare stmt;
    end if;
    
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_emissions_cap_perd_perc''';
    set @sql = v_sql;
    if p_debugl = 1
    then
        select concat('v_sql: ', v_sql);
    else
        prepare stmt from @sql;
        execute stmt;
        -- catch exception by exit handler
        deallocate prepare stmt;
    end if;
    set v_emissions_cap_perd_perc = @out1 + 0.0;
        
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_allowns_allocated_perd_perc''';
    set @sql = v_sql;
    if p_debugl = 1
    then
        select concat('v_sql: ', v_sql);
    else
        prepare stmt from @sql;
        execute stmt;
        -- catch exception by exit handler
        deallocate prepare stmt; 
    end if;
    set v_allowns_allocated_perd_perc = @out1 + 0.0;
        
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_proc_name_start_of_perd''';
    set @sql = v_sql;
    if p_debugl = 1
    then
        select concat('v_sql: ', v_sql);
    else
        prepare stmt from @sql;
        execute stmt;
        -- catch exception by exit handler
        deallocate prepare stmt;
    end if; 
    set v_proc_name_start_of_perd = @out1;
        
    set @out1 = '';
    set @out2 = '';
    set @out3 = '';
    set v_sql = 
        'select substr(game_period_id, locate(game_period_id, ''-'') + 1) + 1,
        date_format(game_start_date, ''%Y-%m-%d''), carbon_price_last_perd
        into @out1, @out2, @out3
        from ce_game
        where game_id =''1''';
    set @sql = v_sql;
    if p_debugl = 1
    then
        select concat('v_sql: ', v_sql);
    else
        prepare stmt from @sql;
        execute stmt;
        -- catch exception by exit handler
        deallocate prepare stmt;
    end if;
    set v_next_period_num = @out1 + 0; 
    set v_game_start_date_str = @out2;
    set v_carbon_price_last_period = @out3 + 0.0;
        
    set v_next_period_id = cast(v_next_period_num as char); 
    set v_next_game_period_id = p_game_id + '-' + v_next_period_id;
    set v_cur_game_period_id = p_game_id + '-' + p_cur_period_id;
    set v_game_period_one_id = p_game_id + '-1';
    */
    
    -- update ce_game record
    set v_sql = 
        'update ce_game
        set game_period_id = ''1-2'',
        allow_play_scenarios = ''y'',
        allow_trade_allowances = ''y''
        where game_id = ''1''';
    set @sql = v_sql;
    if p_debugl = 1
    then
        select concat('v_sql: ', v_sql);
    else
        prepare stmt from @sql;
        execute stmt;
        -- catch exception by exit handler
        deallocate prepare stmt;
        commit;
    end if;
    
    -- insert 1 record for each account playing in the next period
    -- including the first one
    set v_sql =
    'insert into ce_carbonexch_period_summary
        (account_id, plant_id, game_id,
        game_start_date, game_period_id, period_desc,
        period_start_prod_unit, period_start_emissions_ton, period_end_prod_unit,
        period_end_emissions_ton, allowances_allocated, period_end_emissions_cap,
        allowances_in_bank, initial_allowns_can_be_used, current_carbon_price,
        allowns_lvl_corr_to_cur_price, tgt_allowns_lvl_for_abatmt, emissns_ov_allowns_can_be_used,
        emissns_covered_by_abatmt, emissns_covered_by_allowns, allowns_needed_fr_trading,
        costs_trading, costs_abatement, costs_penalty,
        costs_allocated_allowances, costs_allowances_in_bank, costs_trades_in_queue,
        total_costs, totals_per_unit_period_tcpup, created_date,
        created_by, updated_date, updated_by)
    select m.account_id, m.plant_id, m.game_id,
        date_format(''2016-09-26'', ''%Y-%m-%d''), ''1-2'', null,
        case when g.game_period_id = ''1-1'' then p.initial_production_unit
             else (s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100))
        end case, 
        case when m.plant_id = 1 then 0.7 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.431)
             when m.plant_id = 2 then 1.2 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.453)
             when m.plant_id = 3 then 0.8 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.299)
             when m.plant_id = 4 then 1.0 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.144)
             when m.plant_id = 5 then 1.12 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.178)
             else -9999.0
        end case,
        case when g.game_period_id = ''1-1'' then p.initial_production_unit
             else (s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100))
        end case, 
        case when m.plant_id = 1 then 0.7 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.431)
             when m.plant_id = 2 then 1.2 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.453)
             when m.plant_id = 3 then 0.8 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.299)
             when m.plant_id = 4 then 1.0 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.144)
             when m.plant_id = 5 then 1.12 * pow((s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100)), 1.178)
             else -9999.0
        end case, 
        (s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100) * 95.0/100), 
        (s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100) * 95.0/100),
        0, (s.period_end_prod_unit * (1 + p.stepwise_prod_inc_every_perd_perc/100) * 95.0/100), 
        13.5,
        null, null, null,
        null, null, null,
        null, null, null,
        null, null, null,
        null, null, now(), 
        ''proc_start_of_period'', now(), ''proc_start_of_period''       
    from ce_account_company_plant_map m, ce_carbonexch_period_summary s, ce_plant p, ce_game g
    where m.game_id = p_game_id
        and m.account_id = s.account_id
        and m.plant_id = s.plant_id
        and m.game_id = s.game_id
        and s.game_period_id = ''1-1''
        and s.game_start_date = date_format(''2016-09-26'', ''%Y-%m-%d'')
        and m.plant_id = p.plant_id
        and m.game_id = g.game_id';
    set @sql = v_sql;
    if p_debugl = 1
    then
        select concat('v_sql: ', v_sql);
    else
        prepare stmt from @sql;
        execute stmt;
        -- catch exception by exit handler
        deallocate prepare stmt;
        commit;
    end if;        
        
end //
delimiter ;

grant execute on procedure ce_schema.start_of_period_proc to 'andrewk'@'localhost';

-- For testing
mysql> call ce_schema.start_of_period_proc('1', '1', '2016-09-26', 1);
