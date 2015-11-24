<?php

namespace Easychimp;

class Support
{
    /**
     * @param $email
     * @return string
     */
    public function hashEmail($email)
    {
        return md5(strtolower($email));
    }
}