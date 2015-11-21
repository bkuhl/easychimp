# Easychimp

Easychimp makes integrating a PHP/Laravel app with Mailchimp's API (version 3) dead simple.

* [Usage](#usage)
* [Installation](#installation)
* [Laravel](#laravel)
    * [Facade](#facade)
    * [Configuration](#configuration)

# Usage

```php
$easychimp->isSubscribed($listId, $email); // boolean
$easychimp->subscribe($listId, $email, $firstName = null, $lastName = null);
$easychimp->unsubscribed($listId, $email);
```

# Installation
Add the following to your composer.json

```json
{
    "require": {
        "bkuhl/easychimp": "dev-master"
    }
}
```

Define the `MANDRILL_API_KEY` environmental variable.  To [obtain an API key](https://us1.admin.mailchimp.com/account/api/), go to mailchimp.com under your profile you will find "Extras" -> "API keys".

# Laravel
You can register the [service provider](http://laravel.com/docs/master/providers) in `config/app.php`

```php
'providers' => [
    ...
    Easychimp\ServiceProvider::class
]
```

#### Facade
Add the following to `config/app.php`:

```php
'aliases' => [
    ...
    'Easychimp' => Easychimp\MailchimpFacade::class
]
```