<?php

namespace App\Console\Commands\Scripting;

use App\Models\Collection;
use App\Models\DamResource;
use App\Models\User;
use App\Models\Workspace;
use App\Services\ExternalApis\XevalService;
use App\Services\ResourceService;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use stdClass;

class SyncXeval extends Command
{
    const ACTIVITY = 'activity';
    const ASSESSMENT = 'assessment';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncXeval:start {--type=ALL} {--force}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Sync activities and assessments resources from xeval repository -- type options " . self::ACTIVITY.'|'.self::ASSESSMENT.'|ALL';

    private XevalService $xevalService;
    private ResourceService $resourceService;
    private $page_size;
    private $force;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(XevalService $xevalService, ResourceService $resourceService)
    {
        $this->xevalService = $xevalService;
        $this->resourceService = $resourceService;
        $this->page_size = (int) config('xeval.sync.page_size');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param SolrService $solrService
     * @param ResourceService $resourceService
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $type = $this->option('type');
        $this->force = $this->option('force');
        try {
            $superAdmin = User::where('email', 'superadmin@xdam.com')->first();
            Auth::loginUsingId($superAdmin->id);

            $this->line('Init sync XEVAL resources');
            if ($type === self::ACTIVITY || $type === strtoupper(self::ACTIVITY) || $type == null || $type == 'ALL') {
                $this->line('Sync resources of type '.self::ACTIVITY);
                $this->handleActivity($superAdmin);
            }
            if ($type === self::ASSESSMENT || $type === strtoupper(self::ASSESSMENT) || $type == null || $type == 'ALL') {
                $this->line('Sync resources of type '.self::ASSESSMENT);
                $this->handleAssessment($superAdmin);
            }
            echo 'finished' . PHP_EOL;
        } catch (\Throwable $th) {
            throw $th;
            // throw new \Error('Failed command');
        }
    }

    private function handleActivity($user)
    {
        try {
            $workspace = Workspace::find($user->selected_workspace);
            $collection = Collection::where('organization_id', $workspace->organization_id)->where('solr_connection', self::ACTIVITY)->first();
            if (null == $collection) $collection = Collection::where('organization_id', $workspace->organization_id)->where('accept', self::ACTIVITY)->first();
            $countActivitiesDAM = $this->resourceService->countResources(self::ACTIVITY);
            $countActivitiesXEVAL = $this->getActivitiesFromXeval(1, 0);
            $countActivitiesXEVAL = $countActivitiesXEVAL['total'];

            $this->syncBatchs('getActivitiesFromXeval', 'parseActivityData', self::ACTIVITY, $countActivitiesDAM, $countActivitiesXEVAL, $collection);
        } catch (\Throwable $th) {
            throw $th;
        }

    }

    private function handleAssessment($user)
    {
        try {
            $workspace = Workspace::find($user->selected_workspace);
            $collection = Collection::where('organization_id', $workspace->organization_id)->where('solr_connection', self::ASSESSMENT)->first();
            if (null == $collection) $collection = Collection::where('organization_id', $workspace->organization_id)->where('accept', self::ASSESSMENT)->first();
            $countAssessmentsDAM = $this->resourceService->countResources(self::ASSESSMENT);
            $countAssessmentsXEVAL = $this->getAssessmentsFromXeval(1,0);
            $countAssessmentsXEVAL = $countAssessmentsXEVAL['total'];
            $this->syncBatchs('getAssessmentsFromXeval', 'parseAssessmentData', self::ASSESSMENT, $countAssessmentsDAM, $countAssessmentsXEVAL, $collection);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Get Activities from Xeval reposotory
     *
     * @param int $p number page
     * @param int $ps page size
     * @param string $lang
     * @return array array of activities
     * @throws \Exception
     */
    private function getActivitiesFromXeval($p, $ps)
    {
        return $this->xevalService->getActivities($p, $ps, null);
    }

    /**
     * Get Assessments from Xeval repository.
     *
     * @param int $p number page
     * @param int $ps page size
     * @param string $lang
     * @return array array of assessments
     * @throws \Exception
     */
    private function getAssessmentsFromXeval($p, $ps)
    {
        return $this->xevalService->getAssessments($p, $ps, null);
    }


    private function stopSync() {
        if ($this->force) return false;

        $now = Now();
        return $now->dayOfWeek < 6 && ($now->hour > config('xeval.sync.start_hour') || $now->hour < config('xeval.sync.finish_hour'));
    }

    private function syncBatchs($requestMethod, $parseDataMethod, $type, $countDAM, $countXEVAL, $collection)
    {
        if ($countXEVAL === 0 || $countDAM >= $countXEVAL) return;

        $page = 1;
        $pagesXEVAL = floor($countXEVAL / $this->page_size);
        $pagesDAM = floor($countDAM / $this->page_size);
        if ($pagesDAM <= $pagesXEVAL) $page = $pagesDAM;
        if ($page == 0 || $this->force) $page = 1;

        $this->line("<fg=green>Synchronized $countDAM of $countXEVAL $type</>");
        $progressBar = $this->output->createProgressBar($countXEVAL);
        $progressBar->advance($countDAM);
        try {
            $batch = 1;
            while (!$this->stopSync() || null !== $page) {
                $data = $this->{$requestMethod}($page, $this->page_size);
                $resourcesBatch = [];
                foreach ($data['data'] as $resource) {
                    $resourcesBatch[] = $this->{$parseDataMethod}($resource, $collection->id);
                }
                $this->storeResources($resourcesBatch, $progressBar);
                $page = $data['next_page'];
                $batch++;
                $countDAM++;
            }
            $status = $countDAM == $countXEVAL ? 'finished' : 'stopped';
            $this->info("Synchronization $status. Resources on XDAM $countDAM of $countXEVAL");
            $progressBar->finish();
        } catch (\Throwable $th) {
            throw $th;
        }

    }

    private function storeResources($data, &$progressBar)
    {
        foreach ($data as $resource) {
            $damResource = DamResource::find($resource['xeval_id']);
            if ($damResource) {
                $this->resourceService->update($damResource, $resource);
            } else {
                $this->resourceService->store($resource);
            }
            $progressBar->advance();
        }
    }

    private function parseActivityData($activity, $collection_id)
    {
        $data = [
            'xeval_id' => $activity['id'],
            'collection_id' => $collection_id,
            'type' => self::ACTIVITY,
            'data' => new stdClass(),
            'status' => $activity['status'] === 'ACTIVE'
        ];
        $assessments = [];
        $_assessments = array_column($activity['assessments'], 'id');
        foreach ($_assessments as $assessment_id) {
            $assessments[] = intval($assessment_id);
        }
        $data['data']->description = new stdClass();
        $data['data']->description->xeval_id = $activity['id'];
        $data['data']->description->name = $activity['name'] ?? "Un-named ID {$activity['id']}";
        $data['data']->description->description = $activity['title'];
        $data['data']->description->type = $activity['type'];
        $data['data']->description->language_default = $activity['language_default'];
        $data['data']->description->available_languages = $activity['available_languages'];
        $data['data']->description->isbn = $activity['isbn'];
        $data['data']->description->unit = $activity['units'];
        $data['data']->description->active = $activity['status'] === 'ACTIVE';
        $data['data']->description->assessments = $assessments;
        if ($activity['tags']) {
            $data['data']->description->tags = $activity['tags'];
        }
        return $data;
    }

    private function parseAssessmentData($assessment, $collection_id)
    {
        $data = [
            'xeval_id' => $assessment['id'],
            'collection_id' => $collection_id,
            'type' => self::ASSESSMENT,
            'data' => new stdClass(),
            'active' => $assessment['status'] === 'ACTIVE'
        ];
        $activities = [];
        $_activities = array_column($assessment['activities'], 'id');
        foreach ($_activities as $activity_id) {
            $activities[] = intval($activity_id);
        }
        $data['data']->description = new stdClass();
        $data['data']->description->xeval_id = $assessment['id'];
        $data['data']->description->name = $assessment['title'];
        $data['data']->description->isbn = $assessment['isbn'];
        $data['data']->description->unit = $assessment['units'];
        $data['data']->description->active =$assessment['status'] === 'ACTIVE';
        $data['data']->description->activities = $activities;
        return $data;
    }
}
