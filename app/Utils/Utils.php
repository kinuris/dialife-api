<?php

namespace App\Utils;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Utils
{
    public static function parseJWT($jwt)
    {
        try {
            $decoded = JWT::decode($jwt, new Key(getenv("SECRET_KEY"), 'HS256'));

            return (array) $decoded;
        } catch (ExpiredException) {
            return null;
        }
    }
}
