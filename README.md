Small PHP Helpers
=================

This set of small libraries is intended as easy shortcuts for everyday situations.

* Conversion: Return nice output for numerical values with units
* Coordinates: Calculate with (WGS84) coordinates
* Form: Quick forms including validation
* HtmlEncode & XmlEncode: Convert PHP structures into output; you may also want to look at json_encode
* Media: Generate HTML for video / audio output for HTML5 with fallbacks
* Messages: Collect status messages from your controller for output to the user
* SuperPDO: Gently improves functionality of PDO, see http://www.php.net/manual/en/book.pdo.php
* Entity & Entities: Using SuperPDO to work with entities
* Tester: Simple unit test in case PhpUnit is not deployable
* toolshed: Missing PHP-functions like quoted <code>echo()</code> or a better replacement for <code>empty()</code>

Integration in Symfony & Silex
------------------------------

1. Search and replace `require` with `# require`
2. Search and replace `# namespace` with `namespace`
3. Search and replace `# use` with `use`
4. Put files in folder matching namespace
5. Think of some clever way to use `toolshed.php` :smirk:

Installation
------------

Via [Bower](http://bower.io/): `bower install fboes/small-php-helpers`

Version
-------

Version: 1.3.0 (2016-03-10)

Legal stuff
-----------

Author: [Frank Boës](http://3960.org)

Copyright & license: See LICENSE.txt
