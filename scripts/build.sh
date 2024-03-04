#!/usr/bin/env bash

echo "Cleaning target"
rm -fr vendor dist

echo "Preparing vendors"
composer install --no-dev --optimize-autoloader

echo "Preparing target directory"
mkdir dist
mkdir dist/leaselink-plugin-pl

echo "Copying sources"
cp -R assets dist
cp -R vendor dist/leaselink-plugin-pl
cp -R src/* dist/leaselink-plugin-pl
cp -f readme.txt dist/leaselink-plugin-pl/
cp -f changelog.txt dist/leaselink-plugin-pl/
cp -f LICENSE dist/leaselink-plugin-pl/license.txt

echo "Preparing zip"
cd dist
zip -r leaselink-plugin-pl.zip leaselink-plugin-pl
