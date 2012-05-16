update `icd10_dx_order_code` SET formatted_dx_code = dx_code;
update `icd10_dx_order_code` SET formatted_dx_code = concat(concat(left(dx_code, 3), '.'), substr(dx_code, 4)) WHERE LENGTH(dx_code) > 3;
