<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Silber\Bouncer\BouncerFacade as Bouncer;
use Silber\Bouncer\Database\Ability;

class AbilityService
{

    /**
     * @param Request $request
     * @return Ability
     */
    public function store($name, $title): Ability
    {
        $ability = Bouncer::ability()->firstOrCreate([
            'name' => $name,
            'title' => $title,
        ]);
        return $ability;
    }

    public function index(): Collection
    {
        $abilities = Ability::all();
        return $abilities;
    }
    /**
     * @param Ability $ability
     * @throws \Exception
     */
    public function get($id): Ability
    {
        $ability = Ability::find($id);
        return $ability;
    }

    /**
     * @param Ability $ability
     * @throws \Exception
     */
    public function update($id): Ability
    {
        $ability = Ability::findOrFail($id);
        //update code
        return $ability;
    }

    /**
     * @param Ability $ability
     * @throws \Exception
     */
    public function delete($id)
    {
        $ability = Ability::findOrFail($id);
        $ability->delete();
    }

}
