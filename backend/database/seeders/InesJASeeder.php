<?php

namespace Database\Seeders;

use App\Enums\DefaultOrganizationWorkspace;
use App\Enums\Roles;
use App\Models\Collection;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class InesJASeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coreName = 'document';

        $publicOrganization = Organization::where('name', DefaultOrganizationWorkspace::public_organization)->firstOrFail();

        $inesJA = User::create([
            'name' => 'INES-JA admin user',
            'email' => 'ines-ja@xdam.com',
            'password' => Hash::make('123123')
        ]);

        Bouncer::assign(Roles::SUPER_ADMIN)->to($inesJA);

        $solariumConnections = config('solarium.connections', []);

        Collection::create([
            'name' => DefaultOrganizationWorkspace::public_organization . ' Document collection',
            'organization_id' => $publicOrganization->id,
            'solr_connection' => $coreName,
            'accept' => $solariumConnections[$coreName]['accepts_types'][0]
        ]);
    }
}
