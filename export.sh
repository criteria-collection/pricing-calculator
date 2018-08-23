#!/bin/bash

curl -X GET http://45.76.127.70/api/wp-admin/admin-ajax.php?action=collection_dump \
  --cookie cookies \
  > test-data/export-$(date +%F).json
