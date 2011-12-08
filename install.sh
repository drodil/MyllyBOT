#!/bin/bash

# Simple bash script for quick installation of MyllyBot
# Work in progress.

#default values

idir="/var/MyllyBot"
cdir="/var/MyllyBot/config"
droot="/var/www/MyllyBot"
sqlhost="localhost"

bname="MyllyBot"
bident="Myle"
brealname="THEMyllyBot"
bserver="irc.cs.hut.fi"
bport="6667"
botchannels=""
botproxy=""
botproxyport=""
botproxyuser=""

btimezone="Europe/Helsinki"

# Printing logo function
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
if ps ax | grep -v grep | grep 'mysqld' > /dev/null
then
    echo "MySQL found. Continuing.."
else
    echo "Could not find MySQL running. Aborting.."
    #exit 1
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
echo -n "MySQL host (default: $sqlhost) > "
read mysqlhost

if [ -z "$mysqlhost" ]
then
    mysqlhost="$sqlhost"
fi

echo ""
echo -n "MySQL username for MyllyBot > "
read mysqluser

echo ""
echo -n "MySQL password for $mysqluser > "
read mysqlpwd

echo ""
echo -n "MySQL database for MyllyBot > "
read mysqldb

# Bot settings
echo ""
echo -n "Bot name (default: $bname) > "
read botname

if [ -z "$botname" ]
then
    botname="$bname"
fi

echo ""
echo -n "Bot admin username > "
read botusr

echo ""
echo -n "Bot admin password > "
read botpwd

echo ""
echo -n "Bot ident (default: $bident) > "
read botident

if [ -z "$botident" ]
then
    botident="$bident"
fi

echo ""
echo -n "Bot real name (default: $brealname) > "
read botrealname

if [ -z "$botrealname" ]
then
    botrealname="$brealname"
fi

echo ""
echo -n "IRC server (default: $bserver) > "
read botserver

if [ -z "$botserver" ]
then
    botserver="$bserver"
fi

echo ""
echo -n "IRC server port (default: $bport) > "
read botport

if [ -z "$botport" ]
then
    botport="$bport"
fi

# Proxy settings
echo ""
echo -n "Proxy server > "
read botproxy

if [ ! -z "$botproxy" ]
then
    echo ""
    echo -n "Proxy server port > "
    read botproxyport
    
    echo ""
    echo -n "Proxy username/password (format username:password) > "
    read botproxyuser
fi

# Channel settings
echo ""
echo "Type in bot channels to join (type empty to quit):"
newchan="#myllybot"
i=1
while [ ! -z "$newchan"  ]
do
    echo -n "Enter channel ($i) > "
    read newchan
    
    if [ ! -z "$newchan" ]
    then
        botchannels[$i-1]=$newchan
        i=`expr $i + 1`
    fi
done

# Timezone settings
echo ""
echo -n "Timezone (default: $btimezone) > "
read bottimezone

if [ -z "$bottimezone" ]
then
    bottimezone="$btimezone"
fi

# Print the configuration
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

echo ""
echo "MySQL host: $mysqlhost"
echo "MySQL user: $mysqluser"
echo "MySQL password: $mysqlpwd"
echo "MySQL database: $mysqldb"

echo ""
echo "Bot name: $botname"
echo "Bot admin username: $botusr"
echo "Bot admin password: $botpwd"
echo "Bot ident: $botident"
echo "Bot real name: $botrealname"

echo ""
echo "IRC server: $botserver"
echo "IRC server port: $botport"
if [ ! -z "$botproxy" ]
then
    echo "Proxy: $botproxy:$botproxyport"
    echo "Proxy username:password: $botproxyuser"
fi

echo ""
echo "Bot channels:"
for chan in ${botchannels[@]}
do
    echo $chan
done

echo ""
echo "Timezone: $bottimezone"

echo ""
echo "----------------------------------------"
echo ""
echo -n "Is this correct (Y/n)? > "
read correct

# If correct, start installation..
if [ "$correct" != "n" ]
then
    echo "----------------------------------------"
    echo ""
    echo "Installing..."
    
    # Create config file & dir
    mkdir -p $confdir
    
    conffile="$confdir/bot_config.php"
    
    echo "<?php" > $conffile
    echo "" >> $conffile
    echo "      \$bot['console_output'] = true;" >> $conffile
    echo "      \$bot['database_debug'] = false;" >> $conffile
    echo "      \$bot['file_debug'] = false;" >> $conffile
    echo "      \$bot['file_debug_location'] = \"\";" >> $conffile
    echo "" >> $conffile
    echo "      \$cfg['database_host'] = \"$mysqlhost\";" >> $conffile
    echo "      \$cfg['database_user'] = \"$mysqluser\";" >> $conffile
    echo "      \$cfg['database_password'] = \"$mysqlpwd\";" >> $conffile
    echo "      \$cfg['database_name'] = \"$mysqldb\";" >> $conffile
    echo "" >> $conffile
    echo "      \$bot['botnick'] = \"$botname\";" >> $conffile
    echo "      \$bot['botpassword'] = \"$botpwd\";" >> $conffile
    echo "      \$bot['botident'] = \"$botident\";" >> $conffile
    echo "      \$bot['botrealname'] = \"$botrealname\";" >> $conffile
    echo "" >> $conffile
    echo "      \$bot['serveraddress'] = \"$botserver\";" >> $conffile
    echo "      \$bot['serverport'] = \"$botport\";" >> $conffile
    echo -n "      \$bot['serverchannel'] = array(" >> $conffile
    
    tmp=1
    for chan in ${botchannels[@]}
    do
        if [ $tmp -eq 1 ] 
        then
            echo -n "\"$chan\"" >> $conffile
            tmp=0
        else
            echo -n ", \"$chan\"" >> $conffile
        fi
    done
    echo ");" >> $conffile
    
    echo "" >> $conffile
    echo "      \$bot['http_proxy'] = \"$botproxy\";" >> $conffile
    echo "      \$bot['http_proxy_port'] = \"$botproxyport\";" >> $conffile
    echo "      \$bot['http_prxy_userpwd'] = \"$botproxyuser\";" >> $conffile
    
    echo "" >> $conffile
    echo "      \$bot['timezone'] = \"$bottimezone\";" >> $conffile
    
    echo "?>" >> $conffile
    
    # Override the current configuration file
    echo "<?php include('$conffile'); ?>" > conf_inc.php
    
    # Create Web UI dir
    if [ "$installwebui" != "n" ]
    then
        mkdir -p $documentroot
        cp -R ./www/* $documentroot
        cp -R ./misc/* $documentroot
        cp -f conf_inc.php $documentroot
    fi
    
    # Create bot installation dir
    mkdir -p $installdir
    cp -Rf ./misc/* $installdir
    cp -Rf ./bot/* $installdir
    cp -f conf_inc.php $installdir
    
    # Run database installation script to create tables
    cp -f conf_inc.php database/
    php database/db_install.php

    # Set up access for administrator
    echo "<?php include('$conffile');" > database/usr_access.php
    echo "\$query='INSERT INTO access VALUES ('$botusr', MD5('$botpwd'), '5', '*', '*');" >> database/usr_access.php
    echo "mysql_query(\$query); ?>" >> database/usr_access.php
    php database/usr_access.php
    rm -f database/usr_access.php
    
    # Remove temp conf_inc.php
    rm -rf conf_inc.php
    
    # TODO: Create PHP script to create databases and run it
    
    echo "Installation done."
    echo ""
    
    # TODO: Do you want to start the bot now? - also bot default settins should be 
    #       asked before doing that :)
    
else
    echo "Aborting.."
    exit 1
fi

