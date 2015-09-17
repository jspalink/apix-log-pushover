#Pushover logger for Apix Log

An extension for the [Apix/Log](https://github.com/frqnck/apix-log) PSR-3 logger that sends log messages via [Pushover.net](https://pushover.net/api) via the 3rd party [Pushy](https://github.com/sqmk/Pushy) API implementation.

Apix Log was written by Franck Cassedanne (@frqnck). This extension is by Jonathan Spalink (@jspalink) and is released under the BSD-3 license.

##Installation

You can install the Pushover logger using [composer](http://getcomposer.org):

```json
{
  "require": {
    "jspalink/apix-log-pushover": "dev-master"
  }
}
```

See [composer](http://getcomposer.org) and [packagist](https://packagist.org)  for more information.

##Usage

Create an Apix Pushover Log instance, providing pre-configured Pushy Client and User instances to the constructor.
The new Log instance will be used for all subsequent messages sent through to Pushover.

By default, the logger will send a push notification for each log message received.
Especially given Pushover's [monthly limitations](https://pushover.net/api#limits) and
["Being Friendly"](https://pushover.net/api#friendly) clases, I recommend calling
`$logger->setDeferred(true)` to aggregate log messages and send them in one message
when the destructor is called.

There is also a character length limitation for Pushover of 1024 characters.  The
Pushover Logger will truncate at 1024 characters and will not indicate that the
message would have been longer.  This means that you may possibly lose valuable
logging information if you aggregate too much at once.

##Example

```php
// Create Pushy Client and User instances
$pushy_client = new Pushy\Client('APPLICATION KEY');
$pushy_user = new Pushy\User('USER/GROUP ID');

$logger = new Apix\Logger\Pushover($pushy_client, $pushy_user);
$logger->setDeferred(true);
$logger->info('Info about something');
$logger->error('An error occurred');
```
