alter table icd9_dx_code add column dx_id SERIAL first;
alter table icd9_dx_code add index (dx_code);

alter table icd9_sg_code add column sg_id SERIAL first;
alter table icd9_sg_code add index (sg_code);

alter table icd9_dx_long_code add index (dx_code);
alter table icd9_sg_long_code add index (sg_code);
