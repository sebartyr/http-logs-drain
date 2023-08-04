<?php

class Lock
{

    private string $uri;
    private bool $has_lock;

    public function __construct($fd)
    {
        $this->uri = stream_get_meta_data($fd)['uri'];
    }

    public function lock(int $retry = 3) : bool
    {
        for($i = 0; $i < $retry; $i++)
        {
            while(file_exists($this->uri.'.lock'))
            {
                usleep(1000);
            }

            if($this->has_lock = link($this->uri, $this->uri.'.lock')) break;
        }

        return $this->has_lock;
    }

    public function hasLock() : bool
    {
        return $this->has_lock;
    }
    
    public function unlock() : bool
    {
        if($this->has_lock && unlink($this->uri.'.lock'))
        {
                $this->has_lock = false;
                return true;
        }

        syslog(LOG_ERR, "Error with file unlocking");
        return false;
    }

    public function __destruct()
    {
        if($this->hasLock()) unlink($this->uri.'.lock');
    }
}