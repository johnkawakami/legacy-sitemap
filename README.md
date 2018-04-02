# legacy-sitemap

See the [website][https://johnkawakami.github.io/legacy-sitemap/]

## Description

This is an add-in tool for websites to do the following:

* Create a sitemap for multiple directories that contain HTML files.
* Review and categorize the pages for importation into WordPress (or another CMS).
* Manage a list of 301 redirects from the old HTML files to new articles.

Originally used to move articles from an archived Drupal 6 site to a WordPress
site.

## Installation

Download `legacy-sitemap.zip`.

Unzip it, and a folder called `legacy-sitemap-bundle` is extracted.

Inside it, there's a script, install.sh, and an environment file,
legacy-sitemap.env.example.

You need to do the following:

1. Edit the env file to configure the server installation.
2. Run install.sh.
3. Fix any server config issues related to security.
4. Edit the server's settings.php file to finalize how the application integrates with the site.

### Setting up environment variables in env

This application uses dotenv to load environment variables that
change with the server environment.  It's also used to perform the
installation.

The file contains URLs, file paths, and an API key.

Rename the file `legacy-sitemap.env.example` to `legacy-sitemap.env`, and move it up one directory.

    mv legacy-sitemap.env.example ../legacy-sitemap.env

Here are two example configs. We'll go into each variable in detail below.

This is my test environment configuration:

    LEGACY_SITEMAP_DOCROOT="/var/www/html/"
    LEGACY_SITEMAP_URL="http://192.168.33.21/"
    LEGACY_SITEMAP_API="http://192.168.33.21/legacy-sitemap/api/"
    LEGACY_SITEMAP_DATABASE="/var/www/legacy-sitemap.sqlite3"
    LEGACY_SITEMAP_API_KEY=""
    LEGACY_SITEMAP_HTPASSWD="/var/www/htaccess"

This is my production/live environment configuration:

    LEGACY_SITEMAP_DOCROOT="/www/riceball.com/public/"
    LEGACY_SITEMAP_URL="http://riceball.com/"
    LEGACY_SITEMAP_DATABASE="/www/riceball.com/legacy-sitemap.sqlite3"
    LEGACY_SITEMAP_API="http://riceball.com/legacy-sitemap/api/"
    LEGACY_SITEMAP_DATABASE="/www/riceball.com/legacy-sitemap.sqlite3"
    LEGACY_SITEMAP_API_KEY="thisisasecretNotmyactualpassword"
    LEGACY_SITEMAP_HTPASSWD="/www/riceball.com/htaccess"

#### What each variable means

##### LEGACY_SITEMAP_DOCROOT

The docroot is the directory where the web server delivers pages.

##### LEGACY_SITEMAP_URL

This is the URL for the website.

##### LEGACY_SITEMAP_API

This is the URL to the API.  The API is located in the /legacy-sitemap/api/ path
and shouldn't be changed.  

This setting exists because the development and test environments can run separate
servers for the API and the frontend development.

##### LEGACY_SITEMAP_DATABASE

Path to the SQLite3 database file.

##### LEGACY_SITEMAP_API_KEY

Type in a long string of random characters here. Don't include any quotes.

This value is copied into the frontend application, and used by the API server.

##### LEGACY_SITEMAP_HTPASSWD

This is the full path to an htpasswd file that's used to secure the frontend app.

If you don't use the Apache security features, an unauthorized person can go into your
site and alter your website.

If you already have passwords, you might be able to use an existing file.

### Running install.sh

At this time, installation depends on Perl and CURL.

After you've configured legacy-sitemap.env, run:

    ./install.sh

Read the messages.

If the last message says that .htaccess is ignored, you need to alter your
server configuration to use .htaccess files.

### Altering the Apache Configuration to Use .htaccess Files

Find the configuration for your virtual server.  On Debian systems, it's at `/etc/apache2/sites-enables/000-default`.

Add a stanza like this:

    <Directory /var/www/html>
    AllowOverride all
    </Directory>

Then, restart the sever:

    sudo service apache2 restart

Test the security by going to your website, to the URL `http://example.com/legacy-sitemap/app`.
Replace example.com with your server, of course.

### Creating an htpasswd File

If you don't have an existing htpasswd file, you can use the `htpasswd` command to create it.

    source legacy-sitemap.env
    htpasswd -c $LEGACY_SITEMAP_HTPASSWD yourusername
    # type in the password

### Configuring the Application with settings.php

The previous sections, about legacy-sitemap.env, configured both the front
and backend.  

`settings.php` configures only the backend.

This file is located at legacy-sitemap/src/settings.php

The included configuration reflects a file system that looks like this:

    [docroot]/
    [docroot]/d
    [docroot]/d/node/
    [docroot]/d/content/
    [docroot]/r/
    [docroot]/html-files-to-import/
    [docroot]/trash/

My system also has a WordPress installation, but I've omitted those directories.


#### What you can change

You can change the number of directories that get scanned for HTML files,
and the locations of the directories for importing, retaining, and trashing
files.

If you have your files in /archive/, in the subdirectories /archive/people/, 
/archive/groups/, and /archive/events/, you configure it like this:

        'text-sitemap' => [
            'directories' => [
                'archive/people/',
                'archive/groups/',
                'archive/events/',
            ],...

You can also specify the destination for the sitemap.txt file:

            ...],
            'sitemap-path' => 'archive/sitemap.txt',
            'remove-from-title' => '| a computer hobbyist\'s notebook',
            'root-directory' => $docroot,
            'root-url' => $urlroot

Note that the directories and `sitemap-path` don't have a leading "/".
All paths are relative to the website's docroot, `root-directory`.
(The docroot configuration is set in config.ini, below.)

If your importer tool wants files in a directory named 'html-import', 
it's set in the `file-mover` service:


        'file-mover' => [
            'states' => [
                'trash' => 'trash/',
                'import' => 'html-import/',
                'retain' => 'r/'
            ],...

Change the other states to conform to what you need.  
The state names, `trash`, `import`, and `retain` 
cannot be changed.

### Submitting Your Sitemap to Search Engines

The best way to indicate to all search engines that
you have a sitemap is by adding a Sitemap line to
your robots.txt, like this.

    Sitemap: http://riceball.com/d/sitemap.txt

This way, all search engines pick it up.

### Creating an .htaccess File to Redirect Legacy URLs

As files are moved out of the old site, they are
recorded into the database.  Later, after the import
is performed, lists of old files, and their new URLs
can be entered into the Legacy Sitemap application,
to create 301 redirects.

The application works by reading in any existing redirects,
and then writing them, and new redirects, out to an 
.htaccess file.

The .htaccess file is specified in `settings.php` under the key

    'apache-redirect-table-gateway' => [ 'htaccess' => ... ]

The application searches for two strings, and writes the redirect rules between them:

    # BEGIN PAGE REDIRECTS
	# END PAGE REDIRECTS

If you have an existing .htaccess file, you can insert those two strings, and the
application will dump the redirects in there.

If you don't have an existing .htaccess file, create one like this:

    RewriteEngine on
    # BEGIN PAGE REDIRECTS
	# END PAGE REDIRECTS

And put it in the root directory that contains your old files.  (Don't put it
in the docroot.)

#### Integrating Existing Redirects

If you have existing redirects, you should probably keep them out of the block
that the application will manage.
The application uses a restrictive regex, and doesn't maintain the order of
the redirects.  It doesn't support any kind of wildcards.

However, if you want to try to subject your existing URLs to this application, you should
use a tool to de-duplicate rules and remove redirect chains. See:

    http://riceball.com/misc/redirect-reducer/

Legacy Sitemaps does its own kind of de-duplication, but it may break chains of redirects,
and harm SEO. That's why you need to clean up your redirects, first.

(It's also just a good idea to clean your redirects.)


## Using the Application

The application presents a list of articles from the archives.

Click on one to view it in a popup.

Choose one of the three dispositions, or close it to deal with it later.

Once you have some articles to import, open a new tab and run the importer tool.

The importer tool for WordPress shows a list of Redirects.  You can copy these.

Back in Legacy Sitemaps, click on the "Redirect" link in the upper right.

Paste the list of redirects into the form, and submit.

Click on the links to test the redirects.

### What is Retain?

Retaining an article removes it from the site, but doesn't import it into
the new CMS.

I added this feature to remove off-topic articles, but retain them for reuse
on other websites.

Trashing is reserved for low-quality content.

You can download the retained file directory and do with it what you want.
