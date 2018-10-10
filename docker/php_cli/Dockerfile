FROM php:7.1-cli

ENV TZ=Asia/Shanghai

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysqli pdo_mysql pcntl\
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

COPY ./php.ini /usr/local/etc/php/conf.d/php.ini

RUN apt install -y libxext-dev libfontconfig1-dev libxrender-dev

COPY ./wkhtmltopdf /usr/local/bin

COPY ./simsun.ttc /usr/share/fonts/

RUN chmod +x /usr/local/bin/wkhtmltopdf

WORKDIR /var/www/html/WMS-API-V2/src/

CMD ["sh", "-c","php artisan queue:work --tries=3 --timeout=60 --queue=emails"]
CMD ["sh", "-c","php artisan queue:work --tries=3 --timeout=60 --queue=wms2"]