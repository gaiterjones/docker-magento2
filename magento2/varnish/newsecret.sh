#!/bin/bash
dd if=/dev/random of=./varnish.secret count=1
echo New Varnish secret created!
