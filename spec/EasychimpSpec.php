<?php

namespace spec\Easychimp;

use Easychimp\EmailAddressNotSubscribed;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EasychimpSpec extends ObjectBehavior
{
    protected static $EMAIL;

    /** @var string */
    protected $listId;

    function let()
    {
        # Load environment variables for local development
        if (file_exists('.env.php')) {
            require_once '.env.php';
        }

        # We need a list to test with
        $this->listId = getenv('MAILCHIMP_TEST_LIST_ID');

        $this->beConstructedWith(getenv('MAILCHIMP_API_KEY'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Easychimp\Easychimp');
    }

    function it_throws_exception_when_subscriber_doesnt_exist()
    {
        $this->shouldThrow(EmailAddressNotSubscribed::class)
            ->duringSubscriberInfo($this->listId, self::$EMAIL);
    }

    // ----- The following few tests share email addresses

    function it_can_subscribe_new_emails()
    {
        self::$EMAIL = uniqid().'@gmail.com';
        $this->subscribe($this->listId, self::$EMAIL, 'FirstName', 'LastName')
            ->shouldReturn(true);
    }

    function it_can_update_subscriber_info()
    {
        $this->updateSubscriber($this->listId, self::$EMAIL, 'FirstName', 'LastName')
            ->shouldReturn(true);
    }

    function it_throws_exception_when_updating_subscriber_info()
    {
        $email = uniqid().'@gmail.com';
        $this->shouldThrow(EmailAddressNotSubscribed::class)
            ->duringUnsubscribe($this->listId, $email);
    }

    function it_can_fetch_subscribers_info()
    {
        $this->subscriberInfo($this->listId, self::$EMAIL)
            ->shouldHaveKeyWithValue('email_address', self::$EMAIL);
    }

    function it_shows_email_as_subscribed()
    {
        $this->isSubscribed($this->listId, self::$EMAIL)
            ->shouldReturn(true);
    }

    function it_unsubscribes_email_addresses()
    {
        $this->unsubscribe($this->listId, self::$EMAIL)
            ->shouldReturn(true);
    }

    // ----- end shared email address


    function it_throws_exception_when_unsubscribing_emails_that_were_never_subscribed()
    {
        $this->shouldThrow(EmailAddressNotSubscribed::class)
            ->duringUnsubscribe($this->listId, uniqid().'@gmail.com');
    }

    function it_should_classify_nonexistant_emails_as_unsubscribed()
    {
        $this->isSubscribed($this->listId, microtime().'@the.moon')
            ->shouldReturn(false);
    }

    function it_should_fail_to_subscribe_invalid_email()
    {
        $this->shouldThrow()->during('subscribe', [
            $this->listId,
            'Waffles'
        ]);
    }

    function it_should_list_interest_categories_and_interests()
    {
        $category = $this->interestCategories($this->listId)->shouldHaveCount(1)[0];

        $this->interests($this->listId, $category->id)->shouldHaveCount(5);
    }
}
