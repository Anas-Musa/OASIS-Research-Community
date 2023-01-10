<?php

namespace v1\Models;

use PDO;

class Database
{
    private static $DBConnection;

    public static function connectDB()
    {
        if (self::$DBConnection == null) {
            // self::$DBConnection = new PDO('mysql:host=localhost;dbname=oasighag_orc;charset=utf8', 'oasighag_root', '[a8}vUGi8-hq');
            self::$DBConnection = new PDO('mysql:host=localhost;dbname=orc;charset=utf8', 'root', '');
            self::$DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$DBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$DBConnection;
    }
}
