
# Docker Magento 2.4.X Open Source (CE) 02-2021

Docker containers for Magento 2.4.x development including :

  - PHP 7.4
  - Apache 2.4
  - MYSQL 8
  - Varnish 6 FPC  
  - RabbitMQ  
  - PhpMyAdmin
  - memcached
  - ELASTIC search 7.x
  - REDIS Session, System, FPC
  - Scaleable php-apache service

## Installation

1. git clone https://github.com/gaiterjones/docker-magento2  
2. EDIT .env - **add your Magento authentication keys**  
3. `docker-compose build`
4. `docker-compose up -d`   
5. Install sample data

docker exec -i -t --user magento magento2_php-apache_1 install-sampledata  

6. Install Magento

docker exec -i -t --user magento magento2_php-apache_1 install-magento

7. Disable 2FA for testing

docker exec -i -t --user magento magento2_php-apache_1 bin/magento module:disable Magento_TwoFactorAuth

## Test

 - Admin
http://magento2.dev.com/admin  
 - Frontend
http://magento2.dev.com   
 - CLI


    docker exec -i -t --user magento magento2_php-apache_1 /bin/bash

### More

https://blog.gaiterjones.com/docker-magento-2-development-deployment-php7-apache2-4-redis-varnish-scaleable/ for further deployment instructions.

![MAGENTO2 INSTALL](https://blog.gaiterjones.com/dropbox/docker-install-magento240.gif)
