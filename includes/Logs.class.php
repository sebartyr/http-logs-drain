<?php

class Logs
{

    private string $raw_logs;
    private array $logs;

    public function __construct(string $raw_logs)
    {
        $this->logs = [];
        $this->raw_logs = $raw_logs;

        $this->process();
    }

    private function process() : void
    {
        $this->logs["date"] = $this->extractDate();
        $this->logs["instanceId"] = $this->extractInstanceId();
        $this->logs["logsInfo"] = $this->extractLogsInfo();
    }

    public function isValidated() : bool
    {
        return (!empty($this->logs["date"]) && !empty($this->logs["instanceId"]) && !empty($this->logs["logsInfo"]));
    }

    public function getLogs() : array
    {
        return $this->logs;
    }

    public function toString() : string
    {
        return '('.$this->logs["instanceId"].') '.$this->logs["date"].' '.$this->logs["logsInfo"]."\n";
    }

    private function extractDate() : string
    {
        $m = [];
        if(preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{3}Z/", $this->raw_logs, $m))
            return $m[0];
        
        return "";
    }

    private function extractInstanceId() : string
    {
        $m = [];

        if(preg_match("/instanceId=\"[a-z0-9\-]+\"/", $this->raw_logs, $m))
        {
            $m[0] = str_replace('"', "", str_replace("instanceId=", "", $m[0]));
            return $m[0];
        }
            
        return "";
    }

    private function extractLogsInfo() : string
    {
        $m = [];

        if(preg_match("/\[instanceId=\"[a-z0-9\-]+.*\] .*$/", $this->raw_logs, $m))
        {
            $m[0] = preg_replace("/\[instanceId=\"[a-z0-9\-]+.*\] /", "", $m[0]);
            return $m[0];
        }
        return "";
    }

}