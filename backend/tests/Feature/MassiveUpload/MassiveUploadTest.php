<?php

namespace Tests\Feature;

use App\Enums\ResourceType;
use App\Enums\Roles;
use App\Enums\WorkspaceType;
use App\Models\Collection;
use App\Models\Workspace;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;


class MassiveUploadTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_masive_upload_of_multimedia_with_new_workspace()
    {
        $super_admin = $this->getUserWithRole((new Roles)->SUPER_ADMIN_ID(), null);

        $this->actingAs($super_admin, 'api');
        $loops = 4;

        Storage::fake('avatars');
        $files = [];

        for ($i=0; $i < $loops; $i++) {
            $files[] = UploadedFile::fake()->image('avatar'.$i.'.jpg');
        }

        $response = $this->json('POST', '/api/v1/resource/createBatch', [
            'collection' => Collection::where('accept', ResourceType::multimedia)->first()->id,
            'workspace' => 'batch_new',
            'files' => $files,
            'create_wsp' => '1'
        ]);
    }

}
