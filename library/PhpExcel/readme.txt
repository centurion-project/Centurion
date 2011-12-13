PHP-Excel
=========
This is a small library written in PHP to generate an Excel
file (in MicrosoftXML format) out of a two-dimensional array.
It is NOT a full-grown solution to handle some serious data
interchange between databases <> PHP <> Excel, but a small
and simple way of throwing out an XML natively readable by
Excel to the browser.

Author:  Oliver Schwarz <oliver.schwarz@gmail.com>
Project: http://code.google.com/p/php-excel/
Issues:  http://code.google.com/p/php-excel/issues/list
Version: 1.1

Version 1.1
-----------
After revitalizing the project this version now addresses
several issues in the bugtracker. On my way to version 2
I wanted to publish an intermediate version to fix some
stupid errors I did in the first run.

Version 2 now allows:
* The setting of an encoding (default: UTF-8)
* The automagic type identification for strings/numbers

It fixes:
* An issue (#3) with large arrays and implode
* An issue (#7) with other charsets than iso-8859-1
* An issue (#9) with XML entity encoding (still not 100%)

Version 1
---------
This was the first version, released on March, 8th 2007. It
was usable for many people using the ISO-8859-1 charset and
for small arrays. However, it had some serious flaws using
other charsets or using larger data containers.

License
-------
Please see the included license.txt for details (it's MIT
license) or visit:
http://www.opensource.org/licenses/mit-license.php

...for more details.

Tutorial
--------
To get the export running, first create a two-dimensional
array (please stick to 2 dimensions, the library does not
work with complex arrays):

$a = array();
for($i=0;$i<10;$i++)
        $a[] = array('Cell' . $i);

Instanciate the library and give the array as input:

$xls = new Excel_XML();
$xls->addArray($a);

Generate the XML/Excel file. This method should trigger
the browsers "Save as..." dialog:

$xls->generateXML('test');

Optional values
---------------
1) You may set your own charset in the constructor of
the class:

$xls = new Excel_XML('UTF-8');

2) You may activate/deactivate type identification for
table cells (strings/numbers):

$xls = new Excel_XML('UTF-8', true);

Whereas the values are: 'true' = type identification
active, 'false' (default) = type identification inactive.

3) You may set the worksheet title directly in the
constructor:

$xls = new Excel_XML('UTF-8', true, 'Testsheet');

Problems, Errors, Help
----------------------
If problems or error occur I unfortunately can not provide
any support. However, please visit the projects website and
post an issue to the comments or bugtracker. Thanks. 