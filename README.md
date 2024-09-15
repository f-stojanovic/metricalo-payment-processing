# Metricalo Payment Processing API

This project is a payment processing system built with Symfony, which supports multiple payment providers (Shift4 and ACI). The system allows you to process payments via an API or through console commands and includes unit and integration tests for the core functionality.

#### Requirements:
- Symfony 6.4
- PHP 8.2
- docker
- docker-compose

### Installation:
##### Docker setup
```
$ docker-compose build
$ docker-compose up -d
$ docker-compose exec php bash
```
Under the app directory of the docker container, run the command below:
```
$ composer install
```
##### Services

- http://localhost/ - Access the application

### API Endpoint

You can access it in Postman and input JSON body params:
```
$ /app/example/{aci|shift4}
```
Example for Shift4:
```
{
    "amount": 499,
    "currency": "USD",
    "card_number": "4242424242424242",
    "exp_month": "11",
    "exp_year": "2027",
    "cvv": "123",
    "cardholder_name": "John Doe"
}
```
Example for ACI"
```
{
    "amount": 499,
    "currency": "EUR",
    "card_number": "4200000000000000",
    "exp_month": "11",
    "exp_year": "2027",
    "cvv": "123",
    "cardholder_name": "John Doe"
}
```

### CLI Command

Run with following commands:
```
$ bin/console app:example {aci|shift4}
```
Try with following inputs:

For Shift4:
```
php bin/console app:example shift4 499 USD 4242424242424242 11 2027 123 "John Doe"
```
For ACI:

```
 php bin/console app:example aci 499 EUR 4200000000000000 11 2027 123 "John Doe"
```

### Testing

Run the tests inside the php container:
```
$ php bin/phpunit
```