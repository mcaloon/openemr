update `icd9_dx_code` A set long_desc = (select long_desc from icd9_dx_long_code where dx_code = A.dx_code);
update `icd9_sg_code` A set long_desc = (select long_desc from icd9_sg_long_code where sg_code = A.sg_code);
drop table icd9_dx_long_code;
drop table icd9_sg_long_code;
