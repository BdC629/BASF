############################################################
# Dockerfile to build Nginx installed drupal
############################################################

#### Set the base image to PHP-FPM
FROM php:7.4-fpm

### File Author / Maintainer
MAINTAINER Vollcom Digital GmbH
### /

### Set arguments
ARG BRANCH
ARG WITH_XDEBUG=false
ARG WITH_COMPOSER=true
ARG MEMCACHED_VERSION=3.1.5
ARG DEVPORTAL_ENV
### /

### Copy nginx config and entrypoint
COPY /inc/nginx/nginx.conf /bin/
COPY init_container.sh /bin/
### /

###  Configure root user credentials
RUN chmod 755 /bin/init_container.sh \
    && echo "root:Docker!" | chpasswd \
    && echo "cd /home" >> /etc/bash.bashrc
### /

#### Install the PHP extensions we need
#### From https://medium.com/@didokun/run-drupal-8-with-composer-docker-compose-nginx-mariadb-php7-3-ca7d0cc71a99
#### With a few edits
RUN apt-get update; \
	apt-get install -y --no-install-recommends \
		libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libxml2-dev \
        git zip unzip mariadb-client\
        curl \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install -j$(nproc) gd bcmath opcache xmlrpc pdo_mysql \
        && pecl install apcu-5.1.18 \
        && docker-php-ext-enable apcu \

#### Additional libraries not in base recommendations
RUN apt-get update; \
	    apt-get install -y --no-install-recommends \
        openssh-server \
        git \
        nano \
        sudo \
        tcptraceroute \
        vim \
        wget \
        nginx \
        rsyslog \
        python \
        gpg \
        gdb \
        apt-utils \
        libssl-dev;
### /

### Set recommended PHP.ini settings
### See https://secure.php.net/manual/en/opcache.installation.php
RUN { \
		echo 'opcache.memory_consumption=512'; \
		echo 'opcache.interned_strings_buffer=64'; \
		echo 'opcache.max_accelerated_files=50000'; \
        echo 'opcache.max_wasted_percentage=15'; \
        echo 'opcache.interned_strings_buffer=64'; \
        echo 'opcache.validate_timestamps=0'; \
		echo 'opcache.revalidate_freq=0'; \
		echo 'opcache.fast_shutdown=1'; \
       } > /usr/local/etc/php/conf.d/opcache-recommended.ini
### /

### Set customized drupal rsyslog settings
COPY /inc/rsyslog/50-default.conf /etc/rsyslog.d/
###

#### Install xDebugger and enable it
RUN if [ $WITH_XDEBUG = "true" ] ; then \
	    pecl install xdebug-2.9.5; \
	    docker-php-ext-enable xdebug; \
	fi ;
### /

### Change nginx logs directory
RUN   \
   rm -f /var/log/nginx/* \
   && rmdir /var/log/nginx \
   && chmod 777 /var/log \
   && chmod 777 /bin/init_container.sh \
   && cp /bin/nginx.conf /etc/nginx/nginx.conf \
   && rm -rf /var/www/html \
   && rm -rf /var/log/nginx \
   && mkdir -p /home/LogFiles \
   && ln -s /home/LogFiles  /var/log/nginx
### /

### Change maximum server requests
COPY /inc/php/www.conf /usr/local/etc/php-fpm.d/
### /

### Append "daemon off;" to the beginning of the configuration
RUN echo "daemon off;" >> /etc/nginx/nginx.conf

### Include PHP recommendations from https://www.drupal.org/docs/7/system-requirements/php
RUN { \
    echo 'log_errors=On'; \
    echo 'display_startup_errors=Off'; \
    echo 'date.timezone=Europe/Berlin'; \
    echo 'session.cache_limiter = nocache'; \
    echo 'session.auto_start = 0'; \
    echo 'expose_php = off'; \
    echo 'allow_url_fopen = on'; \
    echo 'display_errors=Off'; \
    echo 'upload_max_filesize = 8M'; \
    echo 'post_max_size = 8M'; \
    echo 'realpath_cache_size = 256k'; \
    echo 'realpath_cache_ttl = 3600'; \
    echo 'memory_limit = 256M'; \
    } > /usr/local/etc/php/conf.d/php.ini
### /

### Installs memcached support for php with optimized config
RUN apt-get update && apt-get install -y libmemcached-dev zlib1g-dev \
    && pecl install memcached-${MEMCACHED_VERSION}\
    && docker-php-ext-enable memcached
RUN apt-get update && apt-get install -y memcached
### /

### Setup environment variables
COPY sshd_config /etc/ssh/
EXPOSE 2222 8080

ENV NGINX_RUN_USER www-data
ENV PHP_VERSION 7.3
ENV PORT 8080
ENV WEBSITE_ROLE_INSTANCE_ID localRoleInstance
ENV WEBSITE_INSTANCE_ID localInstance
ENV PATH ${PATH}:/var/www/html
ENV DEVPORTAL_ENV ${DEVPORTAL_ENV}
ENV LOG_WORKSPACE_ID ${LOG_WORKSPACE_ID}
ENV LOG_SHARED_KEY ${LOG_SHARED_KEY}
### /

### Begin Drush install
RUN wget https://github.com/drush-ops/drush/releases/download/8.4.10/drush.phar
RUN chmod +x drush.phar
RUN mv drush.phar /usr/local/bin/drush
RUN drush init -y
RUN drush cache-clear drush
### /

### Install Azure Log Analytics Agent & Troubleshooter for Linux
RUN mkdir -p /opt/microsoft/omsagent/bin
RUN wget https://raw.github.com/microsoft/OMS-Agent-for-Linux/master/source/code/troubleshooter/omsagent_tst.tar.gz
RUN chmod +x omsagent_tst.tar.gz
RUN tar -xzvf omsagent_tst.tar.gz
RUN ./install_tst
RUN mkdir -p /etc/cron.d
RUN wget https://raw.githubusercontent.com/Microsoft/OMS-Agent-for-Linux/master/installer/scripts/onboard_agent.sh
RUN echo "#!/bin/sh\nexit 0" > /usr/sbin/policy-rc.d
RUN chmod +x onboard_agent.sh
RUN mv onboard_agent.sh /opt/microsoft/omsagent/bin/onboard_agent.sh
###

### Change workdir
WORKDIR /var/www/html/
### /

### Install composer
RUN echo ipv4 >> ~/.curlrc
RUN curl -sS https://getcomposer.org/installer | php -d allow_url_fopen=on -- --install-dir=/usr/local/bin --filename=composer --version=1.10.17
### /

### Install dependencies
RUN mkdir -p /var/www/html/docroot
COPY /src/composer.json ./
RUN if [ $WITH_COMPOSER = "true" ] ; then \
      php -d allow_url_fopen=on -d memory_limit=-1 /usr/local/bin/composer update --no-dev --no-interaction; \
    fi ;
### /

### Copy custom modules and custom from local drupal code base to final destination
COPY /src/docroot/modules/custom docroot/modules/custom
COPY /src/docroot/themes/custom docroot/themes/custom
RUN true

##COPY /src/docroot/profiles docroot/profiles
COPY /src/docroot/sites/default docroot/sites/default
RUN true
COPY /src/docroot/sites/developer.basf.com docroot/sites/developer.basf.com
RUN true
COPY /src/docroot/sites/ap.developer.basf.com docroot/sites/ap.developer.basf.com
### /

### Finish composer
RUN if [ $WITH_COMPOSER = "true" ] ; then \
      composer dump-autoload --no-scripts --no-dev --optimize; \
    fi ;
### /

### Beginn Import default.settings.php with Azure DB Variables
COPY /src/docroot/sites/developer.basf.com/settings.php docroot/sites/developer.basf.com
RUN true
COPY /src/docroot/sites/ap.developer.basf.com/settings.php docroot/sites/ap.developer.basf.com
RUN true
COPY /src/docroot/sites/sites.php docroot/sites
### /

### Add directories for public and private files for default
RUN mkdir -p  /home/site/wwwroot/sites/default/files \
    && mkdir -p  /home/site/wwwroot/sites/default/files/private \
    && mkdir -p  /home/site/wwwroot/sites/default/files/tmp \
    && ln -s /home/site/wwwroot/sites/default/files  /var/www/html/docroot/sites/default/files \
    && ln -s /home/site/wwwroot/sites/default/files/private  /var/www/html/docroot/sites/default/files/private \
    && ln -s /home/site/wwwroot/sites/default/files/tmp  /var/www/html/docroot/sites/default/files/tmp
### /

### Add directories for public and private files for developer.basf.com
RUN mkdir -p  /home/site/wwwroot/sites/developer.basf.com/files \
    && mkdir -p  /home/site/wwwroot/sites/developer.basf.com/files/private \
    && mkdir -p  /home/site/wwwroot/sites/developer.basf.com/files/tmp \
    && ln -s /home/site/wwwroot/sites/developer.basf.com/files  /var/www/html/docroot/sites/developer.basf.com/files \
    && ln -s /home/site/wwwroot/sites/developer.basf.com/files/private  /var/www/html/docroot/sites/developer.basf.com/files/private \
    && ln -s /home/site/wwwroot/sites/developer.basf.com/files/tmp  /var/www/html/docroot/sites/developer.basf.com/files/tmp
### /

### Add directories for public and private files for ap.developer.basf.com
RUN mkdir -p  /home/site/wwwroot/sites/ap.developer.basf.com/files \
    && mkdir -p  /home/site/wwwroot/sites/ap.developer.basf.com/files/private \
    && mkdir -p  /home/site/wwwroot/sites/ap.developer.basf.com/files/tmp \
    && ln -s /home/site/wwwroot/sites/ap.developer.basf.com/files  /var/www/html/docroot/sites/ap.developer.basf.com/files \
    && ln -s /home/site/wwwroot/sites/ap.developer.basf.com/files/private  /var/www/html/docroot/sites/ap.developer.basf.com/files/private \
    && ln -s /home/site/wwwroot/sites/ap.developer.basf.com/files/tmp  /var/www/html/docroot/sites/ap.developer.basf.com/files/tmp
### /

### Begin copy basf certificate
COPY /src/docroot/sites/developer.basf.com/files/private/private.pem /home/site/wwwroot/sites/developer.basf.com/files/private
COPY /src/docroot/sites/developer.basf.com/files/private/public.pem /home/site/wwwroot/sites/developer.basf.com/files/private
RUN true
COPY /src/docroot/sites/ap.developer.basf.com/files/private/private.pem /home/site/wwwroot/sites/ap.developer.basf.com/files/private
COPY /src/docroot/sites/ap.developer.basf.com/files/private/public.pem /home/site/wwwroot/sites/ap.developer.basf.com/files/private
RUN true
### /

### Begin of set permissions to drupal file system
RUN chgrp www-data /var/www/html/docroot/sites/default/files/ -R
RUN chgrp www-data /var/www/html/docroot/sites/developer.basf.com/files/ -R
RUN true
RUN chgrp www-data /var/www/html/docroot/sites/ap.developer.basf.com/files/ -R
RUN chmod g+w /var/www/html/docroot/sites/default/files/ -R
RUN true
RUN chmod g+w /var/www/html/docroot/sites/developer.basf.com/files/ -R
RUN chmod g+w /var/www/html/docroot/sites/ap.developer.basf.com/files/ -R
### /

### Webroot permissions per www.drupal.org/node/244924#linux-servers ###
# For sites/default/files directory, permissions come from
# /home/site/wwwroot/sites/default/files
WORKDIR /var/www/html/docroot
RUN chown -R www-data:www-data .

### Begin of set permissions to drupal file system
COPY /inc/drupal/set-permissions.sh /set-permissions.sh
RUN chmod +x /set-permissions.sh

ENTRYPOINT ["/bin/init_container.sh"]