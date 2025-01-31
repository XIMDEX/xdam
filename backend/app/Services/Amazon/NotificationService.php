<?php 

namespace App\Services\Amazon;

class NotificationService
{ 
    public function executeScript($script)
    {
        shell_exec("/bin/bash ".$script);
    }
}