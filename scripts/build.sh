#!/usr/bin/env bash

echo "Cleaning target"
rm -fr vendor dist

echo "Preparing vendors"
composer install --no-dev --optimize-autoloader

echo "Preparing target directory"
mkdir dist
mkdir dist/pay-by-paynow-pl

echo "Copying sources"
cp -R assets dist
cp -R vendor dist/pay-by-paynow-pl
cp -R src/* dist/pay-by-paynow-pl
cp -f readme.txt dist/pay-by-paynow-pl/
cp -f changelog.txt dist/pay-by-paynow-pl/
cp -f LICENSE dist/pay-by-paynow-pl/license.txt

echo "Preparing zip"
cd dist
zip -r pay-by-paynow-pl.zip pay-by-paynow-pl