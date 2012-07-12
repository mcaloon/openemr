update `icd9_dx_code` SET formatted_dx_code = dx_code;
update `icd9_dx_code` SET formatted_dx_code = concat(concat(left(dx_code, 3), '.'), substr(dx_code, 4)) WHERE dx_code RLIKE '^[V0-9]{1}.*' AND LENGTH(dx_code) > 3;
update `icd9_dx_code` SET formatted_dx_code = concat(concat(left(dx_code, 4), '.'), substr(dx_code, 5)) WHERE dx_code RLIKE '^[E]{1}.*' AND LENGTH(dx_code) > 4;
update `icd9_sg_code` SET formatted_sg_code = concat(concat(left(sg_code, 2), '.'), substr(sg_code, 3));
update `icd9_dx_code` A, `icd9_dx_long_code` B set A.long_desc = B.long_desc where A.dx_code = B.dx_code and A.active = 1 and A.long_desc is NULL;
update `icd9_sg_code` A, `icd9_sg_long_code` B set A.long_desc = B.long_desc where A.sg_code = B.sg_code and A.active = 1 and A.long_desc is NULL;
