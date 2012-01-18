#!/bin/bash

dbfile="opendeals.sql"
dbname="opendeals"
declare -a data
declare -a samples

data=( "insert_categories.sql" "insert_offer_types.sql" )
samples=( "insert_sample_users.sql" "insert_sample_students.sql" "insert_sample_companies.sql" 
          "insert_sample_offers.sql" )

echo -n "Enter your mysql user to connecto to db (enter for root) > "
read dbuser
if [[ -z $dbuser ]]; then
    dbuser="root"
fi

echo -n "Enter your mysql password for user ${dbuser} > "
stty -echo
read dbpass
stty echo
echo -e "\n"

echo -n ">> Inserting database schema..."
mysql -u${dbuser} -p${dbpass} < ${dbfile}
echo "DONE"

echo -n ">> Inserting database data..."
for i in ${data[@]}; do
    mysql -u${dbuser} -p${dbpass} ${dbname} < "${i}"
done
echo "DONE"

echo -n ">> Inserting sample data..."
for i in ${samples[@]}; do
    mysql -u${dbuser} -p${dbpass} ${dbname} < "${i}"
done
echo "DONE"
