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
        $wsps = [];
        $workspaces = Workspace::all();
        $sadmin = User::where('email', 'superadmin@xdam.com')->first();

        Auth::login($sadmin);

        $batches_org = [];

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

            if (!isset($wsps[$wsp_name])) {
                $wsps[$wsp_name] = ['first' => $wsp_id, 'ids' => []];
            }
            
            if (isset($wsps[$wsp_name]) && $wsp_id !== $wsps[$wsp_name]['first']) {
                $wsps[$wsp_name]['ids'][] = $wsp_id;
            }
        }

        $wsp_not_delete = [];
        $this->withProgressBar($wsps, function ($wsp) {
        // foreach ($wsps as $wsp) {

            
            $wsp_not_delete[] = $wsp['first'];
            if (count($wsp['ids']) === 0) return;
            $dam_resources_wsps = DamResourceWorkspace::whereIn('workspace_id', $wsp['ids'])->get();
            //     ->update(['workspace_id' => $wsp['first']]);
            foreach ($dam_resources_wsps as $drw) {
                /**
                 * @var DamResource $resource
                 */
                $resource = DamResource::find($drw->dam_resource_id);
                $resource->workspaces()->detach($wsp['ids']);
                $resource->workspaces()->attach($wsp['first']);

                $resource->save();
                $this->solrService->saveOrUpdateDocument($resource);
                
            }

            Ability::where('entity_type', 'App\Models\Workspace')
                ->whereIn('entity_id', $wsp['ids'])
                ->update(['entity_id' => $wsp['first']]);

            User::whereIn('selected_workspace', $wsp['ids'])
                ->update(['selected_workspace' => $wsp['first']]);

            WorkspaceUser::whereIn('workspace_id', $wsp['ids'])
                ->update(['workspace_id' => $wsp['first']]);
            

        });

        Workspace::whereNotIn('id', $wsp_not_delete)->delete();
    }
}