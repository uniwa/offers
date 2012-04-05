#!/bin/bash

# create required tmp files
if [[ ! -d app/tmp/cache ]]; then
    mkdir -p app/tmp/{cache,logs,sessions,tests}
    mkdir -p app/tmp/cache/{models,persistent,views}
fi

files=( bootstrap.php core.php database.php ldap.php configuration.php )

for file in "${files[@]}"; do
    filepath="app/Config/${file}"
    if [[ ! -e "${filepath}" ]]; then
        cp "${filepath}.default" "${filepath}"
        if [[ "${file}" != "bootstrap.php" && "${file}" != "core.php" && "${file}" != "configuration.php" ]]; then
            echo "Configure ${filepath}."
        fi
    fi
done

