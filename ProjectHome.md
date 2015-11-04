**17th November 2009 There is a bug in the current version of the filter. A workaround has been added into the source which resolves the issue for now. Please get the latest source version to fix this issue.**

This is a collection of PHP classes which can be used as spam filters or used in other text classification problems.

Currently there is only one class which is a bayesian filter implementation aimed at identifying spam and ham. It is based on Paul Grahams "A Plan for Spam" http://www.paulgraham.com/spam.html

Planned objects include a vector space object which will allow for greater accuracy and classification of all forms of text allowing for automated categorisation.

All are currently tied to MySQL as the backend for data storage.

All code is dual licenced under the GNU General Public License v2 and v3

Update - 14th April 2010 Currently working on a major modification to clean up the logic and move everything over to using PDO for database connectivity. A sample of this can be found here https://code.google.com/p/phpspamdetection/source/browse/trunk/SpamChecker.class.php