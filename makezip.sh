#! /bin/bash

rm -f legacy-sitemap.zip

# copy the build over into the public webroot
mkdir public/legacy-sitemap/app
cp -a jsapp/build/* public/legacy-sitemap/app/

mkdir -p legacy-sitemap-bundle/legacy-sitemap/
cp -a public legacy-sitemap-bundle
cp -a legacy-sitemap/vendor legacy-sitemap-bundle/legacy-sitemap/
cp -a legacy-sitemap/src legacy-sitemap-bundle/legacy-sitemap/
cp -a legacy-sitemap/templates legacy-sitemap-bundle/legacy-sitemap/
zip -r legacy-sitemap.zip legacy-sitemap-bundle
rm -rf legacy-sitemap-bundle


