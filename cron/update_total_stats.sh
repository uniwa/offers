#!/bin/sh

root_path="$(dirname $0)/.."
app_path="$root_path/app"
console="$app_path/Console/cake"
script="update_total_stats"

$console -app $app_path $script "$@"
