# Ninebot-ESP32-Scooter-Locator
Locates ninebot scoooters or any device advertising a nordic UART service over BLE.

## Setup

Clone the repo somewhere, preferably in the Arduino sketch directory so that your sketch will come up in the sketchbook in Arduino. 

Requires joeybab3/database which can be installed using composer:

`composer install joeybab3/database`

The repo has it intalled already so you can just run `composer update` to insure it is up to date.

Change credentials in `credentials.php` to match your database login/preferences.

Once you have sets up the credentials, run InstallSchema/index.php.

## Building/location footprints

Pull data from OSM using [Overpass Turbo](https://overpass-turbo.eu/), with `way["addr:housenumber"="address"]({{bbox}});` by changing "address" to the address of your building and panning to the location the building exists with the slippy map. If your building does not exist in openstreetmap, add it, then export as geojson.

In the Geojson, add a key=>value pair of 'station': 'yourstationid' to the building associated with the address under properties.

## Server

As of right now this is a very rough preview, I have it working for myself but I am working on making it easier to use for the general public.

The server consists of 3 scripts:
* Index.php: the frontend/user interface
* Getscooters.php: The ajax handler for index.php
* Putscooters.php: The handler for the arduino to put data about nearby scooters.
