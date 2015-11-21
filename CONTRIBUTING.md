# Contributing to Easychimp

### Running Tests Locally

Integration tests are run using phpspec and actually utilize the Mailchimp API.  To run the tests, create a **.env.php** file in the project root with the below contents substituting values as necessary.

```
<?php

putenv('MAILCHIMP_API_KEY=[YOUR_API_KEY]');
putenv('MAILCHIMP_TEST_LIST_ID=[YOUR_TEST_LIST_ID]');
```

Please include integration tests for any new features.