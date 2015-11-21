<?php

namespace spec\Easychimp;

use Mailchimp\Mailchimp;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EasychimpSpec extends ObjectBehavior
{
    /** @var string */
    protected $listId;

    function let()
    {
        # Load environment variables for local development
        include '.env.php';

        # We need a list to test with
        $this->listId = getenv('MAILCHIMP_TEST_LIST_ID');

        $api = new Mailchimp(getenv('MAILCHIMP_API_KEY'));
        $this->beConstructedWith($api);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Easychimp\Easychimp');
    }

    function it_can_manage_subscribers()
    {
        $emailAddress = 'benkuhl+'.time().'@gmail.com';
        $this->subscribe($this->listId, $emailAddress, 'FirstName', 'LastName')->shouldReturn(true);
        $this->isSubscribed($this->listId, $emailAddress)->shouldReturn(true);
        $this->unsubscribe($this->listId, $emailAddress)->shouldReturn(true);
        $this->isSubscribed($this->listId, $emailAddress)->shouldReturn(false);
    }

    function it_should_classify_nonexistant_emails_as_unsubscribed()
    {
        $this->isSubscribed($this->listId, microtime().'@the.moon')->shouldReturn(false);
    }

    function it_should_fail_to_subscribe_invalid_email()
    {
        $this->shouldThrow()->during('subscribe', [
            $this->listId,
            'Waffles'
        ]);
    }
}
