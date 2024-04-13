<?php
openlog("http-logs-drain", LOG_PID | LOG_PERROR, LOG_LOCAL0);

class Logging
{
   private static string $format = "%date% [%level%] %message%";

   public static function setFormat(string $format) : void
   {
        if(!empty($format)) self::$format = $format;
   }

   private static function formatMessage(string $level, string $message) : string
   {
        $message = str_replace(["%date%", "%level%", "%message%"], [date("c", time()), $level, $message], self::$format);
        return $message;
   }

   public static function log(int $level, string $message) : void
   {
        switch($level)
        {
            case LOG_ERR:
                self::error($message);
                break;
            
            case LOG_WARNING:
                self::warn($message);
                break;
            
            default:
                self::info($message);
        }
   }

   public static function info(string $message) : void
   {
        $message = self::formatMessage("INFO", $message);
        syslog(LOG_INFO, $message);
   }

   public static function warn(string $message) : void
   {
        $message = self::formatMessage("WARN", $message);
        syslog(LOG_WARNING, $message);
   }

   public static function error(string $message) : void
   {
        $message = self::formatMessage("ERROR", $message);
        syslog(LOG_ERR, $message);
   }
}