#!/bin/bash
cat >/etc/motd <<EOL
  _____
  /  _  \ __________ _________   ____
 /  /_\  \\___   /  |  \_  __ \_/ __ \
/    |    \/    /|  |  /|  | \/\  ___/
\____|__  /_____ \____/ |__|    \___  >
        \/      \/                  \/
A P P   S E R V I C E   O N   L I N U X

Documentation: http://aka.ms/webapp-linux
PHP quickstart: https://aka.ms/php-qs

EOL
cat /etc/motd

echo "+++ Starting SSH +++"
service ssh start
echo "+++ SSH started +++"

echo "+++ Set env variables for developer.basf.com +++"
sed -i "s/{DEVPORTAL_ENV}/$DEVPORTAL_ENV/g" /var/www/html/docroot/sites/developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_DB}/$DEVPORTAL_DB/g" /var/www/html/docroot/sites/developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_DBUSER}/$DEVPORTAL_DBUSER/g" /var/www/html/docroot/sites/developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_DBPASSWORD}/$DEVPORTAL_DBPASSWORD/g" /var/www/html/docroot/sites/developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_HOST}/$DEVPORTAL_HOST/g" /var/www/html/docroot/sites/developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_PORT}/$DEVPORTAL_PORT/g" /var/www/html/docroot/sites/developer.basf.com/settings.php
echo "+++ Env variables for developer.basf.com set +++"

echo "+++ Set env variables for ap.developer.basf.com +++"
sed -i "s/{DEVPORTAL_ENV}/$DEVPORTAL_ENV/g" /var/www/html/docroot/sites/ap.developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_DB_AP}/$DEVPORTAL_DB_AP/g" /var/www/html/docroot/sites/ap.developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_DBUSER_AP}/$DEVPORTAL_DBUSER_AP/g" /var/www/html/docroot/sites/ap.developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_DBPASSWORD_AP}/$DEVPORTAL_DBPASSWORD_AP/g" /var/www/html/docroot/sites/ap.developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_HOST_AP}/$DEVPORTAL_HOST_AP/g" /var/www/html/docroot/sites/ap.developer.basf.com/settings.php
sed -i "s/{DEVPORTAL_PORT_AP}/$DEVPORTAL_PORT_AP/g" /var/www/html/docroot/sites/ap.developer.basf.com/settings.php
echo "+++ Env variables for ap.developer.basf.com set +++"

echo "+++ Starting Memcached and give write permissions +++"
service memcached start
echo "+++ Memcached started +++"

echo "+++ Starting enhanced syslog +++"
service rsyslog start
echo "+++ Enhanced syslog started +++"

if [ ! "$DEVPORTAL_ENV" == "localhost" ]
then
  echo "+++ Starting Azure Log Analytics Agent +++"
  sh /opt/microsoft/omsagent/bin/onboard_agent.sh -w "$LOG_WORKSPACE_ID" -s "$LOG_SHARED_KEY" -d opinsights.azure.com
  echo "+++ Azure Log Analytics Agent started+++"
fi

echo "+++ Starting PHP-FPM & Nginx +++"
php-fpm -D; nginx