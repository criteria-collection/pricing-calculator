#!/bin/bash

curl --request POST http://45.76.127.70/api/wp-login.php \
     --data "log=root&pwd=root" \
     --cookie-jar cookies

curl --request GET http://45.76.127.70/api/wp-admin/admin-ajax.php?action=collection_dump \
     --cookie cookies \
  > test-data/export-$(date +%F).json
