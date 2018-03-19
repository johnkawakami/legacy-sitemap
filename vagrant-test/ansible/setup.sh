#! /bin/bash
# to use this, source the file. It'll set up a couple variables that
# help you develop the playbook. See the Readme.
#
key=$(vagrant ssh-config | grep IdentityFile | awk -e '{print $2}'| sed s/\"//g )
options="-i host_ip --private-key=$key --user=vagrant"
