#!/bin/bash
# Restore script for APS Dream Homes
if [ -z "$1" ]; then
  echo "Usage: $0 <backup_folder>"
  exit 1
fi
cp -r "$1/code" /var/www/apsdreamhomefinal
mysql -u root -p apsdreamhomefinal < "$1/db.sql"
echo "Restore completed from $1"
