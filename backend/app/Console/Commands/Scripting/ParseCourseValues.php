<?php

namespace App\Console\Commands\Scripting;

use App\Models\DamResource;
use Exception;
use Illuminate\Console\Command;

class ParseCourseValues extends Command
{
    protected $signature = 'ParseCourseValues:start';

    protected $description = 'Apply a math operation to a prop over all the courses';

    private $operator;
    private $value;
    private $prop;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->operator = $this->ask('valid operators (+ - / * =)');
        $this->value = $this->ask('number');
        $this->prop = $this->ask('property: cost or duration');

        switch ($this->prop) {
            case 'cost':
                $this->parseCost();
                break;
            case 'duration':
                $this->parseDuration();
                break;
            default:
                throw new Exception('Invallid prop');
                break;
        }
    }

    public function parseCost() {
        foreach (DamResource::where('type', 'course')->get() as $course) {

            $data = json_decode(json_encode($course->data), true);

            if (!isset($data['description']['cost'])) {
                $cost_from_price = (isset($data['description']['price']) && $data['description']['price'] === '')
                    ? 0
                    : ($data['description']['price'] ?? 0);

                $data['description']['cost'] = $cost_from_price;
            }

            $this->applyMath($course, $data);

        }
        echo "\n Finished" . PHP_EOL;
    }

    public function parseDuration() {
        foreach (DamResource::where('type', 'course')->get() as $course) {

            $data = json_decode(json_encode($course->data), true);

            if (!isset($data['description']['duration'])) {
                
                $data['description']['duration'] = 0;
            } else if ($data['description']['duration'] === '') {
                
                $data['description']['duration'] = intval('0');
            } else {
                
                $data['description']['duration'] = (int)$data['description']['duration'];
            }
            
            
            $this->applyMath($course, $data);

        }
        echo "\n Finished" . PHP_EOL;
    }

    public function applyMath($course, $data)
    {
        switch ($this->operator) {
            case '=':
                $math = $data['description'][$this->prop] = $this->value;
                break;
            case '/':
                $math = $data['description'][$this->prop] / $this->value;
                break;
            case '*':
                $math = $data['description'][$this->prop] * $this->value;
                break;
            case '-':
                $math = $data['description'][$this->prop] - $this->value;
                break;
            case '+':
                $math = $data['description'][$this->prop] + $this->value;
                break;
            default:
                throw new Exception('Undefined operator');
                break;
        }

        $data['description'][$this->prop] = (int)$math;
        $course->data = $data;
        $course->save();
        $course->refresh();
        echo "\n Success: $course->id $this->prop is now " . (string)$course->data->description->{$this->prop} . PHP_EOL;
    }

}
