# edudip next API example wrapper

Provides an example PHP wrapper for using the edudip next API (https://www.edudip-next.com/)
This code can be used to list existing webinars, create new webinars and register new participants.

## How to run

1. Clone this repository
2. Run `composer install`
3. Open `src/example.php` and set your API key
4. Execute `example.php`on the command line

## How to integrate the library in an existing project

This library uses composer for dependency management. If you already use composer (https://getcomposer.org/) add the following  entry to the "repositories" field in your composer.json:

```javascript
"repositories": [
    {
        "url": "https://github.com/edudip/next-api-client.git",
        "type": "git"       
    }
],
```

Then add the following lines to the ```autoload``` property:

```javascript
"autoload": {
    "psr-4": {
        "Edudip\\Next\\ApiClient\\": "vendor/edudip/next-api-client/src/Edudip/Next/ApiClient"
    }
},
```

Finally run `composer update` to install the api client as dependency.
