<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ResourceType;
use App\Models\Corporation;

class CorporationService extends BaseService
{
    /**
     * @param $name
     * @return string|string[]
     */
    private function satinizeCorporationName($name)
    {
        return str_replace(" ", "_", $name);
    }

    /**
     * @return Corporation[]
     */
    public static function getAll()
    {
        return Corporation::all();
    }

    /**
     * @param Corporation $corporation
     * @return Corporation
     */
    public function get(Corporation $corporation)
    {
        return $corporation;
    }

    /**
     * @param Corporation $corporation
     * @return mixed
     */
    public function getResources(Corporation $corporation, $active)
    {
        $active = $active == null ? 1 : $active;
        return $corporation->resources()->where('active', $active)->get();
    }

    /**
     * @param Corporation $corporation
     * @param $data
     * @return Corporation|false
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function update(Corporation $corporation, $data)
    {
        return $corporation->update([
            'name' => $this->satinizeCorporationName($data["name"]),
            'type' => ResourceType::fromKey($data["type"])->value
        ]);
    }

    /**
     * @param $params
     * @return Corporation
     * @throws \BenSampo\Enum\Exceptions\InvalidEnumKeyException
     */
    public function store($params) : Corporation
    {
        return Corporation::create([
            'name' => $this->satinizeCorporationName($params["name"]),
            'type' => ResourceType::fromKey($params["type"])->value,
        ]);
    }

    /**
     * @param Corporation $corporation
     * @throws \Exception
     */
    public function delete(Corporation $corporation)
    {
        $corporation->delete();
    }

}
