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
        User::create([
            'name' => 'Super Admin user',
            'email' => 'superadmin@xdam.com',
            'password' => Hash::make('123123')
            ]);

        Bouncer::assign(Roles::super_admin)->to(User::find(1));

        $admin_of_all = User::create([
            'name' => 'Admin of all ',
            'email' => 'admin_of_all@xdam.com',
            'password' => Hash::make('123123')
        ]);

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

        $editor = User::create([
            'name' => 'Editor user',
            'email' => 'editor@xdam.com',
            'password' => Hash::make('123123')
        ]);

        $reader = User::create([
            'name' => 'Reader user',
            'email' => 'reader@xdam.com',
            'password' => Hash::make('123123')
        ]);

        $user_empty = User::create([
            'name' => 'Empty user',
            'email' => 'empty@xdam.com',
            'password' => Hash::make('123123')
        ]);



        //Factory Corporate Organizations ids 3 4 5 6
        $this->adminService->setOrganizationHelper($admin_of_all, Organization::find(3), Roles::admin_id, false);
        $this->adminService->setOrganizationHelper($admin_of_all, Organization::find(4), Roles::admin_id, false);
        $this->adminService->setOrganizationHelper($admin_of_all, Organization::find(5), Roles::admin_id, false);
        $this->adminService->setOrganizationHelper($admin_of_all, Organization::find(6), Roles::admin_id, false);

        $this->adminService->setOrganizationHelper($admin, Organization::find(3), Roles::admin_id, false);
        $this->adminService->setOrganizationHelper($admin, Organization::find(4), Roles::admin_id, false);

        $this->adminService->setOrganizationHelper($manager, Organization::find(4), Roles::manager_id, true);
        $this->adminService->setOrganizationHelper($manager, Organization::find(5), Roles::manager_id, true);

        $this->adminService->setOrganizationHelper($editor, Organization::find(5), Roles::editor_id, true);
        $this->adminService->setOrganizationHelper($editor, Organization::find(6), Roles::editor_id, true);

        $this->adminService->setOrganizationHelper($reader, Organization::find(6), Roles::reader_id, true);
        $this->adminService->setOrganizationHelper($reader, Organization::find(4), Roles::reader_id, true);

    }
}
