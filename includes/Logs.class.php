<?php

class Logs
{

    private string $raw_logs;
    private array $logs;

    public function __construct(string $raw_logs)
    {
        $this->logs = [];
        $this->raw_logs = $raw_logs;

        $this->logs = $this->convertRawLogs();
    }

    private function convertRawLogs() : array
    {
        $logs = [];
        if(preg_match_all("/^.*([0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{3}Z).*instanceId=\"([a-z0-9\-]+)\".*\] (.*)$/m", $this->raw_logs, $m, PREG_SET_ORDER))
        {
            foreach($m as $t)
            {
                $logs[] = ['date' => $t[1], 'instanceId' => $t[2], 'logsInfo' => $t[3]];
            }
        }
        
        return $logs;
    }

    public function isValidated() : bool
    {
        $v = true;
        foreach($this->logs as $logs)
        {
            $v = $v && (!empty($logs["date"]) && !empty($logs["instanceId"]) && !empty($logs["logsInfo"]));
        }

        return $v;
    }

    public function getLogs() : array
    {
        return $this->logs;
    }

    public function toString() : string
    {
        $s = "";

        foreach($this->logs as $logs)
        {
            $s .= '('.$logs["instanceId"].') '.$logs["date"].' '.$logs["logsInfo"]."\n";
        }

        return $s;
    }

    /*private function extractDate() : string
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
    }*/

}