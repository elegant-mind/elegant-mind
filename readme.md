Elegant-Mind
==============

These is the code base for a fork of MODX Evolution.
See the start in the MODX forums: http://forums.modx.com/thread/74766/bringing-evo-back-to-life-again

## Rules ##

A project need rules, if there is more than one developer involved.

### PHP ###
* Functions and procedures do only have one exit.
* Classes, functions and procedures should have a PHPDoc header.
* Unit tests for PHP with PHPUnit should be done.
* Function brackets are starting in a new line.
* IF conditions are always with brackets.
* Concatenate string with a space on the left and the right side of the concatanator.

### SQL ###
* Table and column names with white spaces are not allowed.
* Table and column names, that also represent system commands or functions are not allowed
* Table and colmun names do not need quotes if you respect the previous rules.

## Jobs, that should be done ##
### Change the database class ###
This has do be done, to use other RDBMS than MySQL with MyISAM, for example 
MySQL with InnoDB and PostrgreSQL. On MySQL with InnoDB and PostgreSQL connection
between tables should be done with foreign key constraints.

### Make Elegant-Mindand its core components run on other RDMBS ###
Make Elegant-Mindand its core components should run on PostgreSQL and MySQL with Innno DB using 
transactions and constraints. Other databases as, for example, SQL Server are possible, too.

### Make the user management more comfortable ###
Enable the choice to use separated or non separated front end and back end user as an option.

### Changing the JavaScript library to jQuery ###
jQuery, because of the license, documentation, and its much more much more common.

#### Decision has to be made, which jQuery add-ons to use ####
The file manager and the text editor will be changed to one based on jQuery.

### Change the caching ###
Currently the caching does not scale with more then 5,000 documents.

### Internationalisation of documents ###
Implement a workflow for translations

### Version control ###
Version control with workflows for documents, yes, this should include translated 
documents. For example you work on a document and decide on your which version is 
published.

