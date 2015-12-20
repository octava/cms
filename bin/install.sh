#!/usr/bin/env bash
read -p "Are you sure (y/N)? " -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]];
then
    ./app/console cache:clear
    ./app/console doctrine:schema:drop --force
    ./app/console doctrine:schema:create
    ./app/console octava:mui:translation:update-db
    ./app/console doctrine:fixtures:load -n
    ./app/console cache:clear
    ./app/console octava:administrator:import-acl-resources
    ./app/console octava:administrator:grant-all
    ./app/console octava:admin-menu:generate
fi
