#!/bin/bash

# Simple bash script for quick installation of MyllyBot
# Work in progress.

idir="~/MyllyBot"
droot="/var/www"

# TODO : Initial check for PHP to be installed

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
echo "Where do you want to install MyllyBot?"
echo -n "(default: ~/MyllyBot) > "
read installdir

if [ -z "$installdir" ]
then 
    installdir="$idir"
fi

# TODO: Check if the user wants to install the WEB UI or not

echo ""
echo "Where is your http server document root?"
echo -n "(default: /var/www) > "
read documentroot

if [ -z "$documentroot" ]
then
    documentroot="$droot"
fi

# TODO: MySQL Configuration to be asked and config.php generation

# TODO: Bot administrator settings to be asked

# TODO: Bot basic settings can be asked here too

# Output the user selected configuration

print_logo
echo "SELECTED CONFIGURATION:"
echo ""
echo "Installing bot to $installdir"
echo "Installing WEB UI to $documentroot/MyllyBot"
echo ""
echo "----------------------------------------"
echo ""
echo -n "Is this correct (y/n)? > "
read correct

# If correct, start installation..
if [ "$correct" == "y" ]
then
    echo "Installing.."
else
    echo "Aborting.."
    exit 1
fi

# TODO: Install?
# Making dirs, copying files, setting permissions and running database
# generation script through PHP.

# TODO: Ask user if he wants to run the bot now

