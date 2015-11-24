<?php

namespace Easychimp;

use Mailchimp\Mailchimp;

class Easychimp
{

    /** @var Mailchimp */
    protected $api;

    public function __construct($apiKey)
    {
        $this->api = new Mailchimp($apiKey);
    }

    /**
     * @param $id
     *
     * @return \Easychimp\MailingList
     */
    public function mailingList($id)
    {
        return new MailingList($this->api, new Support(), $id);
    }
}