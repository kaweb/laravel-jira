# Automatically raise errors as tickets in Jira

This package will automatically create tickets in Jira or comment on existing tickets whenever an error has been caught within the Laravel application that it is installed in.

## Installation

To install this package via composer run:

`composer require "kaweb/laravel-jira:^1.0.0"`

The package will automatically register itself and will begin capturing errors automatically, although you will need to configure this for your Jira instance and project.

Publish the configuration file with:

`php artisan vendor:publish --provider="Kaweb\Jira\JiraServiceProvider" --tag="config"`

By default this library will raise 'Alert' tickets at a priority level of 'Critical' - you may need to check the setup in Jira to ensure that tickets will be created at the correct level for your installation.