php composer.phar self-update
php composer.phar update --ignore-platform-reqs --prefer-source --optimize-autoloader
cd vendor
find . -name ".git" | xargs rm -rf
find . -name ".gitattributes" | xargs rm -rf
find . -name ".gitignore" | xargs rm -rf
cd ..
git add vendor

