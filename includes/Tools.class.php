<?php
class Tools
{
    public static function isValidName(string $name) : bool
    {
        return (preg_match("/^[a-zA-Z0-9-_.]+$/", $name));
    }

}