# Chromis Medical Appointments Booking System

Chromis-MA is a booking platform for a employees.

## Requirements

- PHP 8.1 and above
- MariaDB
- [PHP Composer](https://getcomposer.org/)

## Configuration

1. We have to change the values on config.php file & phinx.php
2. Run `composer install`, to install all requirements. (Everytime there is an update in composer.json)
3. Run `vendor/bin/phinx migrate -e development`. (Everytime there is an update in the database schema)
4. Run `vendor/bin/phinx seed:run`. (Only once at the beginning)

## History

- Version: 0.1
  Basic UI and  workflow with bootstrap UI and
- Version: 0.2
  Complete Workflow with Dummy Data
- version: 0.3
  Split Codebase for admin
- version: 0.4
  Added [Phinx ](https://phinx.org)Database Migration
