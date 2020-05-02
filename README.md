# Docker Magento 2.3.X Open Source (CE) 05-2020

Docker containers for Magento 2.3.x development including :

  - PHP 7.3
  - Apache 2.4
  - MYSQL 5.7
  - Varnish 5.2 FPC  
  - RabbitMQ  
  - PhpMyAdmin
  - memcached
  - ELASTIC search 6.x
  - REDIS Session, System, FPC
  - Scaleable php-apache service

for PHP 7.1.32 use gaiterjones/magento2:2.3.0_PHP7.1  
for PHP 7.3 use gaiterjones/magento2:2.3.5  

git clone https://github.com/gaiterjones/docker-magento2-2  
EDIT .ENV  
docker-compose build  
docker exec -i -t --user magento magento2_php-apache_1 install-sampledata  
docker exec -i -t --user magento magento2_php-apache_1 install-magento  

TEST at  
http://magento2.dev.com   

http://blog.gaiterjones.com/docker-magento-2-development-deployment-php7-apache2-4-redis-varnish-scaleable/ for further deployment instructions.

![MAGENTO2 INSTALL](http://blog.gaiterjones.com/dropbox/docker-install-magento2.gif)
