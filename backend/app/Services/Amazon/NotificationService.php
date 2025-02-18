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
        shell_exec("/bin/bash".$this->notificationScriptPath);
    }
}