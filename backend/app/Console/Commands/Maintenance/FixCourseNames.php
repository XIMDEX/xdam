<?php

namespace App\Console\Commands\Maintenance;

use App\Enums\ResourceType;
use App\Models\DamResource;
use App\Services\ResourceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class FixCourseNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:courseNames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'go through the course and set name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param ResourceService $resourceService
     * @return int
     */
    public function handle(ResourceService $resourceService)
    {
        $courses = DamResource::where('type', 'course')->get();

        foreach ($courses as $course) {
            $this->courseTitleToName($course);
            $this->line($course->id . " course name modified");
        }
    }

    public function courseTitleToName($course)
    {
        $data = json_decode(json_encode($course->data), true);



        if(isset($data['description']['course_title']) && !isset($data['description']['name'])) {
            $data['description']['name'] = $data['description']['course_title'];
            unset($data['description']['course_title']);
            $course->name = $data['description']['name'];
            $course->data = $data;
            $course->save();
            return;
        }

        if(isset($data['description']['name'])) {
            if(isset($data['description']['course_title'])) {
                unset($data['description']['course_title']);
            }
            $course->name = $data['description']['name'];
            $course->data = $data;
            $course->save();
            return;
        }

        if(!isset($data['description']['name']) && !isset($data['description']['course_title'])) {
            $data['description']['name'] = 'unnamed';
            $course->name = $data['description']['name'];
            $course->data = $data;
            $course->save();
            return;
        }


    }
}
