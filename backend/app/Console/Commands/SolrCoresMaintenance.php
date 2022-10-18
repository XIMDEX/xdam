<?php

namespace App\Console\Commands;

use App\Services\Solr\SolrConfig;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;

class SolrCoresMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solrCores:maintenance {--action=} {--core=*} {--coreVersion=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to execute the maintenance of the Solr cores.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function printErrorMessage()
    {
        echo 'ERROR! You must provide a valid --action parameter. This can be either "CREATE", "DELETE", or "REINDEX"' . PHP_EOL;
    }

    /**
     * Gets the excluded cores array.
     * @param array $cores
     * @param array $coresToManage
     * @return array
     */
    private function getExcludedCores($cores, $coresToManage)
    {
        $excludedCores = [];

        foreach ($cores as $core) {
            if (!in_array($core, $coresToManage)) $excludedCores[] = $core;
        }

        return $excludedCores;
    }

    /**
     * Gets the core name versioned.
     * @param SolrService $solrService
     * @param string $core
     * @param string $coreVersion
     * @return string
     */
    private function getCoreNameVersioned(SolrService $solrService, $core, $coreVersion)
    {
        $solrVersionUsed = $solrService->getCoreVersion($coreVersion);
        return $solrService->getCoreNameVersioned($core, $solrVersionUsed);
    }

    /**
     * Manages the cores creation.
     * @param SolrService $solrService
     * @param string $path
     * @param array $coresToInstall
     * @param array $solrCores
     * @param string $coreVersion
     * @return bool
     */
    private function manageCoresCreation(SolrService $solrService, $path, $coresToInstall, $solrCores, $coreVersion)
    {
        // Asks for a double user confirmation
        if ($this->confirm('This action will create the specified set of cores, and will remove sensible information stored. ' . 
                            'Do you understand the risks associated with this action?', false)) {
            if ($this->confirm('Are you absolutely sure to continue? This may cause irreversible damage to the stored data.', false)) {
                // Iterates through the cores...
                foreach ($coresToInstall as $core) {
                    // Checks if the core exists
                    if (in_array($core, $solrCores)) {
                        // Gets the core name versioned
                        $coreNameVersioned = $this->getCoreNameVersioned($solrService, $core, $coreVersion);
                
                        // Executes the shell command
                        shell_exec("$path create $core $coreNameVersioned");
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Manages the cores deleting.
     * @param SolrService $solrService
     * @param string $path
     * @param array $coresToDelete
     * @param array $solrCores
     * @param string $coreVersion
     * @return bool
     */
    private function manageCoresDeleting(SolrService $solrService, $path, $coresToDelete, $solrCores, $coreVersion)
    {
        // Asks for a double user confirmation
        if ($this->confirm('This action will delete the specified set of cores, and will remove sensible information stored. ' . 
                            'Do you understand the risks associated with this action?', false)) {
            if ($this->confirm('Are you absolutely sure to continue? This may cause irreversible damage to the stored data.', false)) {
                // Iterates through the cores...
                foreach ($coresToDelete as $core) {
                    // Checks if the core exists
                    if (in_array($core, $solrCores)) {
                        // Gets the core name versioned
                        $coreNameVersioned = $this->getCoreNameVersioned($solrService, $core, $coreVersion);

                        // Executes the shell command
                        shell_exec("$path delete $core $coreNameVersioned");
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Manages the cores reindexing.
     * @param SolrService $solrService
     * @param array $coresToReindex
     * @param array $solrCores
     * @param string $coreVersion
     * @return bool
     */
    private function manageCoresReindexing(SolrService $solrService, $coresToReindex, $solrCores, $coreVersion)
    {
        // Asks for a double user confirmation
        if ($this->confirm('This action will reindex the specified set of cores, and will remove sensible information stored. ' . 
                            'Do you understand the risks associated with this action?', false)) {
            if ($this->confirm('Are you absolutely sure to continue? This may cause irreversible damage to the stored data.', false)) {
                // Checks the excluded cores
                $excludedCores = $this->getExcludedCores($solrCores, $coresToReindex);

                // Calls to the proper command to execute the action
                $this->call("solr:reindex", [
                    "--exclude"     => $excludedCores,
                    "--solrVersion" => $solrService->getCoreVersion($coreVersion)
                ]);
            }
        }

        return false;
    }

    /**
     * Execute the console command.
     * @param SolrService $solrService
     * @param SolrConfig $solrConfig
     * @return bool
     */
    public function handle(SolrService $solrService, SolrConfig $solrConfig)
    {
        // Reads the command parameters
        $action = $this->option('action');
        $coresToManage = $this->option('core');
        $coreVersion = $this->option('coreVersion');

        // Checks if the action is correct
        if ($action === null || ($action !== 'CREATE' && $action !== 'DELETE' && $action !== 'REINDEX')) {
            $this->printErrorMessage();
            return false;
        }

        // Gets the entire set of Solr cores available in the config file
        $solrCores = $solrConfig->getSolrCores();

        // Checks the cores to manage
        $allCores = (count($coresToManage) == 0);
        $coresToManage = ($allCores ? $solrCores : $coresToManage);

        // Checks the action to execute
        if ($action === 'CREATE' || $action === 'DELETE') {
            // Gets the script path
            $path = getcwd() . '/scripts/solr_cores_management.sh';

            // Checks the action to execute
            if ($action === 'CREATE') {
                return $this->manageCoresCreation($solrService, $path, $coresToManage, $solrCores, $coreVersion);
            } else if ($action === 'DELETE') {
                return $this->manageCoresDeleting($solrService, $path, $coresToManage, $solrCores, $coreVersion);
            }
        } else if ($action === 'REINDEX') {
            return $this->manageCoresReindexing($solrService, $coresToManage, $solrCores, $coreVersion);
        }

        return false;
    }
}
