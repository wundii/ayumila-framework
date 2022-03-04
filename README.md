# PHP Framework Ayumila
A small open source microframework for PHP with any Symfony libraries, Propel ORM ready, PHP-Di, Middleware mechanics, Eventhandler, Security-Injection, automatic Logsystem over RabbitMq and same help classes.

## Installation
composer.json
```
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/wundii/ayumila-framework.git"
    }
],
"require": {
    "wundii/ayumila-framework": "~v0.1"
}
```

## Version History
* 0.1.17
  * added ClassEnum Trait for php8.0
  * adjusted the Toast Class
* 0.1.16
  * bugfix Csrf Classes
* 0.1.15
  * added Csrf Classes
  * adjusted the Session Class
  * adjusted the Controller Class
  * changed in composer.json Twig: from v2 to v3
* 0.1.14
  * bugfix Validate Class
  * new Method in the ApplicationLog Class
  * extension of a Method in the ResponseData Class
* 0.1.13
  * bugfix Session Class
* 0.1.12
  * registration Framework-Singletons
* 0.1.11
  * devMode implemented in ApplicationController
* 0.1.10
  * bugfix Schedule Runner
* 0.1.9
  * bugfix Request Class
* 0.1.8
  * Validate Class - AddRules accept array with rules
* 0.1.7
  * bugfix Router Class
  * bugfix CoreEngine Class
  * bugfix Curl Class
* 0.1.6
  * bugfix Validate Class
  * bugfix Router Class
  * bugfix ResponseJson Class
* 0.1.5
  * bugfix ShellExec Class
* 0.1.4
  * bugfix Toast Class
  * new Class ShellExec
* 0.1.3
  * refactor dependent Security extends Class from Abstract to Interface
* 0.1.2
  * refactor dependent Schedule extends Class from Abstract to Interface
* 0.1.1
  * bugfix Schedule Class
* 0.1.0
  * release
## What does the name Ayumila mean?
The name Ayumila is a tribute to our two cats Ayumi and Mila :) Ayumi is a British Shorthair and Mila is a Russian Blue cat.They were born in 2020.

## License
This project is licensed under the MIT License

## Author
Andreas Wunderwald
