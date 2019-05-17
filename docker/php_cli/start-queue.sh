#!/usr/bin/env sh

php artisan queue:work --tries=3 --timeout=60 --queue=emails
php artisan queue:work --tries=3 --timeout=60 --queue=wms2