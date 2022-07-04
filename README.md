
# Docker Magento 2.4.X Open Source (CE) 07-2022

Docker containers for Magento 2.4.x development including :

  - Ubuntu 22.04
  - PHP 8.1
  - Apache 2.4
  - MYSQL 8
  - Varnish 7 FPC  
  - RabbitMQ 3.x
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
`docker-compose exec -u magento php-apache install-sampledata`

6. Install Magento
`docker-compose exec -u magento php-apache install-magento`

7. Disable 2FA for testing
`docker-compose exec -u magento php-apache bin/magento module:disable Magento_TwoFactorAuth`

## Test

 - Admin
http://magento2.dev.com/admin  
 - Frontend
http://magento2.dev.com   
 - CLI


    `docker-compose exec -u magento php-apache bash`

to fix layout issues with demo data : `docker-compose exec -u magento php-apache cp /var/www/dev/magento2/vendor/magento/module-cms-sample-data/fixtures/styles.css /var/www/dev/magento2/pub/media/`
### More

https://blog.gaiterjones.com/docker-magento-2-development-deployment-php7-apache2-4-redis-varnish-scaleable/ for further deployment instructions.

![MAGENTO2 INSTALL](https://blog.gaiterjones.com/dropbox/docker-install-magento240.gif)
