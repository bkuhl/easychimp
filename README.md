# Easychimp
[![Latest Stable Version](https://poser.pugx.org/bkuhl/easychimp/v/stable.png)](https://packagist.org/packages/bkuhl/easychimp) [![Total Downloads](https://poser.pugx.org/bkuhl/easychimp/downloads.png)](https://packagist.org/packages/bkuhl/easychimp) [![Build Status](https://travis-ci.org/bkuhl/easychimp.svg?branch=master)](https://travis-ci.org/bkuhl/easychimp) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bkuhl/easychimp/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bkuhl/easychimp/?branch=master)

Easychimp makes integrating a PHP/Laravel app with Mailchimp's API (version 3) dead simple.  Functionality is currently limited to managing subscriptions on a list because that's what I needed for a project.  I'd welcome pull requests that add additional functionality.

* [Usage](#usage)
* [Installation](#installation)
* [Laravel](#laravel)
* [Contributing](https://github.com/bkuhl/easychimp/blob/master/CONTRIBUTING.md)

# Usage

```php
$easychimp = new Easychimp\Easychimp($apiKey);
$easychimp->isSubscribed($listId, $email); // boolean
$easychimp->subscribe($listId, $email, $firstName = null, $lastName = null); // boolean
$easychimp->unsubscribed($listId, $email); // boolean
```

# Installation

```
composer require bkuhl/easychimp:dev-master
```

# Laravel
You can register the [service provider](http://laravel.com/docs/master/providers) in `config/app.php`

```php
'providers' => [
    ...
    Easychimp\ServiceProvider::class
]
```

To use the [facade](http://laravel.com/docs/master/facades), add the following to `config/app.php`:

```php
'aliases' => [
    ...
    'Easychimp' => Easychimp\MailchimpFacade::class
]
```

Define the `MANDRILL_API_KEY` environmental variable.  [Get your API key here](https://us1.admin.mailchimp.com/account/api-key-popup/).