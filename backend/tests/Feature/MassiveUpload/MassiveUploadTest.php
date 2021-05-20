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

        //$collection_id = $data['org']->collections()->where('solr_connection', 'multimedia')->first()->id;

        $response = $this->json('POST', '/api/v1/resource/createBatch', [
            'collection' => Collection::where('accept', ResourceType::multimedia)->first()->id,
            'workspace' => 'batch_new',
            'files' => $files,
            'create_wsp' => '1'
        ]);

        $a = 0;
    }

    // public function test_throw_error_if_mime_type_is_not_suported()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    // public function test_the_batch_created_a_new_workspace()
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

}
