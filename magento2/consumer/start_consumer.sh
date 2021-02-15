#!/bin/bash
# https://magento2.dev.com/rabbitmq/#/queues
# https://github.com/gaiterjones/Magento2_MessageManager
#
module='/var/www/dev/magento2/app/code/Gaiterjones/MessageManager'
if [[ ! -d $module ]]; then
    echo "MessageManager module not installed!"
    exit 1
fi

# Get consumer json from Gaiterjones_MessageManager
#
if [ "$CONSUMER_WHITELIST" = true ]; then
    echo "Loading consumers with whitelist..."
    consumersjson=$(php /var/www/dev/magento2/bin/magento messagemanager:getconsumers --whitelist --json)
else
    echo "Loading consumers..."
    consumersjson=$(php /var/www/dev/magento2/bin/magento messagemanager:getconsumers --json)
fi

consumers=$(jq -r .[] <<< "$consumersjson")
echo $consumers

if [ ${#consumers[@]} -eq 0 ]; then
    echo "No consumers found."
    exit 1
fi

# Load consumer processes
#
for consumer in $consumers;
do
    php /var/www/dev/magento2/bin/magento queue:consumers:start $consumer &
    status=$?
    if [ $status -ne 0 ]; then
      echo "Failed to start $consumer: $status"
      exit $status
    else
      echo "Loading $consumer ..."
    fi
done

echo "All consumers loaded, monitoring consumer processes..."

# Monitor consumer processes
#
while sleep 60; do

	for consumer in $consumers;
	do
	  ps aux |grep $consumer |grep -q -v grep
	  PROCESS_STATUS=$?
	  if [ $PROCESS_STATUS -ne 0 ]; then
		echo "Consumer $consumer has stopped, restarting..."
		exit 1
	  fi
	done

done
