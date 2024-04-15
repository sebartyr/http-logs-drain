<?php

class Logs
{

    private string $raw_logs;
    private array $logs;

    public function __construct(string $raw_logs = "")
    {
        $this->raw_logs = $raw_logs;

        if(!empty($raw_logs)) $this->logs = $this->convertRawLogs();
    }

    private function convertRawLogs() : array
    {
        $logs = [];
        $pattern = "/^.*([0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{3}Z).*\[\s?instanceId=\"([a-z0-9-]+)\" severity=\"[a-zA-Z0-9-]+\" zone=\"[a-zA-Z0-9-]+\"\s?\] (.*)$/m";
        
        if(preg_match_all($pattern , $this->raw_logs, $m, PREG_SET_ORDER))
        {
            foreach($m as $t)
            {
                $logs[] = ['date' => $t[1], 'instanceid' => $t[2], 'logsinfo' => $t[3]];
            }
        }
        
        return $logs;
    }

    public function isValidated() : bool
    {
        $v = !empty($this->logs);
        foreach($this->logs as $logs)
        {
            $v = $v && (!empty($logs["date"]) && !empty($logs["instanceid"]) && !empty($logs["logsinfo"]));
        }

        return $v;
    }

    public function getLogs() : array
    {
        return $this->logs;
    }

    public function setLogs(array $logs) : void
    {
        $this->logs = $logs;
    }

    public function toString() : string
    {
        $s = "";

        foreach($this->logs as $logs)
        {
            $s .= '('.$logs["instanceid"].') '.$logs["date"].' '.$logs["logsinfo"]."\n";
        }

        return $s;
    }
}