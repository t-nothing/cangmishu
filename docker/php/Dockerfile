FROM php:7.1.16-fpm



#RUN printf "deb http://archive.debian.org/debian/ jessie main\ndeb-src http://archive.debian.org/debian/ jessie main\ndeb http://security.debian.org jessie/updates main\ndeb-src http://security.debian.org jessie/updates main" > /etc/apt/sources.list
RUN mv /etc/apt/sources.list /etc/apt/sources.list.bak && \
    echo "deb http://mirrors.163.com/debian/ jessie main non-free contrib" >/etc/apt/sources.list && \
    echo "deb http://mirrors.163.com/debian/ jessie-proposed-updates main non-free contrib" >>/etc/apt/sources.list && \
    echo "deb-src http://mirrors.163.com/debian/ jessie main non-free contrib" >>/etc/apt/sources.list && \
    echo "deb-src http://mirrors.163.com/debian/ jessie-proposed-updates main non-free contrib" >>/etc/apt/sources.list


ENV TZ=Asia/Shanghai

RUN apt-get update  && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
		#libpng12-dev \
		libpng-dev \
		zlib1g-dev \
		libxext-dev \
		libfontconfig1-dev \
		libxrender-dev \
		vixie-cron \
		crontabs \ 
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysqli pdo_mysql pcntl\
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
