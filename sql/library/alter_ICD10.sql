alter table icd10_gem_pcs_9_10 add column map_id SERIAL first;
alter table icd10_gem_pcs_10_9 add column map_id SERIAL first;
alter table icd10_gem_dx_9_10 add column map_id SERIAL first;
alter table icd10_gem_dx_10_9 add column map_id SERIAL first;
alter table icd10_reimbr_dx_9_10 add column map_id SERIAL first;
alter table icd10_reimbr_pcs_9_10 add column map_id SERIAL first;
