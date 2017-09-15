# Tests built with codeception

## How to run in terminal

1. Add the codeception alias to the PATH.
2. On Linux:

  alias codecept='./vendor/bin/codecept' (on Linux)

3. On Windows: You don't need to do anything special on Windows, because the repo contains a file **codecept.bat** that redirects your commands to the proper executable (more info: [Setting up Codeception to Run on Windows](https://blogyii.com/blog/setting-up-codeception-on-windows))

  - Additional info regarding the Windows command-line:

    - Use `--no-colors` to turn off non-supported characters

4. Run a command.

  codecept run api

### Passing command line parameters

In order to pass command line parameters, the best way to do it is to hardcode it in the config under `env` node. Example: Instead of

codecept run api

You can run

codecept run api --env sandbox

## API tests

- The tests are located in the /api folder. XxCept denote single test files, XxCest a collection of tests.
- The support functions are located in _support/ApiTester.php and _support/Helper/Api.php
- The configuration file is api.suite.yml (contains the URL for the API, dependencies and includes)
- The global constants are defined in /api/_bootstrap.php (such as usernames, etc.)

### Api.php

This file contains various support functions used to transform/process data. The basic behavior of the functions is described in inline comments.

### ApiTester.php

This file contains support functions that the Tester object uses in tests. Ideally, the actual requests to the endpoints are only made here and not in tests (currently not always possible/feasible). The functions are named XxResponse. The structure is as follows:

1. Save the current authorization and/or role *
2. Set the proper authorization and/or role / _start request process_ /
3. Prepare the endpoint and/or fields
4. Set headers for the request
5. Perform a request to the endpoint / _end request process_ /
6. (Optional) Grab the response and comment it (only used for debugging)
7. Reset the authorization and/or role

*saving and resetting the role is necessary because each supporting call may need a specific authentication/role and they may differ to the authorization/role of the test scenario.

### XXCest files

These files contain the actual tests and some local support functions. The support functions enable the tests to share resources and therefore remove the need to set up new resources for each test (e.g. creating an experiment, a user... for each test). The shareable resources are saved in Fixtures. The tests themselves are structured as follows:

1. (Optional) Set variables to use in $fields.
2. $I->wantTo('succeed/fail at doing something and (optionally) test the action.')
3. (Optional) Prepare common resources (e.g. $experimentId = $this->getExperimentId($I);)
4. $I->setRole(), $I->setStatus() and $I->setAuthorization() as needed for this test.
5. $I->

  <calltosupportfunctionfromapitester.php> (ideally I call a support function, but sometimes the whole request process is needed, as described in the ###ApiTester.php section)</calltosupportfunctionfromapitester.php>

6. $I->checkResponse(), $I->decodeResponse() and $I->comment() the result.

7. (Optional) Perform the test of the action. The method of testing depends on the specific test scenario.

### XXCept files

These files perform a single test.

## Plan of future work

The following are pending TO-DO items:

- Define the expected behavior regarding the return codes and the format of the response values and open bugs appropriately.
- Go over any //TODO: comments and do the pending work
- Rethink the way roles and authorization is used. (need to differentiate between calls for test and for verification of test.)
- Replace assertions that expect non-200 code with specific code (5xx or 4xx)

## Acceptance tests

- The tests are located in the /acceptance folder. XxCept denote single test files, XxCest a collection of tests.
- The support functions are located in _support/AcceptanceTester.php and _support/Helper/Acceptance.php
- The configuration file is acceptance.suite.yml (contains the URL for the test page, browser setting, etc)
- The global constants are defined in /acceptance/_bootstrap.php (such as usernames, etc.)

**WARNING:** Running these tests against _Internet Explorer_ will DELETE all your locally saved browser data (cache, form data, local storage).

### Pre-requisites

The tests use Selenium WebDriver. To run the tests, you need to have the appropriate browser installed on your system.

You need to start the Selenium WebDriver before you test. To run the [Selenium Standalone Server](http://docs.seleniumhq.org/download/), execute the following command in **/acceptance/bin/**:

java -jar selenium-server-standalone-

<version>.jar</version>

or

On Windows you can run the provided **.bat file**: `selenium.bat`

### Running Acceptance tests

Run the command `codecept run acceptance`.

#### Passing command-line parameters

In order to pass command line parameters, the best way to do it is to hardcode it in the config under the `env` node. Example: Instead of

codecept run acceptance

You can run

codecept run acceptance --env chrome

### Possible issues and solutions

#### IE 11

If you are getting the error **Exception: Unable to get browser** in selenium, you may need to do one or both of the following:

- Adjust the _Protected Mode_ setting.
- Add the domain to _Trusted sites_
Note:  See [Selenium WebDriver and IE11](http://www.michael-whelan.net/selenium-webdriver-and-ie11/) for more details.

#### Edge

Currently, the setting for Selenium do not allow us to clear the local session data for Edge, which means that by default it will retain data such as Local storage. To resolve, you need to change the Edge browser settings to **clear browsing data on browser close**.

Testing on local "virtual host":
- Edge by default ignores any entry in the **hosts** file. To resolve, get the [Windows Loopback Exemption Manager](https://loopback.codeplex.com/) application and tick all the boxes for entries that include "edge".

#### Safari

Requirements:
- A MacOS environment.
- Manually install the [Safari webdriver extension](https://github.com/SeleniumHQ/selenium/wiki/SafariDriver)
