#!/bin/sh

root_path="$(pwd -P)/.."
app_path="$root_path/app"
console="$app_path/Console/cake"
script="news"

$console -app $app_path $script "$@"
