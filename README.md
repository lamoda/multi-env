# Lamoda multi-env

[![Build Status](https://travis-ci.org/lamoda/multi-env.svg?branch=master)](https://travis-ci.org/lamoda/multi-env)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lamoda/multi-env/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lamoda/multi-env/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/lamoda/multi-env/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lamoda/multi-env/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/lamoda/multi-env/badges/build.png?b=master)](https://scrutinizer-ci.com/g/lamoda/multi-env/build-status/master)

Library that provides classes to work with envs in multitenant environment

Library based on params passed to it on initialization stage will decide which env variable should be used 
for current request. 

## Installation

1. Install library with composer:
```bash
composer require lamoda/multi-env
```

## Usage

### Library usage to work in not multitenant environment (could be useful in development mode)

```php
<?php

use \Lamoda\MultiEnv\Strategy\RawEnvResolvingStrategy;
use \Lamoda\MultiEnv\Decorator\EnvProviderDecorator;

// RawEnvResolvingStrategy - just wrap native PHP get_env function call
$strategy = new RawEnvResolvingStrategy();
EnvProviderDecorator::init($strategy);

// Will be search original TEST_ENV env variable
EnvProviderDecorator::getEnv('TEST_ENV');
```

### Library usage to work in multitenant environment

```php
<?php

use \Lamoda\MultiEnv\Strategy\HostBasedEnvResolvingStrategy;
use \Lamoda\MultiEnv\Decorator\EnvProviderDecorator;

/*
 * Pass as first param one of available HostDetectorInterface implementations
 * Pass as second param one of available EnvNameFormatterInterface implementations
 */
$strategy = new HostBasedEnvResolvingStrategy($hostDetector, $envFormatter);
EnvProviderDecorator::init($strategy);

/*
 * Will search env with some specific prefix/postfix resolved by HostDetectorInterface
 * For example host_id__TEST_ENV
 */
EnvProviderDecorator::getEnv('TEST_ENV');
```

### Library set up with multiple strategy
```php
<?php

use \Lamoda\MultiEnv\Strategy\FirstSuccessfulEnvResolvingStrategy;
use \Lamoda\MultiEnv\Strategy\RawEnvResolvingStrategy;
use \Lamoda\MultiEnv\Strategy\HostBasedEnvResolvingStrategy;
use \Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector;
use \Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
use \Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
use \Lamoda\MultiEnv\Formatter\PrefixEnvNameFormatter;
use \Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
use \Lamoda\MultiEnv\Decorator\EnvProviderDecorator;

$rawEnvResolvingStrategy = new RawEnvResolvingStrategy();
$hostBasedEnvResolvingStrategy = new HostBasedEnvResolvingStrategy(
    new FirstSuccessfulHostDetector([
        new ServerHeadersBasedHostDetector('HTTP_X_TEST_HEADER'),
        new CliArgsBasedHostDetector('host_id', GetOptAdapterFactory::build())    
    ]),
    new PrefixEnvNameFormatter('___')
);

$firstSuccessfulStrategy = new FirstSuccessfulEnvResolvingStrategy([
    $rawEnvResolvingStrategy,
    $hostBasedEnvResolvingStrategy
]);
EnvProviderDecorator::init($firstSuccessfulStrategy);

/*
 * Try find original env 'TEST_ENV' first. 
 * If original env not found than try to find env with some specific prefix/postfix resolved by HostDetectorInterface.
 * For example host_id__TEST_ENV
 */
EnvProviderDecorator::getEnv('TEST_ENV');
```

## Available HostDetectorInterface implementations

1.  __\Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector__ - use to identificate host via HTTP request
    ```php
    <?php

    use \Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
    use \Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
    
    /*
     * Search passed needle in $_SERVER header. Use found value to identify current host
     * Throw HostDetectorException when passed empty needle
     */
    $headerBasedHostDetector = new ServerHeadersBasedHostDetector('HTTP_X_SOME_HEADER');
    $hostId = $headerBasedHostDetector->getCurrentHost();
    ``` 
2. __\Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector__ - use to identificate host via Cli script run 
    ```php
    <?php
 
    use \Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
    use \Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
    use \Lamoda\MultiEnv\HostDetector\Exception\HostDetectorException;
    
    /*
     * Search passed needle in CLI args. Use found value to identify current host
     * Throw HostDetectorException when passed empty needle
     */
    $cliArgsBasedHostDetector = new CliArgsBasedHostDetector('needle', GetOptAdapterFactory::build());
    $hostId = $cliArgsBasedHostDetector->getCurrentHost();
    ```

3. __\Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector__ - use to aggregate multiple HostDetector's 
    ```php
    <?php
 
    use \Lamoda\MultiEnv\HostDetector\ServerHeadersBasedHostDetector;
    use \Lamoda\MultiEnv\HostDetector\CliArgsBasedHostDetector;
    use \Lamoda\MultiEnv\HostDetector\Factory\GetOptAdapterFactory;
    use \Lamoda\MultiEnv\HostDetector\FirstSuccessfulHostDetector;
    
    // Iterate through passed HostDetector's and return first not empty HostId
    $firstSuccessfulHostDetector = new FirstSuccessfulHostDetector([
        new CliArgsBasedHostDetector('some_host_id', GetOptAdapterFactory::build()),
        new ServerHeadersBasedHostDetector('HTTP_HOST_ID')
    ]);
    $hostId = $firstSuccessfulHostDetector->getCurrentHost();
    ```
    
## Available EnvNameFormatterInterface implementations
1. __\Lamoda\MultiEnv\Formatter\PrefixEnvNameFormatter__ - format env name. Combine __original env name__, __delimiter__,
__host id__ in order \*__host id__\*, \*__delimiter__\*, \*__original env name__\*
    ```php
    <?php 
 
    use \Lamoda\MultiEnv\Formatter\PrefixEnvNameFormatter;
    use \Lamoda\MultiEnv\Model\HostId;
    use \Lamoda\MultiEnv\Formatter\Exception\FormatterException;
 
    $formatter = new PrefixEnvNameFormatter('__');
    // Throw FormatterException if passed empty originalEnvName
    $formatterName = $formatter->formatEnvName('originalEnvName', new HostId('test_host'));
    ```
