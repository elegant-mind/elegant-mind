Elegant-Mind
==============

These is the code base for a fork of MOFX Evolution.
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

### Make Evo and its core components run on other RDMBS ###
Make Evo and its core components should run on PostgreSQL and MySQL with Innno DB using 
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

##Modification to Evolution 1.0.6
===
##Changelog

###12/04/2012

* Re-factored manager/includes/header.inc.php to use a template. All data to be output is loaded into the $modx->placeholders array. [View](https://github.com/sottwell/evolution/commit/15908d2ab334f88b5ae209ec24680a29c8603b89)
* A new template file was created in manager/media/style/<ThemeName>/templates/header.php containing only the HTML with placeholders.

###08/04/2012

* Began update of sample site
* Replaced WebLogin snippets with WebLoginPE version 1.3.2b. This version is an improved version that offers the option of included external config files, and has the language array keys as informative text rather than numbers. For example, $wlpe_lang['username_used'] instead of $wlpe_lang[14]. [View](https://github.com/sottwell/evolution/commit/ad2db9c9d651d5fbd97db3c29b2de79273d3fe61)

###02/04/2012

* Fixed bug in ManagerManager when using manual setting for jQuery location [View](https://github.com/sottwell/evolution/commit/a13ffa16a296e16ae320571c38c313f27f6e3871) 
* Moved Template Variables to its own tab [View](https://github.com/sottwell/evolution/commit/1a2c0546c93207b33dcc1138f820952825c22e76)


###31/03/2012 

* Updated master to 1.0.6
* Modified Template Editing page to not show assigned TVs if there are none [View](https://github.com/sottwell/evolution/commit/da99fa130ef80eeac838e9cf435c78993eba337e)
* Began creating a new default sample template and sample site based on HTML 5 [View](https://github.com/sottwell/evolution/commit/af8abf80d60813d8ff7896bb9b32257bd9b04de0)

###Previous Modifications:

* Modify manager/index.php line 170 to check for SESSION variable and HTTP_REFERER to permit QM+ access when user is not allowed Manager access
* Modify qm.inc.php line 55 to set a SESSION variable to enable QM+ access
* Improved manager lockout display
* Moved Manager templates from assets/templates/manager to manager/media/style/themeName/templates/
* Updated ManagerManager plugin to 0.3.11
* Updated TinyMCE to 3.5b2
