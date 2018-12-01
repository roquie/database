-- migration: 2018_11_25_153835_lol.up.sql


CREATE TABLE lols (
  migration Character Varying (255) NOT NULL,
  batch Integer NOT NULL
  );


-- CREATE VIEW "view1" -----------------------------------------
CREATE OR REPLACE VIEW "public"."view1" AS  SELECT 1;;
-- -------------------------------------------------------------
