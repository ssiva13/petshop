#!/bin/bash
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Change access rights for the Laravel folders
# in order to make Laravel able to access
# cache and logs folder.
chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache



# Create log file for Laravel and give it write access
# www-data is a standard apache user that must have an
# access to the folder structure
touch storage/logs/laravel.log \
    && chmod 775 storage/logs/laravel.log \
    && chown www-data storage/logs/laravel.log


# Install dependencies
echo -e "\n${BLUE} Please wait while installing Composer packages ... \n ${NC}" \
    && composer install --no-interaction --prefer-dist \
    && echo -e "\n${GREEN} Composer Packages Installed ... \n ${NC}"

#echo -e "\n${BLUE} Please wait while installing Node PacÏkages ... \n ${NC}" \
#    && npm install \
#    && echo -e "\n${GREEN} Node Packages Installed ... \n ${NC}"
#

# #Run `npm rebuild node-sass` to download the binding for your current environment
#echo -e "\n${BLUE} Please wait while building Node Sass Packages ... \n ${NC}" \
#    && npm rebuild node-sass \
#    && echo -e "\n${GREEN} Node Packages built ... \n ${NC}"
#
#echo -e "\n${BLUE} Please wait while building Node Packages ... \n ${NC}" \
#    && npm run dev \
#    && echo -e "\n${GREEN} Node Packages built ... \n ${NC}"


php artisan key:generate --show
