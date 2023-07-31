<?php

class Lock
{

    private string $uri;
    private bool $has_lock;

    public function __construct($fd)
    {
        $this->uri = stream_get_meta_data($fd)['uri'];
    }

    public function lock() : bool
    {
        while(file_exists($this->uri.'.lock'))
        {
            usleep(10000);
            syslog(LOG_ERR, "OK");
        }

        $this->has_lock = link($this->uri, $this->uri.'.lock');
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
        $this->unlock();
    }

}