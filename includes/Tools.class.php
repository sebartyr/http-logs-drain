<?php
class Tools
{
    public static function isValidFilename(string $name) : bool
    {
        return (preg_match("/^[a-zA-Z0-9-_.]+$/", $name));
    }

    public static function isValidTableName(string $name) : bool
    {
        return (preg_match("/^[a-zA-Z0-9-_]+$/", $name));
    }

    public static function isValidDirpath(string $name) : bool
    {
        return (preg_match("/^[a-zA-Z0-9-_\/]+$/", $name));
    }

    public static function isValidDate(string $date) : bool
    {
        return preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]{3}Z$/", $date);
    }

}