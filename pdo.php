<?php


namespace App;

use \PDO;

class Connection
{

    public static function getPDO(): PDO
    {

        $user = 'root';
        $password = 'M@ssoutre2013';

        return new PDO('mysql:host=localhost;dbname=portail_massoutre', $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public static function getPDO_2(): PDO
    {
        $user = 'root';
        $password = 'M@ssoutre2013';

        return new PDO('mysql:host=localhost;dbname=massoutre', $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}