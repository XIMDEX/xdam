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

class EditorUserSeeder extends Seeder
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
            'name' => 'Editor user',
            'email' => 'editor@xdam.com',
            'password' => Hash::make('xdamMhe@2024')
            ]);

        Bouncer::assign(Roles::SUPER_ADMIN)->to($superadmin);

    }
}
