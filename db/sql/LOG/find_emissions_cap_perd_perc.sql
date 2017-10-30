use ce_schema;
select concat('ce_emissions_cap_perd_perc:', concat(parameter_value, ':')) from ce_configuration_parameter
where parameter_name = 'ce_emissions_cap_perd_perc';
