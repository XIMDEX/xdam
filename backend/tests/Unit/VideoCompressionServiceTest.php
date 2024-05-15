<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Media\VideoCompressionService;
use App\Services\Solr\SolrService;
use App\Models\MediaConversion;
use App\Models\Media;
use App\Models\DamResource;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoCompressionServiceTest extends TestCase
{
    public function testCompressVideo()
    {
        // Positive test case
        $solrServiceMock = $this->createMock(SolrService::class);
        $videoCompressionService = new VideoCompressionService($solrServiceMock);
        $task = (object)[
            'dest_path' => 'test_dest_path.mp4',
            'src_path' => 'test_src_path.mp4',
            'resolution' => '1280x720',
            'media_id' => 1,
            'media_conversion_name_id' => 1
        ];
        $this->expectOutputString('');
        $this->assertNull($videoCompressionService->compressVideo($task));

        // Negative test case - File does not exist
        $task->dest_path = 'non_existent_file.mp4';
        $this->expectException(ProcessFailedException::class);
        $this->assertNull($videoCompressionService->compressVideo($task));
    }

   /* public function testSaveConversionDetails()
    {
        // Positive test case
        $solrServiceMock = $this->createMock(SolrService::class);
        $videoCompressionService = new VideoCompressionService($solrServiceMock);
        $task = (object)[
            'dest_path' => 'test_dest_path.mp4',
            'src_path' => 'test_src_path.mp4',
            'resolution' => '1280x720',
            'media_id' => 1,
            'media_conversion_name_id' => 1
        ];
        $path = explode('/', $task->dest_path);
        Media::shouldReceive('where')->with('id', $task->media_id)->andReturnSelf();
        Media::shouldReceive('first')->andReturn((object)['model_id' => 1]);
        DamResource::shouldReceive('where')->with('id', 1)->andReturnSelf();
        DamResource::shouldReceive('first')->andReturn((object)['id' => 1]);
        $solrServiceMock->expects($this->once())->method('saveOrUpdateDocument')->with((object)['id' => 1]);
        MediaConversion::shouldReceive('create')->once()->with([
            'media_id' => $task->media_id,
            'file_type' => 'video/mp4',
            'file_name' => 'test_dest_path.mp4',
            'file_compression' => 1,
            'resolution' => '1280x720'
        ]);
        $this->assertNull($videoCompressionService->saveConversionDetails($task, $path));

        // Negative test case - Media not found
        Media::shouldReceive('where')->with('id', $task->media_id)->andReturnSelf();
        Media::shouldReceive('first')->andReturn(null);
        $this->expectException(\Exception::class);
        $this->assertNull($videoCompressionService->saveConversionDetails($task, $path));
    }*/
}