use ce_schema;
select concat('ce_proc_name_start_of_perd:', concat(parameter_value, ':')) from ce_configuration_parameter
where parameter_name = 'ce_proc_name_start_of_perd';
