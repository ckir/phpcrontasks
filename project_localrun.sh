export XDEBUG_CONFIG="idekey=netbeans-xdebug remote_host=localhost"
dev_appserver.py --enable_task_running=yes --log_level=debug --logs_path=/var/www/GAE/dev_appserver.log.sqlite --php_remote_debugging=yes --php_executable_path=/home/user/php-5.4.25/installdir/bin/php-cgi /var/www/GAE/phpcrontasks/

