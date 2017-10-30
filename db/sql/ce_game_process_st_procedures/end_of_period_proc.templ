/*
End of the period process (developer: Andrew) :
Tables/data to be set at the end of the period:
Ce_carbonexch_scenario_play, ce_carbonexch_period_summary, ce_emissions_trade_combined, ce_emissions_trade_team_summary, 
Ce_game.allow_play_scenario=’n’, allow_trade_allowances=’n’

- consolidate multiple emissions_trade records of the same transaction id to one single emissions_trade_combined record. done
- copy the following fields from emissions_trade_combined to trade_team_summary:
  transaction_id, account_id, market_id, game_id, game_start_date, game_period_id, buy_or_sell, allowances_buy_amt, allowances_buy_price, average_buy_price,
  allowances_sell_amt, allowances_sell_price, average_sell_price, system_completion_charge, submit_datetime, complete_datetime, status
- copy the following fields from the selected scenario in scenario_play to period_summary:
  adjust_start_level_for_abatment, emissions_covered_by_abatement,
  emissions_covered_by_allowances, allowances_needed_from_trading,
  costs_allocated_allowances (use carbon_price_last_perd), costs_trading (see below), costs_abatement, costs_penalty,  costs_allowances_liquidation (use carbon_price_last_perd), costs_trades_in_queue (0), total_costs (see below)
- calculate costs_trading, total_costs, and total_costs_per_unit_period
  costs_trading = (1 * average_buy_price * allowances_buy_amt + system_completion_charge) or (-1 * average_sell_price * allowances_sell_amt + system_completion_charge)
  calculate total_costs = costs_allocated_allowances + costs_trading + costs_abatement + costs_penalty + costs_allowances_liquidation + costs_trades_in_queue
  calculate total_costs_per_unit_period = total_costs/period_ave_prod_unit
- Set ce_game.allow_play_scenarios = ‘n’, allow_trade_allowances = ‘n’. 
*/

delimiter //
create procedure end_of_period_proc (
    in p_game_id varchar(30), 
    in p_cur_period_id varchar(30),
    in p_game_start_date varchar(30) --YYYY-MM-DD
)
begin
    declare exit handler for sqlexception select 'sqlexception encountered';
    declare v_fetch_finish integer;
    declare continue handler for not found set v_fetch_finish = 1;
    
    declare v_case_error_float_str varchar(10) = '-9999.0';
    declare v_case_error_int_str varchar(10) = '-9999';
    declare v_case_error_str_str varchar(10) = 'INCORRECT_VALUE';
    declare v_fail_return number = 1;
    declare v_success_return number = 0;    
    
    declare v_sql varchar(65500);
    declare v_game_id varchar(30);
    declare v_next_period_num number;
    declare v_next_period_id varchar(30);
    declare v_game_start_date_str varchar(8);
    declare v_game_period_id varchar(30);
    declare v_carbon_price_last_period float;
    declare v_emissions_cap_perd_perc float;
    declare v_allowns_allocated_perd_perc float;
    
    /*
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_emissions_cap_perd_perc''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_emissions_cap_perd_perc = @out1 + 0.0;
        
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_allowns_allocated_perd_perc''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_allowns_allocated_perd_perc = @out1 + 0.0; 
    */
    
    set v_sql = 
        'select to_date(''' + p_period_start_date + ''' from dual';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    -- catch exception by exit handler
    deallocate prepare stmt;    
        
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_proc_name_end_of_perd''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_proc_name_end_of_perd = @out1;  
    
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_penalty_excd_emiss_cap_perc''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_penalty_excd_miss_cap_perc = @out1 + 0.0;     
        
    set @out1 = '';
    set @out2 = '';
    set @out3 = '';
    set v_sql = 
        'select substr(game_period_id, locate(game_period_id, ''-'') + 1) + 1,
        date_format(game_start_date, ''%Y-%m-%d''), carbon_price_last_perd
        into @out1, @out2, @out3
        from ce_game
        where game_id =''' + p_game_id + '''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_next_period_num = @out1 + 0; 
    set v_game_start_date_str = @out2;
    set v_carbon_price_last_period = @out3 + 0.0;
        
    set v_next_period_id = cast(v_next_period_num as varchar); 
    set v_next_game_period_id = p_game_id + '-' + v_next_period_id;
    set v_cur_game_period_id = p_game_id + '-' + p_cur_period_id);
    set v_game_period_one_id = p_game_id + '-1';
    
    -- Test if any transaction has more than 4 splits
    set @out1 = '';
    set v_sql = 
        'select count(1) into @out1
        from ce_emissions_trade
        where game_period_id = ''' + v_cur_game_period_id '''
        and transaction_id like '''%_5'''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_count_gr_than_4splits = @out1 + 0;  
    
    
    if v_count_gr_than_4splits > 0
    then
        select 'Transactions have more than 4 splits: ' + char(v_count_gr_than_4splits);
        return v_fail_return;
    end if;
    
    -- insert 1 record for each account playing in the next period
    -- including the first one
    set v_sql =
    'insert into ce_emissions_trade_combined
        (transaction_id, account_id, market_id, --1
        game_start_date, game_id, game_perid_id, --4
        buy_or_sell, allowances_sell_amt, allowances_sell_price, accept_partial, --7
        submit_datetime,
        completed_datetime,
        transaction_id0, amt0_completed, --13
        amt0_price, match_with_transaction0, --15
        sys_completn_charge0_perc,
        transaction_id1, amt1_completed, amt1_price, match_with_transaction1, --18
        sys_completn_charge1_perc,
        transaction_id2, amt2_completed, amt2_price, match_with_transaction2, --23
        sys_completn_charge2_perc,
        transaction_id3, amt3_completed, amt3_price, match_with_transaction3, --28
        sys_completn_charge3_perc,
        transaction_id4, amt4_completed, amt4_price, match_with_transaction4, --33
        sys_completn_charge4_perc,
        average_buy_price,
        remaining_amt_in_queue, queue_position, status, --39
        created_date, created_by, updated_date, --42
        updated_by)
    select transaction_id, account_id, market_id --1
        game_start_date, game_id, game_period_id --4
        buy_of_sell, allowances_sell_amt, allowances_sell_price, accept_partial, --7
        submit_datetime, 
        (select max(updated_date) from ce_emissions_trade t5
        where t5.transaction_id like t_transaction_id + ''%'') as completed_datetime,        
        transaction_id as transaction_id0, executed_amt as amt0_completed, --13
        executed_price as amt0_price, match_with_transaction as match_with_transaction0, --15
        sys_completn_charge_perc as sys_completn_charge0_perc,
        (select transaction_id from ce_emissions_trade t11
        where t11.transction_id = t.transaction_id + ''-1'') as transaction_id1, --18
        (select executed_amt from ce_emissions_trade t12
        where t12.transction_id = t.transaction_id + ''-1'') as amt1_completed,
        (select executed_price from ce_emissions_trade t13
        where t13.transction_id = t.transaction_id + ''-1'') as amt1_price,
        (select match_with_transaction from ce_emissions_trade t14
        where t14.transction_id = t.transaction_id + ''-1'') as match_with_transaction1,
        (select sys_completn_charge_perc from ce_emissions_trade t15
        where t15.transction_id = t.transaction_id + ''-1'') as sys_completn_charge1_perc,
        (select transaction_id from ce_emissions_trade t21
        where t21.transction_id = t.transaction_id + ''-2'') as transaction_id2, --23
        (select executed_amt from ce_emissions_trade t22
        where t22.transction_id = t.transaction_id + ''-2'') as amt2_completed,
        (select executed_price from ce_emissions_trade t23
        where t23.transction_id = t.transaction_id + ''-2'') as amt2_price,
        (select match_with_transaction from ce_emissions_trade t24
        where t24.transction_id = t.transaction_id + ''-2'') as match_with_transaction2,
        (select sys_completn_charge_perc from ce_emissions_trade t25
        where t25.transction_id = t.transaction_id + ''-2'') as sys_completn_charge2_perc,
        (select transaction_id from ce_emissions_trade t31
        where t31.transction_id = t.transaction_id + ''-3'') as transaction_id3, --28
        (select executed_amt from ce_emissions_trade t32
        where t32.transction_id = t.transaction_id + ''-3'') as amt3_completed,
        (select executed_price from ce_emissions_trade t33
        where t33.transction_id = t.transaction_id + ''-3'') as amt3_price,
        (select match_with_transaction from ce_emissions_trade t34
        where t34.transction_id = t.transaction_id + ''-3'') as match_with_transaction3,
        (select sys_completn_charge_perc from ce_emissions_trade t35
        where t35.transction_id = t.transaction_id + ''-3'') as sys_completn_charge3_perc,
        (select transaction_id from ce_emissions_trade t41
        where t41.transction_id = t.transaction_id + ''-4'') as transaction_id4, --33
        (select executed_amt from ce_emissions_trade t42
        where t42.transction_id = t.transaction_id + ''-4'') as amt4_completed,
        (select executed_price from ce_emissions_trade t43
        where t43.transction_id = t.transaction_id + ''-4'') as amt4_price,
        (select match_with_transaction from ce_emissions_trade t44
        where t44.transction_id = t.transaction_id + ''-4'') as match_with_transaction4,
        (select sys_completn_charge_perc from ce_emissions_trade t45
        where t45.transction_id = t.transaction_id + ''-4'') as sys_completn_charge4_perc,
        null as average_sell_price, 
        0 as remaining_amt_in_queue, null as queue_position, ''totally_completed'' as status, --39
        created_date, created_by, updated_date, --42
        updated_by
    from ce_emissions_trade t
    where game_period_id = v_cur_game_period_id
    and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')
    and buy_or_sell = ''s''
    and transaction_id not like ''%-_''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;  
    commit;
    
    set v_sql =
    'insert into ce_emissions_trade_combined
        (transaction_id, account_id, market_id, --1
        game_start_date, game_id, game_perid_id, --4
        buy_or_sell, allowances_buy_amt, allowances_buy_price, accept_partial, --7
        submit_datetime, 
        completed_datetime, 
        transaction_id0, amt0_completed, --13
        amt0_price, match_with_transaction0, --15
        sys_completn_charge0_perc,
        transaction_id1, amt1_completed, amt1_price, match_with_transaction1, --18
        sys_completn_charge1_perc,
        transaction_id2, amt2_completed, amt2_price, match_with_transaction2, --23
        sys_completn_charge2_perc,
        transaction_id3, amt3_completed, amt3_price, match_with_transaction3, --28
        sys_completn_charge3_perc,
        transaction_id4, amt4_completed, amt4_price, match_with_transaction4, --33
        sys_completn_charge4_perc,
        average_buy_price, --32
        remaining_amt_in_queue, queue_position, status, --39
        created_date, created_by, updated_date, --42
        updated_by)
    select transaction_id, account_id, market_id --1
        game_start_date, game_id, game_period_id --4
        buy_of_sell, allowances_buy_amt, allowances_buy_price, accept_partial, --7
        submit_datetime, 
        (select max(updated_date) from ce_emissions_trade t5
        where t5.transaction_id like t_transaction_id + ''%'') as completed_datetime,
        transaction_id as transaction_id0, executed_amt as amt0_completed, --13
        executed_price as amt0_price, match_with_transaction as match_with_transaction0, --15
        sys_completn_charge_perc as sys_completn_charge0_perc,        
        (select transaction_id from ce_emissions_trade t11
        where t11.transction_id = t.transaction_id + ''-1'') as transaction_id1, --18
        (select executed_amt from ce_emissions_trade t12
        where t12.transction_id = t.transaction_id + ''-1'') as amt1_completed,
        (select executed_price from ce_emissions_trade t13
        where t13.transction_id = t.transaction_id + ''-1'') as amt1_price,
        (select match_with_transaction from ce_emissions_trade t14
        where t14.transction_id = t.transaction_id + ''-1'') as match_with_transaction1,
        (select sys_completn_charge_perc from ce_emissions_trade t15
        where t15.transction_id = t.transaction_id + ''-1'') as sys_completn_charge1_perc,        
        (select transaction_id from ce_emissions_trade t21
        where t21.transction_id = t.transaction_id + ''-2'') as transaction_id2, --23
        (select executed_amt from ce_emissions_trade t22
        where t22.transction_id = t.transaction_id + ''-2'') as amt2_completed,
        (select executed_price from ce_emissions_trade t23
        where t23.transction_id = t.transaction_id + ''-2'') as amt2_price,
        (select match_with_transaction from ce_emissions_trade t24
        where t24.transction_id = t.transaction_id + ''-2'') as match_with_transaction2,
        (select sys_completn_charge_perc from ce_emissions_trade t25
        where t25.transction_id = t.transaction_id + ''-2'') as sys_completn_charge2_perc,         
        (select transaction_id from ce_emissions_trade t31
        where t31.transction_id = t.transaction_id + ''-3'') as transaction_id3, --28
        (select executed_amt from ce_emissions_trade t32
        where t32.transction_id = t.transaction_id + ''-3'') as amt3_completed,
        (select executed_price from ce_emissions_trade t33
        where t33.transction_id = t.transaction_id + ''-3'') as amt3_price,
        (select match_with_transaction from ce_emissions_trade t34
        where t34.transction_id = t.transaction_id + ''-3'') as match_with_transaction3,
        (select sys_completn_charge_perc from ce_emissions_trade t35
        where t35.transction_id = t.transaction_id + ''-3'') as sys_completn_charge3_perc,         
        (select transaction_id from ce_emissions_trade t41
        where t41.transction_id = t.transaction_id + ''-4'') as transaction_id4, --33
        (select executed_amt from ce_emissions_trade t42
        where t42.transction_id = t.transaction_id + ''-4'') as amt4_completed,
        (select executed_price from ce_emissions_trade t43
        where t43.transction_id = t.transaction_id + ''-4'') as amt4_price,
        (select match_with_transaction from ce_emissions_trade t44
        where t44.transction_id = t.transaction_id + ''-4'') as match_with_transaction4,
        (select sys_completn_charge_perc from ce_emissions_trade t45
        where t45.transction_id = t.transaction_id + ''-4'') as sys_completn_charge4_perc,         
        null as average_buy_price, 
        0 as remaining_amt_in_queue, null as queue_position, ''totally_completed'' as status, --39
        created_date, created_by, updated_date, --42
        updated_by
    from ce_emissions_trade t
    where game_period_id = v_cur_game_period_id
    and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')    
    and buy_or_sell = ''b''
    and transaction_id not like ''%-_''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;    
    commit;
    
    -- copy each transaction record from emissions_trade_combined to trade_team_summary:
    set v_sql =
    'insert into ce_emissions_trade_team_summary
        (transaction_id, account_id, market_id,
        game_start_date, game_id, game_period_id,
        buy_or_sell, allowances_buy_amt, allowances_buy_price,
        average_buy_price, 
        allowances_sell_amt, allowances_sell_price,
        average_sell_price, 
        sys_completn_charge_perc, submit_datetime,
        complete_datetime, status, created_date,
        created_by, updated_date, updated_by)
    select transaction_id, account_id, market_id,
        game_start_date, game_id, game_period_id,
        buy_or_sell, allowamces_buy_amt, allowances_buy_price,
        ((ifnull(amt0_completed, 0) * ifnull(amt0_price, 0) * (1 + ifnull(sys_completn_charge0_perc, 0)) + ifnull(amt1_completed, 0) * ifnull(amt1_price, 0) * (1 + ifnull(sys_completn_charge1_perc, 0)) + ifnull(amt2_completed, 0) * ifnull(amt2_price, 0) * (1 + ifnull(sys_completn_charge2_perc, 0)) + ifnull(amt3_completed, 0) * ifnull(amt3_price, 0) * (1 + ifnull(sys_completn_charge3_perc, 0)) + ifnull(amt4_completed, 0) * ifnull(amt4_price, 0) * (1 + ifnull(sys_completn_charge4_perc, 0))) / (isnull(amt0_completed, 0) + isnull(amt1_completed, 0) + isnull(amt2_completed, 0) + isnull(amt3_completed, 0) + isnull(amt4_completed, 0))) as average_buy_price,
        null as allowances_sell_amt, null as allowances_sell_price,
        null as average_sell_price
        null as sys_completn_charge_perc, submit_datetime,
        completed_datetime, ''totally_completed'', now(),
        v_proc_name_end_of_perd, now(), v_proc_name_end_of_perd
    from ce_emissions_trade_combined
    where buy_or_sell = ''b''
        and game_period_id = v_cur_game_period_id
        and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;         
    commit;
    
    set v_sql =
    'insert into ce_emissions_trade_team_summary
        (transaction_id, account_id, market_id,
        game_start_date, game_id, game_period_id,
        buy_or_sell, allowances_buy_amt, allowances_buy_price,
        average_buy_price, 
        allowances_sell_amt, allowances_sell_price,
        average_sell_price, 
        sys_completn_charge_perc, submit_datetime,
        complete_datetime, status, created_date,
        created_by, updated_date, updated_by)
    select transaction_id, account_id, market_id,
        game_start_date, game_id, game_period_id,
        buy_or_sell, null as allowamces_buy_amt, null as allowances_buy_price,
        null as average_buy_price,
        allowances_sell_amt, allowances_sell_price;
        ((ifnull(amt0_completed, 0) * ifnull(amt0_price, 0) * (1 - ifnull(sys_completn_charge0_perc, 0)) + ifnull(amt1_completed, 0) * ifnull(amt1_price, 0) * (1 - ifnull(sys_completn_charge1_perc, 0)) + ifnull(amt2_completed, 0) * ifnull(amt2_price, 0) * (1 - ifnull(sys_completn_charge2_perc, 0)) + ifnull(amt3_completed, 0) * ifnull(amt3_price, 0) * (1 - ifnull(sys_completn_charge3_perc, 0)) + ifnull(amt4_completed, 0) * ifnull(amt4_price, 0) * (1 - ifnull(sys_completn_charge4_perc, 0))) / (isnull(amt0_completed, 0) + isnull(amt1_completed, 0) + isnull(amt2_completed, 0) + isnull(amt3_completed, 0) + isnull(amt4_completed, 0))) as average_sell_price,
        null as sys_completn_charge_perc, submit_datetime,
        completed_datetime, ''totally_completed'', now(),
        v_proc_name_end_of_perd, now(), v_proc_name_end_of_perd
    from ce_emissions_trade_combined
    where buy_or_sell = ''s''
        and game_period_id = v_cur_game_period_id
        and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')'; 
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;         
    commit;        
    
    -- Check if all the accounts have picked their scenario
    set @out1 = '';
    set v_sql = 
        'select count(1) into @out1
        from ce_carbonexch_scenario_play
        where game_period_id = ''' + v_cur_game_period_id '''
        and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')        
        and scenario_choice = ''y''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_count_selected_scenarios = @out1 + 0;  
    
    set @out1 = '';
    set v_sql = 
        'select count(1) into @out1
        from ce_account_company_plant_map
        where game_id = ''' + p_game_id '''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_count_teams = @out1 + 0;    
    
    if v_count_select_scenario <> v_count_teams
    then
        select 'Not every team select its scenario.  Num of teams: ' + char(v_count_teams) + ' Num of selected scenarios: ' + char(v_count_selected_scenarios);
        return v_fail_return;
    end if;    
    
    -- Combine scenario records of each team from scenaio_play and copy to period_summary.  Calculate costs_trading: 
    set v_sql =
    'insert into ce_carbonexch_period_summary
        (account_id, plant_id, game_id,
        game_start_date, game_period_id, period_desc,
        period_start_prod_unit, period_start_emissions_ton, period_end_prod_unit,
        period_end_emissions_ton, allowances_allocated, period_end_emissions_cap,
        allowances_in_bank, initial_allowns_can_be_used, current_carbon_price,
        allowns_lvl_corr_to_cur_price, tgt_allowns_lvl_for_abatmt, emissns_ov_allowns_can_be_used,
        emissns_covered_by_abatmt, emissns_covered_by_allowns, allowns_needed_fr_trading, 
        actual_allowns_traded,
        costs_trading, costs_abatement, costs_penalty,
        costs_allocated_allowances, costs_allowances_in_bank, costs_trades_in_queue,
        total_costs, total_costs_per_unit_period_tcpup, created_date,
        created_by, updated_date, updated_by)
    select account_id, plant_id, game_id,
        game_start_date, game_period_id, null as period_desc,
        period_start_prod_unit, period_start_emissions_ton, period_end_prod_unit,
        period_end_emissions_ton, allowances_allocated, period_end_emissions_cap,
        allowances_in_bank, initial_allowns_can_be_used, current_carbon_price,
        null as allowns_lvl_corr_to_cur_price, null as tgt_allowns_lvl_for_abatmt, emissns_ov_allowns_can_be_used,
        emissns_covered_by_abatmt, emissns_covered_by_allowns, allowns_needed_fr_trading,
        (select sum(allowances_buy_amt - allowances_sell_amt)
        from ce_emissions_trade_team_summary s1
        where s1.account_id = p.account_id
        and s1.game_period_id = p.game_period_id) as actual_allowns_traded,
        (select sum(total_per_trans) from
        (select (1 * allowances_buy_amt * average_buy_price) as total_per_trans 
        from ce_emissions_trade_team_summary s
        where s.account_id = p.account_id
        and s.game_period_id = p.game_period_id
        and s.buy_or_sell = ''b''
        union all
        select ((-1) * allowances_sell_amt * average_sell_price) as total_per_trans 
        from ce_emissions_trade_team_summary s
        where s.account_id = p.account_id
        and s.game_period_id = p.game_period_id
        and s.buy_or_sell = ''s''))
        as costs_trading, 
        costs_abatement, costs_penalty,
        costs_allocated_allowances, costs_allowances_in_bank, 0 as costs_trades_in_queue,
        null as total_costs, null as total_costs_per_unit_period_tcpup, created_date,
        created_by, updated_date, updated_by
    from ce_carbonexch_scenario_play p
    where p.game_period_id = ''' + v_cur_game_period_id + char(10 using utf8) +
        'and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')    
        and scenario_choice = ''y''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;         
    commit;         
        
    -- 1 unit of allowance is equivalent to 1 ton of emissions
    -- Add penalty if emissns_ov_allowns_can_be_used > 
    -- (emissns_covered_by_abatmt + 
    -- sum(allowances_buy_amt - allowances_sell_amt))
    set v_sql =
    'update ce_carbonexch_period_summary
    set costs_penalty = (emissns_ov_allowns_can_be_used - emissns_covered_by_abatmt - actual_allowns_traded) * v_penalty_excd_miss_cap_perc
    where game_period_id = ''' + v_cur_game_period_id + char(10 using utf8) +
        'and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')    
        and emissns_ov_allowns_can_be_used > emissns_covered_by_abatmt + actual_allowns_traded';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;         
    commit;       
    
    -- Calculate total_costs, and total_costs_per_unit_period
    set v_sql =
    'update ce_carbonexch_period_summary
    set total_costs = costs_trading + costs_abatement + costs_penalty + costs_allocated_allowances + costs_allowances_in_bank + costs_trades_in_queue,
    total_costs_per_unit_period_tcpup = (costs_trading + costs_abatement + costs_penalty + costs_allocated_allowances + costs_allowances_in_bank + costs_trades_in_queue) / period_start_prod_unit
    where game_period_id = ''' + v_cur_game_period_id + '''
        and game_start_date = to_date(''' + p_game_start_date + ''', ''YYYY-MM-DD'')    
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;         
    commit;    
    
    -- update ce_game record
    set v_sql = 
        'update ce_game
        set allow_play_scenarios = ''n'',
        allow_trade_allowances = ''n''
        where game_id = ''' + p_game_id + '''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    commit;    
        
end //
delimiter ;