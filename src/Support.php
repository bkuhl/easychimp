<?php

namespace Easychimp;

class Support
{
    public function hashEmail(string $email) : string
    {
        return md5(strtolower($email));
    }
}