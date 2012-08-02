#!/bin/sh

root_path="$(dirname $0)/.."
app_path="$root_path/app"
console="$app_path/Console/cake"
script="remove_old_visits"

$console -app $app_path $script "$@"
