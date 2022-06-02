<?php

namespace App\Console\Commands\Maintenance;

use App\Models\Organization;
use Illuminate\Console\Command;

class LinkOrganizationsExternalIds extends Command
{

    const ORGANIZATIONS_RELATIONSHIP = '/../../../../local/organizations_relationships.json';

    protected $signature = 'organization:link';

    protected $description = 'Links current organizations with xdir organizations';

    public function __construct()
    {
        parent::__construct();
    }

    private function findOrganizations(array $ids)
    {
        $organizations = array_map([Organization::class, 'find'], $ids);

        return array_filter(
            $organizations, 
            fn($value) => !is_null($value)
        );
    }

    public function handle()
    {
        $fileContents = file_get_contents(__DIR__ . self::ORGANIZATIONS_RELATIONSHIP, true);

        $relationships = json_decode($fileContents, true);

        $organizations = $this->findOrganizations(array_keys($relationships));

        foreach($organizations as $organization) {
            $xdirId = $relationships[$organization->id];

            $organization->update(['xdir_id' => $xdirId]);
        } 
    }
}