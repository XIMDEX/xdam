#!/bin/bash
get_core_name_versioned() {
	# Gets the core argument
	local core="$1"
	local file="_tmp_core_name_versioned_output.txt"

	php artisan get:coreNameVersion --coreName=$core > $file

	while IFS= read -r line || [[ -n "$line" ]]; do
		fileLine="$line"
	done < "$file"
	
	rm $file
	echo $fileLine
}

delete_core() {
	# Gets the core argument
	local core="$1"
	local core_versioned="$2"

	# Deletes the Solr core
	sudo su - solr -c "/opt/solr/bin/solr delete -c $core_versioned"	
}

create_core() {
	# Gets the core argument
	local core="$1"
	local core_versioned="$2"

	# Creates the Solr core
	sudo su - solr -c "/opt/solr/bin/solr create -c $core_versioned -n data_driven_schema_configs"

	# Stores the core's configuration
	sudo cp ./storage/solr_core_conf/core_files/* /var/solr/data/$core_versioned
	sudo chown -R solr:solr /var/solr/data/$core_versioned
	sudo cp ./storage/solr_core_conf/core_conf_files/* /var/solr/data/$core_versioned/conf
	sudo chown -R solr:solr /var/solr/data/$core_versioned/conf

	# Installs the Solr core
	echo "y" | php artisan solr:install --core=$core_versioned
}

# Sets the array with the cores to delete/create, and iterates through it
cores=("activity" "assessment" "book" "course" "multimedia")
for core in ${cores[@]}; do
	core_versioned=$(get_core_name_versioned $core)

	# Deletes and creates the core
	delete_core $core $core_versioned
	create_core $core $core_versioned
done

# Reindexes the DB content
php artisan solr:reindex

exit 0
