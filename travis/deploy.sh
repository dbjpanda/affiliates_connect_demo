#!/bin/bash

# print outputs and exit on first failure
set -xe

cd /var/www/html/affiliates_connect
echo "Branch change to master
sudo git checkout master
echo "Pulling changes from Git"
sudo git pull origin master
echo "Running Composer Install"
docker exec affiliates_connect composer install --no-interaction
echo "Running update entities"
docker exec affiliates_connect vendor/bin/drupal upe
echo "Running Cache rebuild"
docker exec affiliates_connect vendor/bin/drupal cr
