update `icd9_dx_code` SET formatted_dx_code = dx_code;
update `icd9_dx_code` SET formatted_dx_code = concat(concat(left(dx_code, 3), '.'), substr(dx_code, 4)) WHERE dx_code RLIKE '^[V0-9]{1}.*' AND LENGTH(dx_code) > 3;
update `icd9_dx_code` SET formatted_dx_code = concat(concat(left(dx_code, 4), '.'), substr(dx_code, 5)) WHERE dx_code RLIKE '^[E]{1}.*' AND LENGTH(dx_code) > 4;
update `icd9_sg_code` SET formatted_sg_code = concat(concat(left(sg_code, 2), '.'), substr(sg_code, 3));
update `icd9_dx_code` A set long_desc = (select long_desc from icd9_dx_long_code where dx_code = A.dx_code);
update `icd9_sg_code` A set long_desc = (select long_desc from icd9_sg_long_code where sg_code = A.sg_code);
drop table icd9_dx_long_code;
drop table icd9_sg_long_code;

