# Clickthlu Fed(erated)

## A Little History (You can skip this part)
Many years ago I began writing comic engine software with the idea that it be simple, dedicated and designed to
be as flexible as possible for users who didn't want to spend a lot of time fighting with WordPress/ComicPress.  

This was 2008.

The end result, written over many commuter hours on the Boston MTA was the original version of Clickthulu.  Single use,
terrible to maintain, and easy to get everything screwed up over the course of a few minutes of inattention.

Clickthulu 2 came out 3 months later.  This was the first enterprise version, designed so instead of having to update 
each individual instance by myself (I was the server admin who hosted number of small webcomics), I could just wrap 
it in an RPM and update the core.

Clickthulu 3 debuted in 2015, using Composer instead of the home built namespace decompiler, it became faster and more 
feature filled and lasted for 8 years.

Then, in 2022, I was introduced to ActivityPub and Mastodon, and there was an epiphany.  I had stopped adding new features
to Clickthulu 3, and started looking at it from a different perspective.  Online comics have a problem really.  If you 
don't have an audience starting out, it's going to be very difficult to build an audience from scratch.  

So what does this have to do with Clickthulu?  I'm hoping, via the powers of Federation, we'll be able to make comics 
discoverable again.  To allow users to get recommendations based on what other users are reading, and to keep the discourse
alive by bringing comic pages and comments into the federated feeds of other servers.  

## Installation

### ClickthuluFed is built on Symfony 6.3


1) Make sure PHP 8.1, Composer and your favorite webserver are installed (I personally prefer Apache, but nginx has also been tested)
2) Download the repository and unpack it into a directory
3) Create an empty database, save the credentials for part 2


Well, that gets the first part done.  Now for the part 2.

#### Note:  I plan to put together a nice little install script that will do everything in Part 2.  It's on the to-do list.  It's just not to-done yet.

1) Download or Clone ClickthuluFed
2) Run ***php setup.php***
3) Configure your webserver to point to the public directory.

If you don't want to run setup.php you can do the following steps:

1) Create a .env file by copying the .env.dist file and putting in the appropriate values 
2) Run ***composer install --no-scripts*** to download the libraries 
3) Run ***./bin/console doctrine:migrations:migrate*** to initialize the database
4) Run ***./bin/console app:new-user <username> <password> <email> OWNER*** to create your initial user and give it the owner role.
5) Run ***./bin/console app:set-config server_url <url>*** to assign the server's url
6) Run ***./bin/console app:set-config email_from_name <name>*** To assign the Email From label
7) Run ***./bin/console app:set-config email_from_address <email>*** To assign the Email From address
8) Make directory ***./var/cache*** and set permissions for the webserver to write to it
9) Make directory ***./storage*** and set permissions for the webserver to write to it
10) Go get a drink, you're done, and it would have just been easier to use the setup script next time.

If you are updating ClickthuluFed to the latest code, you can run setup.php again.  It will skip over the parts you have already done, updating only the composer libraries and making any necessary database changes.


## Licensing etc

Right, so ClickthuluFed is licensed under the GPL3 license, which means you can do pretty much anything with it as long as 
you give back.  You can find the details of the license in the **LICENSE** file.


As always, I'm happy to help, form an orderly queue to the left.
