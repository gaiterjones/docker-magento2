#!/bin/bash
# https://magento2.dev.com/rabbitmq/#/queues
#
docker exec magento2_consumer1_1 kill -9 1
echo "Consumer container restarted..."
