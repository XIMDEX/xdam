<?php

namespace App\Console\Commands\Scripting;

use App\Models\DamResource;
use Exception;
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
        $operator = $this->ask('valid operators (+ - / * =)');
        $value = $this->ask('number');

        foreach (DamResource::where('type', 'course')->get() as $course) {

            $data = json_decode(json_encode($course->data), true);

            if (!isset($data['description']['cost'])) {
                $cost_from_price = (isset($data['description']['price']) && $data['description']['price'] === '')
                    ? 0
                    : ($data['description']['price'] ?? 0);

                $data['description']['cost'] = $cost_from_price;
            }

            $this->applyMath($course, $value, $operator, $data);

        }
        echo "\n Finished" . PHP_EOL;
    }

    public function applyMath($course, $value, $operator, $data)
    {
        switch ($operator) {
            case '=':
                $math = $data['description']['cost'] = $value;
                break;
            case '/':
                $math = $data['description']['cost'] / $value;
                break;
            case '*':
                $math = $data['description']['cost'] * $value;
                break;
            case '-':
                $math = $data['description']['cost'] - $value;
                break;
            case '+':
                $math = $data['description']['cost'] + $value;
                break;
            default:
                throw new Exception('Undefined operator');
                break;
        }

        $data['description']['cost'] = (int)$math;
        $course->data = $data;
        $course->save();
        $course->refresh();
        echo "\n Success: $course->id cost is now " . $course->data->description->cost . PHP_EOL;
    }

}
