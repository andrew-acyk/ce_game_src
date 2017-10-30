delimiter //
create procedure recurrent_proc (
    in p_game_id varchar(30), 
    in p_cur_period_id varchar(30),
    in p_period_start_date varchar(30) --YYYY-MM-DD
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
    -- The cur_buy cursor is reading from the temp_ce_read_buy_order table but the buy record is updating directly to 
    -- the ce_emissions_trade table.
    -- Similarly the temp_ce_committed_split_orders is used for avoiding update of the same sell record holding by the cur_sell cursor.
    -- The cur_sell cursor is reading from the ce_emissions_trade table but all the sell records are writing to
    -- the temp_ce_committed_split_orders table.  The records are later copied over to the ce_emissions_trade table after
    -- the cur_sell cursor is closed.
    declare cur_buy cursor for select * from temp_ce_read_buy_order order by submit_datetime asc;
    
    declare v_sql varchar(65500);
    declare v_next_period_num number;
    declare v_next_period_id varchar(30);
    declare v_game_start_date_str varchar(8);
    declare v_game_period_id varchar(30);
    declare v_carbon_price_last_period float;
    declare v_emissions_cap_perd_perc float;
    declare v_allowns_allocated_perd_perc float;
    declare v_fetch_finish integer;
    
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
        
    set @out1 = '';
    set v_sql = 
        'select parameter_value into @out1
        from ce_configuration_parameter
        where parameter_name = ''ce_proc_name_tradg_recur_job''';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_proc_name_tradg_recur_job = @out1;      
    
    set @out1 = '';
    set v_sql = 
        'select date_format(now(), ''%Y%m%d%H%i%S'') into @out1';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;
    set v_datestamp = @out1;    
    
    /*
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
    */
    
    set v_cur_game_period_id = p_game_id + '-' + p_cur_period_id);
    
    -- update allow_play_scenario = 'n', allow_trade_allowances = 'n'
    set v_sql =
        'update ce_game
        set allow_play_scenario = ''n'',
        allow_trade_allowances = ''n'',
        updated_by = ''' + v_proc_name_tradg_recur_job + ''',
        updated_date = date_format(''' + p_game_start_date + ''', ''%Y-%m-%d'')';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;        
    commit;
    
    -- backup ce_emissions_trade table
    set v_sql =
        'create table ce_emissions_trade_' + v_datestamp + char(10 using utf8) +
        'as select * from ce_emissions_trade
        where game_id = ''' + p_game_id + '''
        and game_period_id = ''' + v_cur_game_period_id + '''
        and game_start_date = date_format(''' + p_game_start_date + ''', ''%y-%m-%d'')';
    set @sql = v_sql;
    prepare stmt from @sql;
    execute stmt;
    deallocate prepare stmt;    
    
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
    
    open cur_buy;
    buy_loop: loop
        fetch cur_buy into v_buy_transaction_id, v_buy_account_id, v_buy_market_id,
            v_buy_game_start_date, v_buy_game_id, v_buy_game_period_id,
            v_buy_buy_or_sell, v_buy_allowances_buy_amt, v_buy_allowances_buy_price,
            v_buy_allowances_sell_amt, v_buy_allowances_sell_price, v_buy_executed_amt,
            v_buy_executed_price, v_buy_match_with_transaction, v_buy_sys_completn_charge_perc,
            v_buy_accept_partial, v_buy_original_transaction_id, v_buy_submit_datetime,
            v_buy_status, v_buy_comment;
        if v_fetch_finish = 1 then leave buy_loop end if;
        call recur_proc_find_sell_orders(v_buy_transaction_id, v_buy_allowances_buy_amt, v_buy_allowances_buy_price);
        
        -- delete from ce_emissions_trade where if the same transaction_id exists in temp_ce_committed_split_orders
        set v_sql =
            'delete from ce_emissions_trade trade
            where exists
                (select 1 from temp_ce_committed_split_orders tsplit
                where tsplit.transaction_id = trade.transaction_id)';
        set @sql = v_sql;
        prepare stmt from @sql;
        execute stmt;
        deallocate prepare stmt;        
        commit;
        
        -- copy all records from temp_ce_committed_split_orders to ce_emissions_trade table
        set v_sql =
            'insert into ce_emissions_trade
            select * from temp_ce_committed_split_orders';
        set @sql = v_sql;
        prepare stmt from @sql;
        execute stmt;
        deallocate prepare stmt;        
        commit;  
    end loop;
        
end //
delimiter ;