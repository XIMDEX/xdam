#!/bin/bash

declare -a files=(
	"2022_07_29_142030_add_media_conversion_id_on_pending_video_compression_tasks.php"
	"2022_07_29_115352_create_media_conversions_table.php"
	"2022_07_25_142109_create_pending_video_compression_tasks_table.php"
	"2022_07_18_063157_create_access_permission_rules_table.php"
	"2022_07_18_063011_create_access_permissions_table.php"
	"2022_07_15_110634_create_cdns_collections_table.php"
	"2022_07_15_093711_create_cdns_table.php")
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
