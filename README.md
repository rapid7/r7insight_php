Logging to InsightOps with Php 
=======================================

With these simple steps you can send your Php application logs to InsightOps.

Firsly you must register an account on https://www.rapid7.com/products/insightops/try/, this only takes a few seconds.

Logentries Setup
----------------

When you have made your account on InsightOps, log in and use the "Quick Add" option in the "Add data" page to add a new log, using the Token TCP option.

The log token (unique identifier for the log) and data endpoint will be displayed. Take a note of these.


Parameter Setup
---------------
Inside the `r7insight_php-master` folder, open `r7insight.php` as you need to fill in a parameter, `LOG_TOKEN`.

`LOG_TOKEN` is the token you copied earlier from the InsightOps UI, and associates that logger with the log file in InsightOps.

In the `REGION` field, enter the first 2 characters from the data endpoint that you copied earlier - e.g. us, eu, ca, au etc.


Adding a Custom Host Name and Host ID sent in your PHP log events
---------------
To set a custom Host Name that will appear in your PHP log events as Key / Value pairs:

Inside the `r7insight_php-master` folder, open `r7insight.php` and fill in the parameters as follows:

	$HOST_NAME_ENABLED = true;

	$HOST_NAME = "Custom_host_name_here";

	$HOST_ID = "Custom_ID_here_12345";

The $HOST_NAME constant can be left as an empty string, and the library will automatically attempt to assign a host name from 
your local host machine and use that as the custom host name.

To set a custom Host ID that will appear in your PHP log events as Key / Value pairs:
Enter a value instead of the empty string in $HOST_ID = "";
If no $HOST_ID is set and the empty string is left unaltered, no Host ID or Key / Value pairing will appear in your PHP logs.

If you want to send log events in JSON format, then set the `$USE_JSON` field to `true`. If you set it to `false`, then the logs will be sent in KVP (Key Value Pair) format.

Code Setup
----------

Now you need to download the library from the Downloads Tab, unzip and place the folder in your apps directory.

To use it in your code, enter the following lines, making changes accordingly if you place it in a different location.

	require dirname(__FILE__) . '/r7insight_php-master/r7insight.php';
	
	// The following levels are available
	$log->Debug(" ");
	$log->Info(" ");
	$log->Notice(" ");
	$log->Warn(" ");
	$log->Crit(" ");
	$log->Error(" ");
	$log->Alert(" ");
	$log->Emerg(" ");
	
	
updated 2014-09-03 11:55
