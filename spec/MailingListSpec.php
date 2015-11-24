<?php

namespace spec\Easychimp;

use Easychimp\EmailAddressNotSubscribed;
use Easychimp\Support;
use Mailchimp\Mailchimp;
use PhpSpec\ObjectBehavior;
use Easychimp\MailingList;
use Prophecy\Argument;

class MailingListSpec extends ObjectBehavior
{
    protected static $EMAIL;

    function let()
    {
        # Load environment variables for local development
        if (file_exists('.env.php')) {
            require_once '.env.php';
        }

        $this->beConstructedWith(
            new Mailchimp(getenv('MAILCHIMP_API_KEY')),
            new Support(),
            getenv('MAILCHIMP_TEST_LIST_ID')
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MailingList::class);
        $this->id()->shouldReturn(getenv('MAILCHIMP_TEST_LIST_ID'));
    }

    function it_throws_exception_when_subscriber_doesnt_exist()
    {
        $this->shouldThrow(EmailAddressNotSubscribed::class)
            ->duringSubscriberInfo(self::$EMAIL);
    }

    // ----- The following few tests share email addresses

    function it_can_subscribe_new_emails()
    {
        self::$EMAIL = uniqid().'@gmail.com';
        $this->subscribe(self::$EMAIL, 'FirstName', 'LastName')
            ->shouldReturn(true);
    }

    function it_can_update_subscriber_info()
    {
        $this->updateSubscriber(self::$EMAIL, 'FirstName', 'LastName')
            ->shouldReturn(true);
    }

    function it_throws_exception_when_updating_subscriber_info()
    {
        $email = uniqid().'@gmail.com';
        $this->shouldThrow(EmailAddressNotSubscribed::class)
            ->duringUnsubscribe($email);
    }

    function it_can_fetch_subscribers_info()
    {
        $this->subscriberInfo(self::$EMAIL)
            ->shouldHaveKeyWithValue('email_address', self::$EMAIL);
    }

    function it_shows_email_as_subscribed()
    {
        $this->isSubscribed(self::$EMAIL)
            ->shouldReturn(true);
    }

    function it_unsubscribes_email_addresses()
    {
        $this->unsubscribe(self::$EMAIL)
            ->shouldReturn(true);
    }

    // ----- end shared email address


    function it_throws_exception_when_unsubscribing_emails_that_were_never_subscribed()
    {
        $this->shouldThrow(EmailAddressNotSubscribed::class)
            ->duringUnsubscribe(uniqid().'@gmail.com');
    }

    function it_should_classify_nonexistant_emails_as_unsubscribed()
    {
        $this->isSubscribed(microtime().'@the.moon')
            ->shouldReturn(false);
    }

    function it_should_fail_to_subscribe_invalid_email()
    {
        $this->shouldThrow()->during('subscribe', ['Waffles']);
    }

    function it_should_list_interest_categories_and_interests()
    {
        $category = $this->interestCategories()->shouldHaveCount(1)[0];

        $this->interests($category->id)->shouldHaveCount(5);
    }
}
