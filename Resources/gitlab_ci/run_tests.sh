#!/bin/bash

COMMIT_MESSAGE=$1

echo "$COMMIT_MESSAGE"

if echo "$COMMIT_MESSAGE" | grep -q "\[coverage\]";
then
    echo "Testing WITH coverage"
    php vendor/bin/phpunit --coverage-text --colors=never --debug
else 
    echo "Testing WITHOUT coverage"
    php vendor/bin/phpunit --colors=never --debug
fi
