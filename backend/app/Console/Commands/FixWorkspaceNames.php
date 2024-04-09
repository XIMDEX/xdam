<?php

namespace App\Console\Commands;

use App\Models\Ability;
use App\Models\DamResource;
use App\Models\DamResourceWorkspace;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceUser;
use App\Services\OrganizationWorkspace\WorkspaceService;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\Auth;


class FixWorkspaceNames extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources:fixWorkspaceName';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unify resources with the same workspace name into a single workspace ID';

    const PATTERN_BATCH = '/^Batch\s\d{1,2}-\d{1,2}-\d{4}\s\d{1,2}_\d{1,2}_\d{1,2}$/';
    private $workspaceService;
    private $solrService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WorkspaceService $workspaceService, SolrService $solrService)
    {
        parent::__construct();
        $this->workspaceService = $workspaceService;
        $this->solrService = $solrService;
    }

    public function handle()
    {
        $wsps = ['Batches_1' => ['first' => 8480, 'ids' => []]];
        $workspaces = Workspace::all();
        $sadmin = User::where('email', 'superadmin@xdam.com')->first();

        Auth::login($sadmin);

        $batches_org = [];
        $wsp_not_delete = [8480];
        $wsp_to_delete = [];

        foreach ($workspaces as $workspace) {
            $wsp_name = $workspace->name;
            $wsp_id = $workspace->id;
            if (preg_match(self::PATTERN_BATCH, $wsp_name) ) {
                $orgId = $workspace->organization_id;
                $wsp_name = "Batches_$orgId";
                if (!isset($batches_org[$orgId])) {
                    $new_wsp_batch = Workspace::where('name', $wsp_name)->first();
                    if (!$new_wsp_batch) $new_wsp_batch = $this->workspaceService->create($orgId, $wsp_name);
                    $batches_org[$orgId] = $new_wsp_batch;
                    $wsp_id = $new_wsp_batch->id;
                }
            }

            if (strpos($workspace->name, '16_5_41') !== false) {
                $wsp_name = $workspace->name;
                $wsp_id = $workspace->id;
            }
            if (!isset($wsps[$wsp_name])) {
                $wsps[$wsp_name] = ['first' => $wsp_id, 'ids' => []];
                $wsp_not_delete[] = $wsp_id; 

            }
            
            if (isset($wsps[$wsp_name]) && $wsp_id !== $wsps[$wsp_name]['first']) {
                $wsps[$wsp_name]['ids'][] = $wsp_id;
                $wsp_to_delete[] = $wsp_id;
            }
        }
        
        $a = 22;
        foreach ($wsps as $name => $wsp) {
            $this->line('Workspace Name: '. $name);
            if (count($wsp['ids']) === 0) continue;
            $dam_resources_wsps = DamResourceWorkspace::whereIn('workspace_id', $wsp['ids'])->update();
            $this->withProgressBar($dam_resources_wsps->get(), function ($drw) use ($wsp) {
                /**
                 * @var DamResource $resource
                 */
                $resource = DamResource::find($drw->dam_resource_id);
                $hasWsp = $resource->workspaces()->find($wsp['first']);
                if (!$hasWsp) {
                    $resource->workspaces()->detach($wsp['ids']);
                    $resource->workspaces()->attach($wsp['first']);
                    $resource->save();
                    $this->solrService->saveOrUpdateDocument($resource);
                }                
            });
            Ability::where('entity_type', 'App\Models\Workspace')
                ->whereIn('entity_id', $wsp['ids'])
                ->update(['entity_id' => $wsp['first']]);

            User::whereIn('selected_workspace', $wsp['ids'])
                ->update(['selected_workspace' => $wsp['first']]);

            WorkspaceUser::whereIn('workspace_id', $wsp['ids'])
                ->update(['workspace_id' => $wsp['first']]);
            $this->line("");
        }
        $interseccion = array_intersect($wsp_not_delete, $wsp_to_delete);
        $error = !empty($interseccion);
        
        if ($error ) {
            return $this->error('errr');
        }

        Workspace::whereNotIn('id', $wsp_not_delete)->delete();
    }
}