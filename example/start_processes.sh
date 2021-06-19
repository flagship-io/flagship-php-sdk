#!/bin/bash

# Start the first process
sh apache_process &
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start apache_process: $status"
  exit $status
fi

# Start the second process
sh polling_process -D
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start polling_process: $status"
  exit $status
fi

