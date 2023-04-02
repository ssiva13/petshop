#!/bin/bash
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

#Build docker image
docker-compose up --build -d \
    && echo -e "\n${PURPLE} Please wait while service is up ... \n ${NC}" \
    && sleep 5 && docker exec petshop /var/www/docker/setup.sh \
    && echo "All done"
