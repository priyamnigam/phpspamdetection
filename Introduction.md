# Introduction #

Quick introduction on how to set up and use the spam filter.


## Database ##

The first step is to set up the database tables which will be used to hold the filter spam weignts. Inside the source directory and the packages the file sql.sql contains the sql required to set up the tables which are used by the filter.

http://code.google.com/p/phpspamdetection/source/browse/trunk/sql.sql


## Setup ##

An example of how to use the filter can be found in source and in packages. Look at example.php http://code.google.com/p/phpspamdetection/source/browse/trunk/example.php

The first step is to ensure you have a database connection.

```
$link = mysql_connect('localhost', 'database_user', 'user_password') or die('Could not connect: ' . mysql_error());
mysql_select_db('my_database') or die('Could not select database');
```

Then include the filter and create an instance of the object.

```
include_once('spamchecker.php');
$spamchecker = new spamchecker();
```

You are then ready to go.

## Usage ##

### Training ###

To train on spam use the following method call

```
$spamchecker->train('this is spam viagra',true);
```

To train on ham use the following method call

```
$spamchecker->train('this is not spam kittens',false);
```

### Check Spam Rating ###

To find the spam rating of some text use the following method call.

```
$spamrating = $spamchecker->checkSpam('is this spam');
```

The return value will be a number from 0 to 1 which indicates the level of spamicity. The closer a value is to 1 the more likely it is for the text to be spam.

### Reset the Filter ###

To reset the filter use the following method.

```
$spamchecker->resetSpam();
```

_WARNING_ this will remove **everything** that you have trained, so you will end up with essentially a newly installed filter which is unable to classify anything.