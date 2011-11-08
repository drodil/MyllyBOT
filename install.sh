#!/bin/bash

# Simple bash script for quick installation of MyllyBot
# Work in progress.

idir="/var/MyllyBot"
cdir="/var/MyllyBot/config"
droot="/var/www/MyllyBot"
mysqluser=""
mysqlpwd=""
mysqldb=""

print_logo()
{
    clear
    echo " __  __       _ _       ____        _   "
    echo "|  \/  |     | | |     |  _ \      | |  "
    echo "| \  / |_   _| | |_   _| |_) | ___ | |_ "
    echo "| |\/| | | | | | | | | |  _ < / _ \| __|"
    echo "| |  | | |_| | | | |_| | |_) | (_) | |_ "
    echo "|_|  |_|\__, |_|_|\__, |____/ \___/ \__|"
    echo "         __/ |     __/ |                "
    echo "        |___/     |___/                 "
    echo ""
    echo ""		
    echo "     THE Open source PHP IRC bot        "
    echo "        INSTALLATION SCRIPT             "
    echo "----------------------------------------"
    echo ""
}

print_logo

# Check if mysqld is running
if ! ps -C mysqld > /dev/null
then
    mysqlv = $(mysql -V)
    echo "Running MySQL version is: $mysqlv"
else
    echo "Could not find MySQL running"
    # exit 1
fi

# Check if PHP is installed
if [ ! -z "$(php -i | awk '/PHP version/')" ];
then
    phpversion = $(php -v)
    echo "Running PHP version: $phpversion"
else
    echo "Could not find PHP"
    # exit 1
fi

# Check for installation place
echo ""
echo "Where do you want to install MyllyBot?"
echo -n "(default: $idir) > "
read installdir

if [ -z "$installdir" ]
then 
    installdir="$idir"
fi

# Check for configuration file save place
echo ""
echo "Where do you want to install configuration files?"
echo -n "(default: $cdir) > "
read confdir

if [ -z "$confdir" ]
then
    confdir="$cdir"
fi

# Check if the user wants to install Web UI
echo ""
echo -n "Do you want to install MyllyBot Web UI (Y/n)? > "
read installwebui

if [ "$installwebui" != "n" ]
then
    echo ""
    echo "Where do you want to install Web UI?"
    echo -n "(default: $droot) > "
    read documentroot

    if [ -z "$documentroot" ]
    then
        documentroot="$droot"
    fi
fi

# MySQL settings
echo ""
echo -n "MySQL username for MyllyBot > "
read mysqluser

echo ""
echo -n "MySQL password for $mysqluser > "
read mysqlpwd

echo ""
echo -n "MySQL database for MyllyBot > "
read mysqldb

# TODO: Bot administrator settings to be asked

# TODO: Bot basic settings can be asked here too

print_logo
echo "SELECTED CONFIGURATION:"
echo ""
echo "Bot installation dir: $installdir"
echo "Bot config dir: $confdir"

if [ "$installwebui" != "n" ]
then
    echo "Bot WebUI installation dir: $documentroot"
else
    echo "Bot WebUI will not be installed."
fi

echo "MySQL user: $mysqluser"
echo "MySQL password: $mysqlpwd"
echo "MySQL database: $mysqldb"

echo ""
echo "----------------------------------------"
echo ""
echo -n "Is this correct (y/N)? > "
read correct

# If correct, start installation..
if [ "$correct" == "y" ]
then
    echo "----------------------------------------"
    echo ""
    echo "Installing..."
    
    # Create config file & dir
    mkdir -p $confdir
    
    conffile="$confdir/bot_config.php"
    
    echo "<?php" > $conffile
    echo "  \$mysql_user = \"$mysqluser\";" >> $conffile
    echo "  \$mysql_pwd = \"$mysqlpwd\";" >> $conffile
    echo "  \$mysql_db = \"$mysqldb\";" >> $conffile
    echo "?>" >> $conffile
    
    echo "<?php include('$conffile'); ?>" > conf_inc.php
    
    # Create Web UI dir
    if [ "$installwebui" != "n" ]
    then
        mkdir -p $documentroot
        cp -f conf_inc.php $documentroot
        cp -R ./www/* $documentroot
    fi
    
    # Create bot installation dir
    mkdir -p $installdir
    mv -f conf_inc.php $installdir
    cp -R ./bot/* $installdir
    
    # TODO: Create PHP script to create databases and run it
    
    echo "Installation done."
    echo ""
    
    # TODO: Do you want to start the bot now? - also bot default settins should be 
    #       asked before doing that :)
    
else
    echo "Aborting.."
    exit 1
fi

