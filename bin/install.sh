#!/usr/bin/env bash
read -p "Are you sure? " -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]];
then
    ./app/console cache:clear
    ./app/console doctrine:schema:drop --force
    ./app/console doctrine:schema:create
    ./app/console octava:administrator:import-acl-resources
    ./app/console doctrine:fixtures:load -n
    ./app/console octava:mui:translation:update-db
    ./app/console cache:clear
fi
