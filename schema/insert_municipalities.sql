LOAD DATA LOCAL INFILE "municipalities.csv"
INTO TABLE `opendeals`.`municipalities`
CHARACTER SET 'UTF8'
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(id, county_id, name);

