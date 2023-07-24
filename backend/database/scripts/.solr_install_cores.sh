#!/bin/bash
delete_core() {
	# Gets the core argument
	local core="$1"
	local coreVersion="$2"

	# Deletes the Solr core
	sudo su - solr -c "/opt/solr/bin/solr delete -c $core"	
}

create_core() {
	# Gets the core and the user's argument
	local core="$1"

	# Creates the Solr core
	sudo su - solr -c "/opt/solr/bin/solr create -c $core -n data_driven_schema_configs"

	# Stores the core's configuration
	sudo cp ./storage/solr_core_conf/core_files/* /var/solr/data/$core
	sudo chown -R solr:solr /var/solr/data/$core
	sudo cp ./storage/solr_core_conf/core_conf_files/* /var/solr/data/$core/conf
	sudo chown -R solr:solr /var/solr/data/$core/conf

	# Installs the Solr core
	echo "y" | php artisan solr:install --core=$core --coreVersion=$coreVersion
}

# Sets the array with the cores to delete/create, and iterates through it
cores=("activity" "assessment" "book" "course" "multimedia")
for core in ${cores[@]}; do
	# Deletes and creates the core
	delete_core $core
	create_core $core
done

# Reindexes the DB content
php artisan solr:reindex

exit 0
