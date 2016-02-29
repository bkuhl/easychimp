<?php

namespace spec\Easychimp;

use Mailchimp\Mailchimp;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Easychimp\MailingList;
use Easychimp\InvalidApiKey;

class EasychimpSpec extends ObjectBehavior
{

    function let()
    {
        # Load environment variables for local development
        if (file_exists('.env.php')) {
            require_once '.env.php';
        }

        $this->beConstructedWith(
            new Mailchimp(getenv('MAILCHIMP_API_KEY'))
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Easychimp\Easychimp');
    }

    function it_creates_list_instances()
    {
        $this->mailingList(time())->shouldBeAnInstanceOf(MailingList::class);
    }

    function it_fails_validation_on_invalid_calls()
    {
        $this->validateKey()->shouldThrow(InvalidApiKey::class);
    }
}
