Place to Experiment API
========

## Environmental variables

Variables that are specific to where the application is deployed to. These are specified at the web server level. Example variables: timezone, database_name, database_user, etc. The application fetches these variables via the PHP function getenv('variable_name')

## Configuration

Platform specific configurations are stored in .yaml files in the /platform_config folder. A configuration file is selected base on the value of the 'platform_config' environment variable.

## Structure

The code is inspired by the Domain Driven Design (DDD) paradigm. Most if it's code is /src with the Silex framwork providing a very thin HTTP routes interactor.

###### **Routes**

The http web request is handled by the Silex framework. Routes are defined in XXXXRoutes.php files in /silex/ directory.

###### **Domain Objects**
Example:
- src/Experiment/Experiment.php
- src/Tag/Tag.php
- src/User/User.php

These classes encaplsulate the business rules and logics of experementation.

###### **Repositories**
Example:
- src/Experiment/ExperimentRepository.php

These classes are for retrieving and saving domain objects to the database. The domain objects are not supposed to know anything about the persistance (database) layer.

###### **Interactors**
Example:
- src/User/UserInteractor.php

These classes usually work in between the routes and the domain objects and repositories. They are comparable to controlers. They usuallly perform certain use-cases with one or more domain object.

###### **Silex**
- silex/

This is a minimal framework that bootstrap the appliction and it's dependencies. It also defines http routes, takes the request and modifies it as appropriate before handing a task over to the interactors. Silex should just deal with the application and the web logic. No complex domain specific logic should be coupled with the framework.

Typically, this is what happens with a web request:
Http Request -> Silex Route -> Interactor -> Repositories and Domain Entities

The routes are defined in routes such as /silex/AgitatorUserRoutes.php

## Public HTML
The folder public_html/ is the only folder that should be publicly exposed when deployed on a web server. The webserver should be configured to use this as it's "Document root".

### public_html/index.php
This is the bootstrap file that launches the application. Otherwise, PHP files should mostly not be under this folder.

### public_html/static/
This folder contains static files that are not PHP, such as images, CSS files, JavaScript files, etc.

## Dependencies
Place to Experiment uses [Composer] (https://getcomposer.org/) to manage dependencies. This means that external, third party software packages that are needed to run Place to experiment are defined in the file composer.json and the Composer program which is installed on the developers computer is used to retrieve these packages.

## Setting up a (local) development environment (specifically on Windows, but should be similar for other platforms)

### Prerequisites

* [Composer] (https://getcomposer.org/)
* LAMP stack (such as [XAMPP] (https://www.apachefriends.org/index.html) or [Laragon] (https://laragon.org/))
  * MySQL 5.7
  * PHP 5.6
* PHP extensions you need to activate:
  * php_fileinfo (needed for server side image validation)

### Setting up
* Run **composer install** to get dependencies
* Set **public_html/** as the *Document root* in your VirtualHost configuration file.
* Prepare a database
  * Import the sample database to your local database server.
  * depending on your settings, you may need to disable [SQL STRICT mode](https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html#sql-mode-strict)
    * Usually, you can do that by modifying your **my.ini** file; find the line for *sql_mode* and remove `STRICT_ALL_TABLES` or `STRICT_TRANS_TABLES`
* Set the following ENV variables in your Apache settings:
  * platform_config: The name of the config file to use. The files are located in the **/platform_config** folder.
  * environment: *development* or *production*.
  * protocol: *http* or *https*.
  * platform_secret_key: A 32-character long random string used for encryption.
  * database_name: The name of the database.
  * database_user: The user for the database.
  * database_pass: The password for the database user.
  * database_hostname: Usually *localhost*.
  * allow_api_access: *yes* or *no*.
  * AWS_ACCESS_KEY: The Amazon AWS access key string. Needed for image uploading functionality.
  * AWS_SECRET_ACCESS_KEY: The Amazon AWS secret access key string. Needed for image uploading functionality.

#### Setting ENV variables in Laragon
  * You can set them in your *sites-enabled/auto.<name>.conf* file, like this:
~~~~
   ...
   </Directory>
   SetEnv platform_config kokeilunpaikka.yaml
   ...
 </VirtualHost>
~~~~
