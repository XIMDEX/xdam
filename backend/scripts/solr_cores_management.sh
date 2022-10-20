#!/bin/bash
action="$1"
core="$2"
core_versioned="$3"

create_core() {
    # Gets the core argument
	local core="$1"
	local core_versioned="$2"

    # Creates the Solr core
	error=`sudo su - solr -c "/opt/solr/bin/solr create -c $core_versioned -n data_driven_schema_configs" 2>&1`

	if [[ $error == *"Created new core"* ]]; then
		echo "CORE $core_versioned CREATED"
		# Stores the core's configuration
		sudo cp ./storage/solr_core_conf/core_files/* /var/solr/data/$core_versioned
		sudo chown -R solr:solr /var/solr/data/$core_versioned
		sudo cp ./storage/solr_core_conf/core_conf_files/* /var/solr/data/$core_versioned/conf
		sudo chown -R solr:solr /var/solr/data/$core_versioned/conf

		# Installs the Solr core
		echo "y" | php artisan solr:install --core=$core_versioned
	else
		echo "CORE $core_versioned CREATION SKIPPED DUE TO ERROR $error"
	fi
}

delete_core() {
    # Gets the core argument
	local core="$1"
	local core_versioned="$2"

	# Deletes the Solr core
	sudo su - solr -c "/opt/solr/bin/solr delete -c $core_versioned"

    # Deletes the core's configuration files
	sudo rm -Rf /var/solr/data/$core_versioned	
}

# Checks the action to execute
if [ "$action" = "create" ]
then
    # Creates the specified core
    create_core $core $core_versioned
elif [ "$action" = "delete" ]
then
    # Deletes the specified core
    delete_core $core $core_versioned
fi

exit 0
