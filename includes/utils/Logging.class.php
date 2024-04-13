<?php
if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'wb'));
if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'wb'));
class Logging
{
   private static string $format = "%date% [%level%] %message%";

   public static function setFormat(string $format) : void
   {
        self::$format = $format;
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
        fwrite(STDOUT, $message);
   }

   public static function warn(string $message) : void
   {
        $message = self::formatMessage("WARN", $message);
        fwrite(STDOUT, $message);
   }

   public static function error(string $message) : void
   {
        $message = self::formatMessage("ERROR", $message);
        fwrite(STDERR, $message);
   }
}