<?php

namespace App\Console\Commands\Scripting;

use App\Models\DamResource;
use Illuminate\Console\Command;

class ParseCourseCostValue extends Command
{
    protected $signature = 'ParseCourseCostValue:start';

    protected $description = 'Apply to the property "cost" of the course, a math operation';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $operator = $this->ask('operation (/ *)');
        $value = $this->ask('number');

        foreach (DamResource::where('type', 'course')->get() as $course) {

            $data = json_decode(json_encode($course->data), true);

            if (isset($data['description']['cost'])) {

                $math = $operator === '/'
                ? $data['description']['cost'] / $value
                : $data['description']['cost'] * $value;

                $data['description']['cost'] = (int)$math;
                $course->data = $data;
                $course->save();
                $course->refresh();
                echo "\n Success: $course->id cost is now " . $course->data->description->cost . PHP_EOL;
            }

        }
        echo "\n Finished: the rest of courses has not a cost property" . PHP_EOL;
    }

}
