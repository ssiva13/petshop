#!/bin/bash
#Destroy docker image
echo "Please wait while service is being destroyed..." \
    && docker-compose down -v \
    && echo "All done"
