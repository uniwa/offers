#!/bin/sh

root_path="$(dirname $0)/.."
app_path="$root_path/app"
console="$app_path/Console/cake"
script="notify_improper"

# check for last 6 hours == run cron every 6h
$console -app $app_path $script since '6 hours' "$@"
