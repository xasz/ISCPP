# ISCPP - Improved Sophos Central Partner Center

This projects targets to leverage the Sophos Central API to build a tool for some missing features i have noticed a MSP could need.
Furthermore i want to integration with PSA Systems for Billing.


This is personal side project.
I am not associated with Sophos, Halo oder NinjaRMM.

## Plans

I have a lot of ideas and a small amount of time.
The current state of the project is an alpha and use at your own risk.
At that point in time i cannot give any paid support or promised.
I give my best to look at you problems.

### Current Target

- Alert webhooks for PSA Systems
- Fast an quick overview of Tenant Health Scores
- Fast an quick search for Endpoint and current status
- Halo PSA > Billing Integration - Push Billing Data to Subscription
- Halo PSA > Client Matching for Billing
- Good Documentation for first setup

### Future Targes

- Polish UI
- NinjaRMM > Endpoint Matching
- NinjaRMM > Sophos Endpoint Installation
- Halo PSA > Endpoint Matching
- Sophos > Firewall Mass Update Scheduling
- Multiuser > Currently there is only one user, this need to change soon with some permissions
- MFA


# Installation

This is a PHP Laravel Application with Postgres as backend and based on the Laravel 12 Livewire starterkit. 
I target to be fully compatible with laravel cloud, for as easy as possible setup.

The following jobs need to run in the background:

```
php artisan schedule:run
php artisan queue:work

```

I am planning on creating a detailed doc on how to setup the application with laravel cloud as soon as the Multiuser is implemneted.

As long you can setup a local environment pretty use.
This is not the perfect way to do it. Just a quick one. If you have anything which can host laravel application go with that :)

1. Setup an Postgres, PHP, Compser, NPM
2. Clone the Github Reposoitory
3. Clone the .env.example to .env and fill it
4. run `php artisan migrate`
5. run `composer install`
6. run `php artisan key:generate`
7. run `php artisan optimize`

## Quick Local Testing

Run those commands in 3 terminals:


```
composer run dev
```

```
composer schedule:work
```

```
composer queue:listen
```

## Run as Server

Setup a cron or planned task to run the needed jobs in the background every minute

```
php artisan schedule:run
```

Setup a Task wich autostarts if fails with this

```
php artisan queue:work
```

Setup a Task wich autostarts if fails with this

```
php artisan serve
```

# First Setup

On the initial setup, when no user exist the login route gets forwarded to the register route.
This route is disabled after the first user is added.

Setup your Sophos Central Cerdentials in The `General Settings`

Look around :)

> Make sure your `Jobs in Queue` counter on the dashboard ticks down. This is done with the background jobs.
> Everything is syncronized in the background, so you do not see "live" data. Depending on your environment, it can take amoment to catch up.
