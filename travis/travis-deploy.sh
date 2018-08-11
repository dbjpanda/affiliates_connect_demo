#!/bin/bash

# print outputs and exit on first failure
set -xe

if [ $TRAVIS_BRANCH == "master" ] ; then
    ssh -i ./deploy_key drupal@35.227.98.126 /var/www/html/affiliates_connect/travis/deploy.sh
elif [ $TRAVIS_BRANCH == "dev" ] ; then
    ssh -i ./deploy_key drupal@35.227.98.126 /var/www/html/affiliates_connect_demo/travis/deploy-dev.sh
else
    echo "No deploy script for branch '$TRAVIS_BRANCH'"
fi
