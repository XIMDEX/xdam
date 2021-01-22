<?php


namespace App\Services\Solr;


use App\Enums\CollectionType;
use TSterker\Solarium\SolariumManager;

class SolrConfigService
{
    public array $cores;
    /**
     * @var SolariumManager
     */
    private SolariumManager $solarium;
    /**
     * @var MultimediaSchema
     */
    private MultimediaSchema $multimediaSchema;
    /**
     * @var CourseSchema
     */
    private CourseSchema $courseSchema;

    /**
     * SolrConfigService constructor.
     * @param SolariumManager $solarium
     * @param CourseSchema $courseSchema
     * @param MultimediaSchema $multimediaSchema
     */
    public function __construct(
        SolariumManager $solarium,
        CourseSchema $courseSchema,
        MultimediaSchema $multimediaSchema
    ) {
        $this->solarium = $solarium;
        $this->multimediaSchema = $multimediaSchema;
        $this->courseSchema = $courseSchema;
    }


    public function config(SolariumManager $solrClient, array $cores): void
    {
        $this->solarium = $solrClient;
        $this->cores = $cores;
    }

    public function getSchemaAssociatedToCore($coreName)
    {
        $schema = $this->multimediaSchema;
        if ($coreName == CollectionType::course) {
            $schema = $this->courseSchema;
        }
        return $schema;
    }

    public function createSolrCore(string $coreName): bool
    {
        $coreAdminQuery = $this->solarium->createCoreAdmin();
        $createCoreAction = $coreAdminQuery->createCreate();
        $createCoreAction->setCore($coreName);
        $coreAdminQuery->setAction($createCoreAction);
        $response = $this->solarium->coreAdmin($coreAdminQuery);
    }


    public function checkCoreAlreadyExists(string $coreName): bool
    {
        $coreAdminQuery = $this->solarium->createCoreAdmin();
        $statusAction = $coreAdminQuery->createStatus();
        $statusAction->setCore($coreName);
        $coreAdminQuery->setAction($statusAction);
        $response = $this->solarium->coreAdmin($coreAdminQuery);
        $statusResult = $response->getStatusResult();

        return $statusResult->getStartTime() ? true : false;
    }


    public function createSchemaForCore(string $coreName, array $fields): bool
    {
        $this->solarium->getEndpoint()->setCollection($coreName);
        $query = $this->solarium->createSelect();
        $request = $this->solarium->createRequest($query);
        $request->setHandler("schema");
        $request->setMethod("POST");
        $rawPostData = "";
        $numItems = count($fields);
        $i = 0;
        foreach ($fields as $key => $field) {
            if (++$i === $numItems) {
                $rawPostData .= '"add-field":' . json_encode($field);
            } else {
                $rawPostData .= '"add-field":' . json_encode($field) . ",";
            }
        }
        $request->setRawData($rawPostData);
        return json_decode($this->solarium->executeRequest($request)->getBody(), true);
    }

    public function getSchemaDifferences(string $coreName): array
    {
        $coreSchema = $this->getSchemaAssociatedToCore($coreName);
        $this->solarium->getEndpoint()->setCollection($coreName);

        $originalSchema = [];
        foreach ($coreSchema as $property => $value) {
            $originalSchema[$property] = json_decode($value);
        }

        $query = $this->solarium->createSelect();
        $request = $this->solarium->createRequest($query);
        $request->setHandler("schema");
        $currentSchema = json_decode($this->solarium->executeRequest($request)->getBody(), true)["schema"];
        $currentSchemaFields = $currentSchema["fields"];

        foreach ($currentSchemaFields as $index => $field) {
            $currentSchemaFields[$field["name"]] = $field;
            unset($currentSchemaFields[$index]);
        }

        $diff = array_diff(array_keys($originalSchema), array_keys($currentSchemaFields));

        return array_filter(
            $originalSchema,
            function ($key) use ($diff) {
                return in_array($key, $diff);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
