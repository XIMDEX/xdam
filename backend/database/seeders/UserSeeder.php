<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Admin\AdminService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade as Bouncer;

class UserSeeder extends Seeder
{

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $superadmin = User::create([
            'name' => 'Super Admin user',
            'email' => 'superadmin@xdam.com',
            'password' => Hash::make('123123')
            ]);

        Bouncer::assign(Roles::SUPER_ADMIN)->to($superadmin);

        $admin = User::create([
            'name' => 'Admin user',
            'email' => 'admin@xdam.com',
            'password' => Hash::make('123123')
        ]);

        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@xdam.com',
            'password' => Hash::make('123123')
        ]);

        $basic_user = User::create([
            'name' => 'Basic user',
            'email' => 'basic_user@xdam.com',
            'password' => Hash::make('123123')
        ]);


        //Factory Corporate Organizations ids 3 4 5 6

        $roles = new Roles;
        $this->adminService->setOrganizations($admin->id, 3, $roles->ORGANIZATION_ADMIN_ID());
        // $this->adminService->setOrganizations($manager->id, 3, $roles->ORGANIZATION_MANAGER_ID());
        // $this->adminService->setOrganizations($admin->id, 3, $roles->ORGANIZATION_USER_ID());


    }
}
