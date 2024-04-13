<?php
require_once('utils/Logging.class.php');

class Lock
{

    private string $uri;
    private string $lock_uri;
    private bool $has_lock;

    public function __construct($fd = null, ?string $filepath = null)
    {
        if(is_null($fd) && is_null($filepath)) throw new Exception("Lock : invalid parameters");

        $this->uri = (!is_null($fd))?stream_get_meta_data($fd)['uri']:$filepath;

        $pathinfo = pathinfo($this->uri);
        $this->lock_uri = $pathinfo['dirname'].'/.'.$pathinfo['basename'].'.lock';

        $this->has_lock = false; 
    }

    public function lock(int $retry = 3) : bool
    {
        for($i = 0; $i < $retry; $i++)
        {
            while(file_exists($this->lock_uri))
            {
                usleep(1000);
            }

            if($this->has_lock = link($this->uri, $this->lock_uri)) break;
        }

        return $this->has_lock;
    }

    public function hasLock() : bool
    {
        return $this->has_lock;
    }
    
    public function unlock() : bool
    {
        if($this->has_lock && unlink($this->lock_uri))
        {
                $this->has_lock = false;
                return true;
        }

        Logging::log(LOG_ERR, "Error with file unlocking");
        return false;
    }

    public function __destruct()
    {
        if($this->hasLock()) unlink($this->lock_uri);
    }
}