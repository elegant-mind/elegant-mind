How to run the unit tests with PHPUnit

You need to install PHPUnit on your computer. The easiest way to get it is 
through pear extenstions. Linux distributions offer pear as a package, you only
have to install that package and from there, you install PHPUnit.

The documentation about PHPUnit can be found at http://www.phpunit.de/

To test this system, you need to configure the phpunit.xml file, that is in the
same directory, as this file. This XML configuration file contains the
definition of global constants for these tests.
You need to change the path information in the phpunit.xml file, to fit your
local installation, otherwise you will not be able to run unit tests.

You can run unit tests from a command line using the following command, when
you are in the root directory of the system:
phpunit -c _Test/unit_tests/phpunit.xml _Test/unit_tests/manager/includes/DocumentParserTest.php

Additional configuration has to be done, to get a database connection. This is
has to be done for example for the DocumentParser class. Replace the existing
configuration in the function setUp to enable the tests to use your database.

The database we use, is our demo installation.