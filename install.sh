#!/usr/bin/env bash

echo "Welcome to ClickthuluFed.  Installation will take a couple of minutes.";

# check PHP version
PHPVER=$(php -v | head -1 | awk '{print $2}')
PHPMAJ="$(cut -d '.' -f 1 <<< "$PHPVER")";
if [ $PHPMAJ -ne 8 ]; then
  echo "ClickthuluFed requires PHP Version 8.* to run.  Please check your version:  $PHPVER";
  return 1;
fi

echo "PHP Version $PHPVER found.  Checking for Composer";

# check for composer

COMPSR=$(which composer)
if [ $COMPSR == "" ]; then
    echo "Composer not found.  Should I attempt to install? y/N"
    read INSTCOMPSR

    if [ $INSTCOMPSR == "y" ] | [ $INSTCOMPSR == "Y" ]; then
        echo "Checking for wget..."
        WGET=$(which wget);
        if [ $WGET == "" ]; then
            echo "Could not download composer.  Please install composer and run the installer again.";
            return 1;
        fi


        echo "Downloading composer...";
        wget https://getcomposer.org/installer -O ./composer.phar
        chmod +x ./composer.phar
        COMPSR=./composer.phar
    fi

fi

# check for .env file

# generate a new APP_SECRET

# request details on database

# request details on smtp

# write new .env file

# install via composer
$COMPSR install

# do database updates

# Setup owner account