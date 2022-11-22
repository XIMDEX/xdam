<?php

namespace App\Services;

use \Vaites\ApacheTika\Client;

class TikaService
{
    /**
     * @param string
     */
    private string $host;

    /**
     * @param int
     */
    private int $port;

    /**
     * @param string
     */
    private string $path;

    /**
     * @param Client
     */
    private Client $client;

    /**
     * @param bool
     */
    private bool $appMode = false;

    public function __construct()
    {
        $this->host = env('TIKA_HOST', 'localhost');
        $this->port = env('TIKA_PORT', 9998);
        $this->path = env('TIKA_APP_PATH', '');
        $execMode = env('TIKA_EXEC_MODE', '');
        $this->appMode = (strtolower($execMode) === 'app' ? true : $this->appMode);
        $this->client = $this->connectClient();
    }

    private function connectClient()
    {
        if ($this->appMode) return Client::make($this->path);
        return Client::make($this->host, $this->port);
    }

    public function getSupportedMIMETypes()
    {
        return $this->client->getSupportedMIMETypes();
    }

    public function getAvailableDetectors()
    {
        return $this->client->getAvailableDetectors();
    }

    public function getAvailableParsers()
    {
        return $this->client->getAvailableParsers();
    }

    public function getVersion()
    {
        return $this->client->getVersion();
    }

    public function isMIMETypeSupported(string $mimeType)
    {
        return $this->client->isMIMETypeSupported($mimeType);
    }

    public function getFileMetadata(string $file)
    {
        $metadata = null;

        try {
            $metadata = $this->client->getMetadata($file);
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
        }

        return $metadata;
    }

    public function getFileRecursiveMetadata(string $file)
    {
        $recursiveMetadata = null;

        try {
            $recursiveMetadata = $this->client->getRecursiveMetadata($file, 'text');
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }

        return $recursiveMetadata;
    }

    public function getFileLanguage(string $file)
    {
        $language = null;

        try {
            $language = $this->client->getLanguage($file);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }
    
        return $language;
    }

    public function getFileMIME(string $file)
    {
        $mime = null;

        try {
            $mime = $this->client->getMIME($file);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }

        return $mime;
    }

    public function getFileHTML(string $file)
    {
        $html = null;

        try {
            $html = $this->client->getHTML($file);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }

        return $html;
    }

    public function getFileXHTML(string $file)
    {
        $xhtml = null;

        try {
            $xhtml = $this->client->getXHTML($file);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }

        return $xhtml;
    }

    public function getFileText(string $file)
    {
        $text = null;

        try {
            $text = $this->client->getText($file);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }

        return $text;
    }

    public function getFileMainText(string $file)
    {
        $mainText = null;

        try {
            $mainText = $this->client->getMainText($file);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();
        }

        return $mainText;
    }
}