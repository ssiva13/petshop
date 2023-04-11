#!/bin/bash
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

cp .env.example .env



# rm -rf libraries/currency-exchange
# git clone https://github.com/ssiva13/currency-exchange.git libraries/currency-exchange

# rm -rf libraries/laravel-notify
# git clone https://github.com/ssiva13/laravel-notify.git libraries/laravel-notify

# rm -rf libraries/laravel-stripe
# git clone https://github.com/ssiva13/laravel-stripe.git libraries/laravel-stripe


# composer require ssiva/laravel-notify:dev-main
# composer require ssiva/laravel-stripe:dev-main
# composer require ssiva/currency-exchange:dev-main




#Build docker image
docker-compose up --build -d \
    && echo -e "\n${PURPLE} Please wait while service is up ... \n ${NC}" \
    && sleep 5 && docker exec petshop /var/www/docker/setup.sh \
    && echo "All done"
