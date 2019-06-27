FROM php:7.1.16-cli

ENV TZ=Asia/Shanghai

RUN printf "deb http://archive.debian.org/debian/ jessie main\ndeb-src http://archive.debian.org/debian/ jessie main\ndeb http://security.debian.org jessie/updates main\ndeb-src http://security.debian.org jessie/updates main" > /etc/apt/sources.list
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
		#libpng12-dev \
		libpng-dev \
        zlib1g-dev \
		libxext-dev \
		libfontconfig1-dev  \
		libxrender-dev \
		cron \ 
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysqli pdo_mysql pcntl \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install zip

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

COPY ./php.ini /usr/local/etc/php/conf.d/php.ini

#RUN apt install -y libxext-dev libfontconfig1-dev libxrender-dev

COPY ./wkhtmltopdf /usr/local/bin

COPY ./simsun.ttc /usr/share/fonts/

RUN chmod +x /usr/local/bin/wkhtmltopdf

WORKDIR /var/www/html/

COPY ./start-queue.sh /usr/local/bin

RUN chmod +x /usr/local/bin/start-queue.sh

#CMD ["sh", "-c","sh start-queue.sh"]
CMD ["sh", "-c", "/usr/local/bin/start-queue.sh 2>&1 > /dev/stdout"]
