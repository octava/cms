#!/usr/bin/env bash
./app/console doctrine:schema:drop --force
./app/console doctrine:schema:create
./app/console octava:administrator:import-acl-resources
./app/console doctrine:fixtures:load -n
./app/console octava:mui:translation:update-db
./app/console cache:clear
