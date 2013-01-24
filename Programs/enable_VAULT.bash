#!/bin/bash

pgservice_core=`"$WIFF_ROOT"/wiff --getValue=core_db`

if [ -z "$pgservice_core" ]; then
    echo "Undefined or empty pgservice_core!"
    exit 1
fi

PGSERVICE="$pgservice_core" psql --set ON_ERROR_STOP=on -f - <<EOF
-- Activate the VAULT application
UPDATE application SET available = 'Y' WHERE name = 'VAULT';
EOF
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error enabling application 'VAULT'."
    exit $RET
fi