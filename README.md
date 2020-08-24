# Ninebot-ESP32-Scooter-Locator
Locates ninebot scoooters or any device advertising a nordic UART service over BLE.

Requires a
`composer install joeybab3/database`

Pull data from OSM using [Overpass Turbo](https://overpass-turbo.eu/), with `way["addr:housenumber"="address"]({{bbox}});` and export as geojson.

In the Geojson, add a key=>value pair of 'station': 'yourstationid' to the building associated with the address under properties.

As of right now this is a very rough preview, I have it working for myself but I am working on making it easier to use for the general public.
