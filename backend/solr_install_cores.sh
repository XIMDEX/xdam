#!/bin/bash
delete_core() {
	local core="$1"
	sudo su - solr -c "/opt/solr/bin/solr delete -c $core"	
}

create_core() {
	local core="$1"
	sudo su - solr -c "/opt/solr/bin/solr create -c $core -n data_driven_schema_configs"
	sudo cp /home/lluis/htdocs/Projects/xdam/backend/storage/solr_core_conf/core_files/* /var/solr/data/$core
	sudo chown -R solr:solr /var/solr/data/$core
	sudo cp /home/lluis/htdocs/Projects/xdam/backend/storage/solr_core_conf/core_conf_files/* /var/solr/data/$core/conf
	sudo chown -R solr:solr /var/solr/data/$core/conf
	echo "y" | php artisan solr:install --core=$core
}

# Deletes the cores
delete_core "activity"
delete_core "assessment"
delete_core "book"
delete_core "course"
delete_core "multimedia"

# Creates the cores
create_core "activity"
create_core "assessment"
create_core "book"
create_core "course"
create_core "multimedia"

# Reindexes the DB content
php artisan solr:reindex

exit 0
