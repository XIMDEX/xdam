#!/bin/bash

declare -a files=(
	"2022_08_23_135121_create_process_conversions_manager_table.php"
    "2022_08_19_141034_create_failed_jobs_table.php"
    "2022_08_19_140953_create_jobs_table.php")
folder="database/migrations"
indices=( ${!files[@]} )

rollback_migration () {
	local file="$1"
	php ../../artisan migrate:rollback --path="$file"
}

make_migration () {
	local file="$1"
    php ../../artisan migrate --path="$file"
}

for i in "${files[@]}"
do
    file="$folder/$i"
	rollback_migration "$file"
done

for ((i=${#indices[@]} - 1; i >= 0; i--))
do
    file="$folder/${files[indices[$i]]}"
    make_migration "$file"
done

exit 0
