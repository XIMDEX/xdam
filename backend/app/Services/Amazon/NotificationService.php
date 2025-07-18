<?php 

namespace App\Services\Amazon;

class NotificationService
{ 
    
    private $notificationScriptPath;

    public function __construct()
    {
        $this->notificationScriptPath = env('NOTIFICATION_SCRIPT_PATH');
    }
    
    public function notification($params)
    { 
        $path = ($this->notificationScriptPath);
        return shell_exec("php " . escapeshellarg($path) . ' ' . escapeshellarg($params['name']) . ' ' . escapeshellarg($params['metadata']) . ' ' . escapeshellarg($params['url']));
    }
}