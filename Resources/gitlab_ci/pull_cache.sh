#!/bin/bash

COMMIT_MESSAGE=$1
IMAGE_NAME=$2

echo "$COMMIT_MESSAGE"
echo "$IMAGE_NAME"

if echo "$COMMIT_MESSAGE" | grep -q "\[noCache\]";
#if [[ "$COMMIT_MESSAGE" == *\[noCache\]* ]]; 
then
    echo "Not pulling cache"
else 
    docker pull "$IMAGE_NAME"
fi

echo "Continue with building"
