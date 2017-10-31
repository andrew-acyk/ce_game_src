delimiter //
create procedure recur_proc_find_sell_orders (
    in p_transaction_id varchar(30), 
    in p_buy_amt integer,
    in p_buy_price float
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
    
    -- The temp_ce_read_buy_order table is used for avoiding update of the same buy record holding by the cur_buy cursor.
    -- The cur_buy cursor is reading from the temp_ce_read_buy_order table but the buy record is updating to 
    -- the ce_emissions_trade table.
    -- Similarly the temp_ce_committed_split_orders is used for avoiding update of the same sell record holding by the cur_sell cursor.
    -- The cur_sell cursor is reading from the ce_emissions_trade table but all the sell records are writing to
    -- the temp_ce_committed_split_orders table.  The records are later copied over to the ce_emissions_trade table after
    -- the cur_sell cursor is closed.
    declare cur_sell cursor for 
        select a.* 
        from CE_EMISSIONS_TRADE a, CE_GAME b
        where a.game_id = b.game_id
        and a.game_start_date = b.game_start_date
        and a.game_period_id = b.game_period_id
        and a.buy_or_sell = ''s''
        and a.status in (''queued'', ''partial''
        order by submit_datetime asc;
    
    declare v_sql varchar(65500);
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
        
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_proc_name_start_of_perd''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_proc_name_recurrent_job = @out1;        
        
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
    
    -- update ce_game record
    set v_sql = 
        'update ce_game
        set game_period_id = ''' + v_next_game_period_id + ''',
        allow_play_scenarios = ''y'',
        allow_trade_allowances = ''y''
        where game_id = ''' + p_game_id + '''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    commit;
    
    -- update allow_play_scenario = 'n', allow_trade_allowances = 'n'
    set v_sql =
        'update ce_game
        set allow_play_scenario = ''n'',
        allow_trade_allowances = ''n''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;        
    commit;
    
    -- copy buy orders to temp_ce_read_by_order table
    set v_sql =
        'truncate table temp_ce_read_buy_order';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;        
    commit;
    
    set v_sql =
        'insert into temp_ce_read_buy_order
        select a.* 
        from CE_EMISSIONS_TRADE a, CE_GAME b
        where a.game_id = b.game_id
        and a.game_start_date = b.game_start_date
        and a.game_period_id = b.game_period_id
        and a.buy_or_sell = ''b''
        and a.status in (''queued'', ''partial'')';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;        
    commit; 
    */
    
    set v_remaining_buy_amt = p_buy_amt;
    open cur_sell;
    sell_loop: loop
        fetch cur_sell into v_sell_transaction_id, v_sell_account_id, v_sell_market_id,
            v_sell_game_start_date, v_sell_game_id, v_sell_game_period_id,
            v_sell_buy_or_sell, v_sell_allowances_buy_amt, v_sell_allowances_buy_price,
            v_sell_allowances_sell_amt, v_sell_allowances_sell_price, v_sell_executed_amt,
            v_sell_executed_price, v_sell_match_with_transaction, v_sell_sys_completn_charge_perc,
            v_sell_accept_partial, v_sell_original_transaction_id, v_sell_submit_datetime,
            v_sell_status, v_sell_comment; 
        if v_fetch_finish = 1 then leave sell_loop end if;
        if v_sell_allowances_sell_price <= p_buy_price
        then
            if v_remaining_buy_amt > v_sell_allowances_sell_amt
            then
                -- write 1 totally completed sell order
                set v_sql = 
                    'insert into temp_ce_committed_split_orders
                        (transaction_id, account_id, market_id,
                        game_start_date, game_id, game_period_id,
                        buy_or_sell, allowances_buy_amt, allowances_buy_price,
                        allowances_sell_amt, allowances_sell_price, executed_amt,
                        executed_price, match_with_transaction, sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime,
                        status, comment, created_date,
                        created_by, updated_date, updated_by)
                    select ''' + v_sell_transaction_id + ''', ''' + v_sell_account_id + ''', market_id,
                        game_start_date, game_id, game_period_id, 
                        ''s'', null, null, ' +
                        v_sell_allowances_sell_amt + ', ' + v_sell_allowances_sell_price + ', ' + v_sell_allowances_sell_amt + ', ' +
                        v_sell_allowances_sell_price + ', ''' + p_transaction_id + ''', null,
                        ''y'', ''' + v_sell_transaction_id + ''', date_format(''' + v_sell_submit_datetime_str + ''', ''%y-%m-%d %H:%m:%S''), 
                        ''totally_completed'', null, now(),
                        ''' + v_proc_name_recurrent_job + ''', now(), ''' + v_proc_name_recurrent_job + '''                   
                    from ce_emissions_trade
                    where transaction_id = ''' + p_transaction_id + '''';
                set @sql = v_sql;
                prepare stmt from @sql;
                execute stmt;
                deallocate prepare stmt;                
                -- write 1 partially completed buy order where buy_amt = sell_amt 
                set v_sql = 
                    'insert into temp_ce_committed_split_orders
                        (transaction_id, account_id, market_id,
                        game_start_date, game_id, game_period_id,
                        buy_or_sell, allowances_buy_amt, allowances_buy_price,
                        allowances_sell_amt, allowances_sell_price, executed_amt,
                        executed_price, match_with_transaction, sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime,
                        status, comment, created_date,
                        created_by, updated_date, updated_by)
                    select ''' + p_transaction_id ?? + ''', account_id, market_id,
                        game_start_date, game_id, game_period_id, 
                        buy_or_sell, allowances_buy_amt, allowances_buy_price, allowances_sell_amt, allowances_sell_price, ' + v_sell_allowances_sell_amt + ', ' +
                        v_sell_allowances_sell_price + ', ''' + v_sell_transaction_id + ''', sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime, 
                        ''partially_completed'', comment, now(),
                        ''' + v_proc_name_recurrent_job + ''', now(), ''' + v_proc_name_recurrent_job + '''                   
                    from ce_emissions_trade
                    where transaction_id = ''' + p_transaction_id + '''';
                set @sql = v_sql;
                prepare stmt from @sql;
                execute stmt;
                deallocate prepare stmt;                
                -- remaining = buy_amt - sell_amt
                set v_remaining_buy_amt = v_remaining_buy_amt - v_sell_allowances_sell_amt
            elsif v_remaining_buy_amt = v_sell_allowances_sell_amt
            then
                -- write 1 totally completed sell order
                set @out1 = '';
                set v_sql = 
                    'insert into temp_ce_committed_split_orders
                        (transaction_id, account_id, market_id,
                        game_start_date, game_id, game_period_id,
                        buy_or_sell, allowances_buy_amt, allowances_buy_price,
                        allowances_sell_amt, allowances_sell_price, executed_amt,
                        executed_price, match_with_transaction, sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime,
                        status, comment, created_date,
                        created_by, updated_date, updated_by)
                    select ''' + v_sell_transaction_id + ''', ''' + v_sell_account_id + ''', market_id,
                        game_start_date, game_id, game_period_id, 
                        ''s'', null, null, ' +
                        v_sell_allowances_sell_amt + ', ' + v_sell_allowances_sell_price + ', ' + v_sell_allowances_sell_amt + ', ' +
                        v_sell_allowances_sell_price + ', ''' + p_transaction_id + ''', null,
                        ''y'', ''' + v_sell_transaction_id + ''', date_format(''' + v_sell_submit_datetime_str + ''', ''%y-%m-%d %H:%m:%S''), 
                        ''totally_completed'', null, now(),
                        ''' + v_proc_name_recurrent_job + ''', now(), ''' + v_proc_name_recurrent_job + '''                   
                    from ce_emissions_trade
                    where transaction_id = ''' + p_transaction_id + '''';
                set @sql = v_sql;
                prepare stmt from @sql;
                execute stmt;
                deallocate prepare stmt;                
                -- write 1 totally completed buy order where buy_amt = sell_amt 
                set @out1 = '';
                set v_sql = 
                    'insert into temp_ce_committed_split_orders
                        (transaction_id, account_id, market_id,
                        game_start_date, game_id, game_period_id,
                        buy_or_sell, allowances_buy_amt, allowances_buy_price,
                        allowances_sell_amt, allowances_sell_price, executed_amt,
                        executed_price, match_with_transaction, sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime,
                        status, comment, created_date,
                        created_by, updated_date, updated_by)
                    select ''' + p_transaction_id + ''', account_id, market_id,
                        game_start_date, game_id, game_period_id, 
                        buy_or_sell, allowances_buy_amt, allowances_buy_price, allowances_sell_amt, allowances_sell_price, ' + v_sell_allowances_sell_amt + ', ' +
                        v_sell_allowances_sell_price + ', ''' + v_sell_transaction_id + ''', sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime, 
                        ''totally_completed'', comment, now(),
                        ''' + v_proc_name_recurrent_job + ''', now(), ''' + v_proc_name_recurrent_job + '''                   
                    from ce_emissions_trade
                    where transaction_id = ''' + p_transaction_id + '''';
                set @sql = v_sql;
                prepare stmt from @sql;
                execute stmt;
                deallocate prepare stmt;                 
                -- remaining = 0
                set v_remaining_buy_amt = 0
                leave sell_loop;
            else 
                -- v_remaining_buy_amt < v_sell_allowances_sell_amt
                -- write 1st partially completed sell order where sell_amt = buy_amt
                set @out1 = '';
                set v_sql = 
                    'insert into temp_ce_committed_split_orders
                        (transaction_id, account_id, market_id,
                        game_start_date, game_id, game_period_id,
                        buy_or_sell, allowances_buy_amt, allowances_buy_price,
                        allowances_sell_amt, allowances_sell_price, executed_amt,
                        executed_price, match_with_transaction, sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime,
                        status, comment, created_date,
                        created_by, updated_date, updated_by)
                    select ''' + v_sell_transaction_id + ''', ''' + v_sell_account_id + ''', market_id,
                        game_start_date, game_id, game_period_id, 
                        ''s'', null, null, ' +
                        v_sell_allowances_sell_amt + ', ' + v_sell_allowances_sell_price + ', ' + v_sell_allowances_sell_amt + ', ' +
                        v_sell_allowances_sell_price + ', ''' + p_transaction_id + ''', null,
                        ''y'', ''' + v_sell_transaction_id + ''', date_format(''' + v_sell_submit_datetime_str + ''', ''%y-%m-%d %H:%m:%S''), 
                        ''partially_completed'', null, now(),
                        ''' + v_proc_name_recurrent_job + ''', now(), ''' + v_proc_name_recurrent_job + '''                   
                    from ce_emissions_trade
                    where transaction_id = ''' + p_transaction_id + '''';
                set @sql = v_sql;
                prepare stmt from @sql;
                execute stmt;
                deallocate prepare stmt;                
                -- write 2nd incomplete partial sell order where sell_amt = sell_amt - buy_amt
                set @out1 = '';
                set v_sql = 
                    'insert into temp_ce_committed_split_orders
                        (transaction_id, account_id, market_id,
                        game_start_date, game_id, game_period_id,
                        buy_or_sell, allowances_buy_amt, allowances_buy_price,
                        allowances_sell_amt, allowances_sell_price, executed_amt,
                        executed_price, match_with_transaction, sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime,
                        status, comment, created_date,
                        created_by, updated_date, updated_by)
                    select ''' + v_sell_transaction_id + ''', ''' + v_sell_account_id + ''', market_id,
                        game_start_date, game_id, game_period_id, 
                        ''s'', null, null, ' +
                        v_sell_allowances_sell_amt + ', ' + v_sell_allowances_sell_price + ', ' + v_sell_allowances_sell_amt + ', ' +
                        v_sell_allowances_sell_price + ', ''' + p_transaction_id + ''', null,
                        ''y'', ''' + v_sell_transaction_id + ''', date_format(''' + v_sell_submit_datetime_str + ''', ''%y-%m-%d %H:%m:%S''), 
                        ''partial'', null, now(),
                        ''' + v_proc_name_recurrent_job + ''', now(), ''' + v_proc_name_recurrent_job + '''                   
                    from ce_emissions_trade
                    where transaction_id = ''' + p_transaction_id + '''';
                set @sql = v_sql;
                prepare stmt from @sql;
                execute stmt;
                deallocate prepare stmt;                
                -- write 1 totally completed buy order
                set @out1 = '';
                set v_sql = 
                    'insert into temp_ce_committed_split_orders
                        (transaction_id, account_id, market_id,
                        game_start_date, game_id, game_period_id,
                        buy_or_sell, allowances_buy_amt, allowances_buy_price,
                        allowances_sell_amt, allowances_sell_price, executed_amt,
                        executed_price, match_with_transaction, sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime,
                        status, comment, created_date,
                        created_by, updated_date, updated_by)
                    select ''' + p_transaction_id ?? + ''', account_id, market_id,
                        game_start_date, game_id, game_period_id, 
                        buy_or_sell, allowances_buy_amt, allowances_buy_price, allowances_sell_amt, allowances_sell_price, ' + v_sell_allowances_sell_amt + ', ' +
                        v_sell_allowances_sell_price + ', ''' + v_sell_transaction_id + ''', sys_completn_charge_perc,
                        accept_partial, original_transaction_id, submit_datetime, 
                        ''totally_completed'', comment, now(),
                        ''' + v_proc_name_recurrent_job + ''', now(), ''' + v_proc_name_recurrent_job + '''                   
                    from ce_emissions_trade
                    where transaction_id = ''' + p_transaction_id + '''';
                set @sql = v_sql;
                prepare stmt from @sql;
                execute stmt;
                deallocate prepare stmt;                 
                -- remaining = 0 
                set v_remaining_buy_amt = 0
                leave sell_loop;
            end if;
        end if;
    end loop;
    close cur_sell;
        
end //
delimiter ;