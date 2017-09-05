#!/bin/sh

cd `dirname $0`

php -S localhost:8000 -t .. -d browscap=../data/browser/full_php_browscap.ini -d error_log=../log/php_error.log -d log_errors=On -d display_errors=Off -c ../data/configs/server.ini > ../log/server.log 2>&1
