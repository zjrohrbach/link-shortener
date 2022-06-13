# link-shortener

A simple app backed by MySQL to shorten url's.

## Installation

1. Create a new database on your MySQL Sever
2. Create an `.htpasswd` file if you don't already have one.  ([Tutorial](https://www.hostwinds.com/tutorials/create-use-htpasswd))
3. Update lines 3-8 of `functions.php` to match your MySQL server and a database. 
4. Update line 4 of `.htaccess` to point to the location of the `.htpasswd` file generated in step 2.
5. Copy all files to your web server, probably in a directory with a short name, *i.e.* `https://www.your-domain.com/goto/`
6. Point your browser to `https://www.your-domain.com/goto/db_initialize.php`.  Enter your login information and then initialize the database.
7. You are all set!