# StopForumSpam.com PHP API Client
I built this API Client in a couple of hours to use with software I am building. It has all the functionality you should need, but I am sure the functionality could be extended. I welcome anyone to submit a [PR](https://github.com/robertgallione/stopforumspam-php/pulls) if you would like to add any additional functionality. This has been bug tested heavily, however if you find any bugs, please open up an [issue](https://github.com/robertgallione/stopforumspam-php/issues) and I will review and resolve it when I get time.

##### About StopForumSpam.com
Stop Forum Spam is a free service that records reports of spam on forums, blogs and wikis to name a few. All these records are then made available to you search and view but most importantly, to access in an automated way to block suspected spammers before they can get in the front door. Everyone is familiar with more traditional "solve the word" systems to prevent abuse, Stop Forum Spam is a targeted and specialsed solution to help stop abuse of your website.

## Requirements
* PHP 5.5 or above
* [Composer](https://getcomposer.org/download/)
* This API Client uses [Guzzle](https://github.com/guzzle/guzzle).

## Installation
You must have [Composer](https://getcomposer.org/download/) installed to use this API Client.
```
composer require robertgallione/stopforumspam-php
```
If you aren't using a framework such as [Laravel](https://laravel.com/), you will need to include the `autoload.php` file.
```php
require 'vendor/autoload.php';
```

## Usage


### Initialization
```php
$apiClient = new StopForumSpam\Api('YOUR_API_KEY_GOES_HERE'); // Get your API Key at: http://www.stopforumspam.com/signup
```


### Setting Data
#### setIp($ip = null)
This setter allows you to set one or multiple ip addresses to send.
1. Set a single IP Address.
```php
$apiClient->setIp(); // This will get the current ip address
$apiClient->setIp('127.0.0.1');

```
2. Set multiple IP Addresses (FOR BULK TESTING ONLY).
```php
$apiClient->setIp([
  '127.0.0.1',
  '127.0.0.2',
  '127.0.0.3'
]);
```
#### setEmail($email)
This setter allows you to set one or multiple email addresses to send.
1. Set a single Email Address.
```php
$apiClient->setEmail('example@mail.com');
```
2. Set multiple Email Addresses (FOR BULK TESTING ONLY).
```php
$apiClient->setEmail([
  'example1@mail.com',
  'example2@mail.com',
  'example3@mail.com'
]);
```
#### setUsername($username)
This setter allows you to set one or multiple usernames to send.
1. Set a single Username.
```php
$apiClient->setUsername('example');
```
2. Set multiple Usernames (FOR BULK TESTING ONLY).
```php
$apiClient->setUsername([
  'example1',
  'example2',
  'example3'
]);
```
#### setMaxConfidence($confidence)
This setter allows you to set the maximum confidence level allowed when testing for confidence with the function `setIsConfidence();` and `setConfidenceData();`.

**The Max Confidence defaults to 25.0 when not set**
1. Set Max Confidence Level.
```php
$apiClient->setMaxConfidence(23.4);
```
#### setExplanation($explanation)
This setter allows you to set an explanation to send when reporting data.
1. Set an Explanation.
```php
$apiClient->setExplanation('Spamming');
```


### Retrieving Data from the API
#### setResultData($return = false)
This will make a request to the [StopForumSpam.com](http://stopforumspam.com) API using the data that has been set.
If you set BULK data earlier, it will make a bulk request, if not it will make a regular request. The request is made through [Guzzle](https://github.com/guzzle/guzzle) using the POST method. 

**YOU MUST SET ATLEAST ONE PARAMATER FOR THIS FUNCTION TO WORK!**  
*If `$return = true;` this function will return the data retrieved from the API.*
```php
$apiClient->setResultData(); // After data has been set
```

### Getting Retrieved Data from the API
#### getResultData()
This will return the retrieved data that was received from running `setResultData();`.

**YOU MUST RUN `setResultData();` FOR THIS FUNCTION TO WORK!**
```php
$apiClient->getResultData(); // Will return an array.
```


### Parsing Data
#### setIsConfidence($type = 'ip', $confidence = null, $return = true)
**THIS FUNCTION ONLY WORKS FOR NON-BULK DATA**

This will take the data that was retrieved by running `setResultData();` and will `return true` if the confidence level retrieved from the API is lower than the max confidence level set. You must specify the type of data you want to run this function with.

For example, if you only set the Username when you ran the function `setResultData();`, you must specify `setIsConfidence('username');`.

You can also specify the confidence level directly through this function if you did not set it with `setMaxConfidence($confidence);`.
To do so, you must specify the type, then the max confidence level like this: `setIsConfidence('email', 15.6);`. **The Max Confidence Level defaults to 25 if not set.**
```php
$apiClient->setIsConfidence($type = 'email'); // $types -> ip, email, username
```
Additionally, if `$return = false` this function will always `return true`.
### getIsConfidence()
You can run this function after running `setIsConfidence();` to get the saved response from the last time you ran the function. This function will simply `return true` or `return false`.


### setConfidenceData($type = 'ip', $confidence = null, $return = true)
**THIS FUNCTION ONLY WORKS FOR BULK DATA**

This will take the data that was retrieved by running `setResultData();` and will `return true` if the confidence level retrieved from the API is lower than the max confidence level set. You must specify the type of data you want to run this function with.

For example, if you only set Usernames when you ran the function `setResultData();`, you must specify `setConfidenceData('username');`.

You can also specify the confidence level directly through this function if you did not set it with `setMaxConfidence($confidence);`.
To do so, you must specify the type, then the max confidence level like this: `setConfidenceData('email', 15.6);`. **The Max Confidence level defaults to 25 if not set.**
```php
$apiClient->setConfidenceData($type = 'email'); // $types -> ip, email, username
```
If `$return = true`, the response from this function will include the value from each entry and true/false depending on if the confidence level was lower than the max level. Below in an example of the response from this function.
```
[
  "ip" => [
    '127.0.0.1' => false,
    '127.0.0.2' => true,
    '127.0.0.3' => false
  ]
]
```
### getConfidenceData()
You can run this function after running `setConfidenceData();` to get the saved response from the last time you ran the function. Below in an example of the response.
```
[
  'email' => [
    'example@mail.com' => false,
    'example2@mail.com' => true,
    'example3@mail.com' => false
  ]
]
```
