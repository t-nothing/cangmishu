FROM php:7.4-cli

#RUN printf "deb http://archive.debian.org/debian/ jessie main\ndeb-src http://archive.debian.org/debian/ jessie main\ndeb http://security.debian.org jessie/updates main\ndeb-src http://security.debian.org jessie/updates main" > /etc/apt/sources.list

COPY ./sources.list /etc/apt/sources.list

ENV TZ=Asia/Shanghai

RUN apt-get update  && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
		libpng-dev \
		zlib1g-dev \
		libxext-dev \
		libfontconfig1-dev \
		libxrender-dev \
        libzip-dev \
        sudo \
        git \
        zip \
	    net-tools \
        procps \
	    curl \
	    wget \
        cron \ 
    && docker-php-ext-install -j$(nproc) iconv mysqli pdo_mysql pcntl zip pcntl bcmath \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN pecl install -o -f mcrypt && \
docker-php-ext-enable mcrypt

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

COPY ./php.ini /usr/local/etc/php/conf.d/php.ini

#RUN apt install -y libxext-dev libfontconfig1-dev libxrender-dev

COPY ./wkhtmltopdf /usr/local/bin

COPY ./simsun.ttc /usr/share/fonts/

RUN chmod +x /usr/local/bin/wkhtmltopdf

RUN apt-get install cron -y
#RUN apt-get install rsyslog -y

RUN docker-php-ext-install bcmath

COPY ./crontab /tmp

RUN cat /tmp/crontab >> /etc/crontab

CMD ["sh", "-c", "service cron restart"]

WORKDIR /var/www/html/

COPY ./start-queue.sh /usr/local/bin

RUN chmod +x /usr/local/bin/start-queue.sh

#CMD ["sh", "-c","sh start-queue.sh"]
CMD ["sh", "-c", "/usr/local/bin/start-queue.sh 2>&1 > /dev/stdout"]
