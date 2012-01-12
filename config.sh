#!/bin/bash

cp app/Config/bootstrap.php.default app/Config/bootstrap.php
cp app/Config/core.php.default app/Config/core.php
cp app/Config/database.php.default app/Config/database.php

# create required tmp files
mkdir -p app/tmp/{cache,logs,sessions,tests}
mkdir -p app/tmp/cache/{models,persistent,views}

# print some helpful messages
echo "Configure app/Config/database.php file."
