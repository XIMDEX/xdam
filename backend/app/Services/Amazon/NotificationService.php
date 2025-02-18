<?php 

namespace App\Services\Amazon;

class NotificationService
{ 
    
    private $notificationScriptPath;

    public function __construct()
    {
        $this->notificationScriptPath = env('NOTIFICATION_SCRIPT_PATH');
    }
    
    public function notification()
    { 
        $path = base_path($this->notificationScriptPath);
        return shell_exec("php " . escapeshellarg($path));
    }
}