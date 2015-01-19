/usr/local/zend/bin/php composer.phar self-update
/usr/local/zend/bin/php composer.phar update
cd vendor
find . -name ".git" | xargs rm -rf
find . -name ".gitattributes" | xargs rm -rf
find . -name ".gitignore" | xargs rm -rf
cd ..
git add vendor

