#! /bin/bash

# copy from test
echo Copying files from test server.
cp -ar /www/riceball.com/public/html-files-to-import html/
cp -ar /www/riceball.com/public/trash html/
cp -ar /www/riceball.com/public/r html/
cp -ar /www/riceball.com/public/d html/

echo
echo Making installation ZIP and installing into vagrant-test.
cd ..
./makezip.sh
cd vagrant-test
mv ../legacy-sitemap.zip .
unzip legacy-sitemap.zip
rm legacy-sitemap.zip

IP=`cat Vagrantfile | grep ip | awk -e '{print $4}' | sed s/\"//g`

echo
echo
echo Next, you need to go into the Vagrant VM, edit the configuration
echo files, and run the install.sh script.
echo
echo
echo This Vagrant URLs are 
echo WordPress http://$IP/
echo WordPress Admin http://$IP/wp-admin/
echo Legacy Sitemap http://$IP/legacy-sitemap/app/
echo
