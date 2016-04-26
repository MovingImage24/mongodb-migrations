# MongoDB Migrations

[![Build Status](https://travis-ci.org/MovingImage24/mongodb-migrations.svg?branch=master)](https://travis-ci.org/MovingImage24/mongodb-migrations)
[![Latest Stable Version](https://poser.pugx.org/mi/mongodb-migrations/v/stable)](https://packagist.org/packages/mi/mongodb-migrations)
[![Latest Unstable Version](https://poser.pugx.org/mi/mongodb-migrations/v/unstable)](https://packagist.org/packages/mi/mongodb-migrations)
[![Total Downloads](https://poser.pugx.org/mi/mongodb-migrations/downloads)](https://packagist.org/packages/mi/mongodb-migrations)
[![License](https://poser.pugx.org/mi/mongodb-migrations/license)](https://packagist.org/packages/mi/mongodb-migrations)

## Overview

...

## Installation

### Composer

#### for PHP 7 and greater with the new mongoDB-extension

    composer require mi/mongodb-migrations

#### for other PHP-Versions 

    composer require mi/mongodb-migrations ^1.0.0@beta

### Puli-Bindings

MY_BUNDLE_ALIAS = the alias of your bundle (for example `my_awesome_bundle`)

    puli bind --class Mi\\MongoDb\\Migration\\DependencyInjection\\MigrationPlugin Matthias\\BundlePlugins\\BundlePlugin --param bundle-alias=<MY_BUNDLE_ALIAS>
    puli bind /mi/mongodb-migrations/*.xml mi/service
    puli build

### Configuration

    my_awesome_bundle:
        ...
        
        migration:
            path: '/path/to/version/class/directory'                            // path where versions will be constructed
            xml_path: '/path/to/version/service-definition/file'                // service-definitions where versions will be defined
            namespace: "namespace\\of\\generated\\versions"                     // namespace versions will be constructed with
            migration_collection: 'migration.collection.service.definition.id'  // need to be from type MongoCollection


### refresh autoloader

add namespace `namespace\\of\\generated\\versions` to autoloader and execute
    
    composer dump-autoload

## Usage

### create version

    console mi:mongo-db:migration:generate

### execute migration

    console mi:mongo-db:migration:migrate

## Contributing

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request

# License

This library is under the [MIT license](https://github.com/MovingImage24/mongodb-migrations/blob/master/LICENSE).
