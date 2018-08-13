#!/bin/bash

# print outputs and exit on first failure
set -xe

cd /var/www/html/affiliates_connect_demo
echo "Branch change to dev"
sudo git checkout dev
echo "Pulling changes from Git"
sudo git pull origin dev
echo "Pulling chnages from submodules"
sudo git submodule foreach git pull origin 8.x-1.x
echo "Removing drupal/affiliates_connect module and its plugins before running composer"
sudo rm -rf /var/www/html/affiliates_connect_demo/code/drupal/web/modules/contrib/affiliates_connect
sudo rm -rf /var/www/html/affiliates_connect_demo/code/drupal/web/modules/contrib/affiliates_connect_flipkart
sudo rm -rf /var/www/html/affiliates_connect_demo/code/drupal/web/modules/contrib/affiliates_connect_ebay
echo "Running Composer Install"
docker exec affiliates_connect_demo composer install --no-interaction
echo "Running update entities"
docker exec affiliates_connect_demo vendor/bin/drupal upe
echo "Running Cache rebuild"
docker exec affiliates_connect_demo vendor/bin/drupal cr
