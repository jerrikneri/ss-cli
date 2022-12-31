## Get Started
- `composer install`
- `php artisan generate:suitabilityScore`
  - use `--pathToAddressesFile=` and `--pathToDriverNamesFile=` to specify your own files to be used
  - ex. `php artisan generate:suitabilityScore --pathToAddressesFile='./tests/files/addresses.txt' --pathToDriverNamesFile='./tests/files/drivers.txt'` to use example files provided
  - running without any flags will generate test files with 100 addresses and 100 names
------

## Tests
- `./vendor/bin/pest` to run tests

