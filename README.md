
# Docker Magento 2.4.X Open Source (CE) 03-2025

Docker containers for Magento 2.4.x development including :

  - Ubuntu 24.04
  - PHP 8.3
  - Apache 2.4
  - MYSQL 8
  - Varnish 7 FPC  
  - RabbitMQ 3.x
  - PhpMyAdmin
  - memcached
  - OPEN search 2.x
  - REDIS Session, System, FPC
  - Scaleable php-apache service

## Installation

1. git clone https://github.com/gaiterjones/docker-magento2  
2. EDIT .env - **add your Magento authentication keys**  
3. `docker-compose build`
4. `docker-compose up -d`   
5. Install sample data, note sample data will fail until php has initialised composer, allow 60 seconds after start - ignore any errors
`docker-compose exec -u magento php-apache install-sampledata`

6. Install Magento
`docker-compose exec -u magento php-apache install-magento`

7. Disable 2FA for testing
`docker exec -i -t --user magento magento2_php-apache_1 ./bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth`
`docker-compose exec -u magento magento2_php-apache_1 bin/magento module:disable Magento_TwoFactorAuth`

## Test

 - Admin
http://magento2.dev.com/admin  
 - Frontend
http://magento2.dev.com   
 - CLI


    `docker-compose exec -u magento php-apache bash`

to fix layout issues with demo data : `docker-compose exec -u magento php-apache cp /var/www/dev/magento2/vendor/magento/module-cms-sample-data/fixtures/styles.css /var/www/dev/magento2/pub/media/`

Enable VARNISH FPC in admin Stores-> Configuration -> Advanced -> Full Page Cache

hostname - vanish
port 80

### More

https://blog.gaiterjones.com/docker-magento-2-development-deployment-php7-apache2-4-redis-varnish-scaleable/ for further deployment instructions.

![MAGENTO2 INSTALL](https://blog.gaiterjones.com/dropbox/docker-install-magento240.gif)
