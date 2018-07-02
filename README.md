[![Build Status](https://travis-ci.com/dbjpanda/drupal-on-docker.svg?token=55CADUHzgmryMHLpbyAs&branch=master)](https://travis-ci.com/dbjpanda/drupal-on-docker)

Optional but recommended steps
----------------------
Install Traefik to access your Drupal site using their "domain name" instead of "IP:port" . This is an one time setup and use with all projects. This is usefull for both Drupal and Non-Drupal projects. As this project is configured to work with Traefik by default. If you don't want to install Traefik, then you need to manually provide a port number to nginx service and acess it uisng localhost:port.
```$xslt
docker network create -d bridge traefik-network
docker run -d --network=traefik-network -p 80:80 -p 8080:8080 -v /var/run/docker.sock:/var/run/docker.sock --name=traefik traefik:latest --api --docker
```

Installation of Drupal on development server
----------------------
Step 1
``````
git clone --recursive https://github.com/dbjpanda/drupal-on-docker.git
```````
Step 2
````````
Rename .env.example to .env and modify the file as per your requirements
``````````````
Step 3
````````
docker-compose up -d
````````
Step 4
````````
docker exec -it PROJECT_NAME composer install
````````

Step 5 (Import configuration)
````````
docker exec -it PROJECT_NAME vendor/bin/drupal ci
````````

Step 6 (Rebuild Cache)
````````
docker exec -it PROJECT_NAME vendor/bin/drupal cr
````````

Installation of Drupal on production server
---------------------------
Here you need to override the default environment variables for production server. You can achieve it by below command while deploying.

````````
SITE_NAME=example.com MYSQL_USER=someone MYSQL_PASS=yoursecrets docker-compose up -d
``````````````
