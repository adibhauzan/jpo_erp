<?php

namespace App\Utils;

class GenerateRandomToken
{
    public static function generateRandomToken($input)
    {
        $tokenLength = 15;

        $randomToken = bin2hex(random_bytes($tokenLength));

        $encodedToken = hash_hmac('sha256', $randomToken, $input);

        return substr($encodedToken, 0, $tokenLength);
    }
}
