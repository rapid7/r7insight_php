# r7insight_php

With these simple steps you can send your PHP application logs to the Insight Platform.

- [r7insight_php](#r7insight_php)
	- [Insight Platform Setup](#insight-platform-setup)
	- [Parameter Setup](#parameter-setup)
	- [Adding a Custom Host Name and Host ID sent in your PHP log events](#adding-a-custom-host-name-and-host-id-sent-in-your-php-log-events)
	- [Code Setup](#code-setup)

## Insight Platform Setup

Once you have made your account on the Insight platform, log in and use the "Quick Add" option in the "Add data" page to add a new log, using the Token TCP option.

The Log Token and data endpoint will be displayed.  
The Log Token is a unique identifier for the log allowing for write-only access.
Take note of these.


## Parameter Setup
Inside the `r7insight_php` folder, open `r7insight.php` and fill in the `LOG_TOKEN` parameter.

`LOG_TOKEN` is the token you copied earlier from the Insight Platform UI, and associates that logger with the log in Insight Platform.

In the `REGION` field, enter the first 2 characters from the data endpoint that you copied earlier - e.g. `us`, `eu`, `ca`, `au` etc.


Adding a Custom Host Name and Host ID sent in your PHP log events
---------------
To set a custom Host Name that will appear in your PHP log events as Key / Value pairs:

Inside the `r7insight_php` folder, open `r7insight.php` and fill in the parameters as follows:

	$HOST_NAME_ENABLED = true;

	$HOST_NAME = "Custom_host_name_here";

	$HOST_ID = "Custom_ID_here_12345";

The `$HOST_NAME` constant can be left as an empty string, and the library will automatically attempt to assign a host name from 
your local host machine and use that as the custom host name.

To set a custom Host ID that will appear in your PHP log events as Key / Value pairs:
Enter a value instead of the empty string in `$HOST_ID = "";`  
If no `$HOST_ID` is set and the empty string is left unaltered, no Host ID or Key / Value pairing will appear in your PHP logs.

If you want to send log events in JSON format, then set the `$USE_JSON` field to `true`. If you set it to `false`, then the logs will be sent in KVP (Key Value Pair) format.

## Code Setup

Now you need to download/clone the library from GitHub and place the folder in your apps directory.

To use it in your code, enter the following lines, making changes accordingly if you place it in a different location.
```php
require dirname(__FILE__) . './r7insight_php/r7insight.php';

// The following levels are available
$log->debug("Isn't that the fault of the voters?");
$log->info("That's because sometimes I go by my maiden name.");
$log->notice("Give me the strongest thing you got.");
$log->crit("Awfully big moustache.");
$log->error("Yeah, and when I find the guy that did it...");
$log->alert("That's ok. I sometimes go by my maiden name.");
$log->emerg("Every time I order out.");
```
