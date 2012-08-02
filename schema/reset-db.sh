#!/bin/bash

# full path to schema folder
base="$( cd "$( dirname "$0" )" && pwd )"

dbfile="opendeals.sql"
declare -a data
declare -a samples

data=("insert_offer_categories.sql"
      "insert_days.sql"
      "insert_counties.sql"
      "insert_municipalities.sql")

samples=("insert_sample_users.sql"
         "insert_sample_students.sql"
         "insert_sample_companies.sql"
         "insert_sample_offers.sql")

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

# insert main database schema
echo ":: Descending into db"
pushd "$base/db" > /dev/null
echo -n "   -> Inserting database schema..."
mysql -u${dbuser} -p${dbpass} < "$dbfile"
echo "DONE"

# insert database data
echo -n "   -> Inserting database data..."
for i in ${data[@]}; do
    mysql -u${dbuser} -p${dbpass} < "$i"
done
echo "DONE"
popd > /dev/null
echo

# insert development data
echo ":: Descending into seeds"
pushd "$base/seeds" > /dev/null
echo -n "   -> Inserting sample data..."
for i in ${samples[@]}; do
    mysql -u${dbuser} -p${dbpass} < "$i"
done
echo "DONE"
popd > /dev/null
