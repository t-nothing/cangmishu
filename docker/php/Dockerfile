FROM php:7.4-fpm

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

RUN docker-php-ext-install bcmath

COPY ./wkhtmltopdf /usr/local/bin

COPY ./simsun.ttc /usr/share/fonts/

RUN chmod +x /usr/local/bin/wkhtmltopdf

# 安装composer并允许root用户运行
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1
ENV COMPOSER_HOME=/usr/local/share/composer
RUN mkdir -p /usr/local/share/composer \
    && curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
    && rm -f /tmp/composer-setup.* \
    # 配置composer阿里云全量镜像
    && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
