use ce_schema;

update ce_account
set created_by = 'andrewk',
    created_date = now(),
    updated_by = 'andrewk',
    updated_date = now();
    
commit;