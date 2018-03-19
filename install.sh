#! /bin/bash

cd "$( dirname "${BASH_SOURCE[0]}" )"

if [[ -e ../legacy-sitemap.env ]] ; then
    source ../legacy-sitemap.env
elif [[ -e ./legacy-sitemap.env ]] ; then
    echo You should move legacy-sitemap.env up one directory.
    source ./legacy-sitemap.env
else
    echo "You need to set up the legacy-sitemap.env file before running this."
    echo "Please look at the README.md file."
    exit;
fi

if [[ -z "$LEGACY_SITEMAP_DOCROOT" ]] ; then
    echo "The env file needs to have a LEGACY_SITEMAP_DOCROOT defined."
    exit
fi

if [[ -z "$LEGACY_SITEMAP_API_KEY" ]] ; then
    echo "The env file needs to have a LEGACY_SITEMAP_API_KEY defined."
    exit
fi

if [[ -z "$LEGACY_SITEMAP_HTPASSWD" ]] ; then
    echo "The env file needs to have a LEGACY_SITEMAP_HTPASSWD defined."
    exit
fi

echo Removing old server code.
rm -rf ../legacy-sitemap

echo Installing new server code from bundle.
cp -ar legacy-sitemap ..


echo Cleaning out old web server integration.
rm -rf ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap/app
rm -rf ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap/api

echo Installing new code into the web docroot.
if [[ ! -e ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap ]] ; then 
    mkdir ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap
fi

cp -ar public/legacy-sitemap/app ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap/app
cp -ar public/legacy-sitemap/api ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap/api

echo Creating .htaccess for app.
export LEGACY_SITEMAP_HTPASSWD
cat htaccess-app | perl -np -e 's/__HTPASSWD__/$ENV{"LEGACY_SITEMAP_HTPASSWD"}/' > ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap/app/.htaccess

echo Creating .htaccess for api.
cp htaccess-api ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap/api/.htaccess

echo Copying api key into the app.
echo "window.LEGACY_SITEMAP_API_KEY=\"$LEGACY_SITEMAP_API_KEY\"" > ${LEGACY_SITEMAP_DOCROOT}legacy-sitemap/app/key.js

echo Testing for .htaccess being loaded.
TEST=(`curl  -s --head ${LEGACY_SITEMAP_URL}legacy-sitemap/app/ | grep Authenticate`)
if [[ $TEST ]] ; then
    echo
    echo .htacces appears to be OK.
    echo A password is required to view the app.
    echo
else
    echo 
    echo .htaccess appears to be ignored.
    echo Add "AllowOverride all" to the web server config file.
    echo Put it into a Directory section of the virtual host config.
    echo
fi
