#!/bin/sh
service php7.1-fpm stop;
service apache2 stop;
service sendmail stop;
service cron stop;

service php7.1-fpm start;
service apache2 start;
service sendmail start;
service cron start;
tail -f /dev/null;