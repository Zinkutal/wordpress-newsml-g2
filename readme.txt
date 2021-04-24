=== NewsML-G2 Importer ===
Contributors: BernhardPunz, ced99, mephinet, avdkucherov
Tags: NewsML-G2, import, APA, IPTC
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 1.2.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Imports NewsML-G2 data and makes them accessible from your Wordpress installation.

== Description ==

This plugin provides an simple and easy way to import NewsML-G2 documents into Wordpress and to publish them as posts inside your blog.
It imports all .xml documents containing NewsML-G2 data found in a provided folder into the Wordpress database and saves them as newsml_post.
newsml_post is a custom post type with a few additional metafields which contain the data, that's stored in the NewsML-G2 document.
You can access the files through HTTP and FTP. If provided, you can use a file called rss.xml. This file needs to have the filenames to import in an <item> element inside an <link> element for each file.


== Installation ==

1. Upload the folder 'newsml-g2-importer' to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. On the 'Settings' page enter the URL containing the desired NewsML-G2 files. If you are using FTP, enter your FTP credentials and check the corresponding checkbox.
4. If desired check the rss.xml checkbox if you want to get your filenames out of a rss.xml file.
5. Click the "Import Media Topics" button to import all mediatopics from the IPTC server. Warning! This may take a while.
6. After the mediatopics are imported, click the "Update newsposts" button to import all NewsML-G2 messages found in the denoted folder or the rss.xml file. Warning! This may take a while.
   This will also register the cron for the automatic updates.
7. `apply_filters( 'newsml_include_filter', '' );`
   Paste this snippet into the content.php or single.php file of your theme (preferably before the_content()) where you want the subtitle, author, date, categories, locations and post images to be shown.
   The name of the file depends on the used theme.
   Possible/known filenames: content.php, single.php, content-single.php
8. Set your Permalinks structure through the 'Settings -> Permalinks' menu in Wordpress to 'Post name'. Otherwise your newsml_posts will not be shown.


== Frequently Asked Questions ==

= Have a question? =

Contact me!


== Screenshots ==

1. The first portion of the settings page, showing the configuration for accessing the files.
2. The second portion of the settings page, showing miscellaneous settings.
3. The third portion of the settings page, showing the buttons for the main actions of the plugin.

== Changelog ==

= 1.0 =
* Initial release

= 1.0.1 =
* removed mentions of "kiosk"

= 1.0.2 =
* fixed saving of expire time

= 1.1.0 =
* Added Innodata parser
* Recursive check for http access

= 1.1.1 =
* PHP error fixes

= 1.2.0 =
* SFTP support
* .zip support for FTP/SFTP

= 1.2.1 =
* Publish posts option

= 1.2.2 =
* Updated Innodata parser mapping & better new_post handling

= 1.2.3 =
* Updated Innodata parser mapping

= 1.2.4 =
* Updated Innodata parser mapping
* Fixed notices on newsml_post update

= 1.2.5 =
* Updated Innodata parser mapping
* Updated logic for newsnls to update

= 1.2.6 =
* Added autoloader support

= 1.2.7 =
* Added phpcs && gh actions check

= 1.2.8 =
* Updated composer info

= 1.2.9 =
* Fatal error fix on plugin activation

= 1.2.10 =
* Session start notice fix

= 1.2.11 =
* Added dynamic mediatopic generation
* Updated guid usage on import vs title
* Updated innodata mappings
* Fixed images dir logic

### 1.2.12
* Fixed duplicates import
* Added nl2br for descriptions imported from innodata

== Upgrade Notice ==

= 1.0 =
* Initial release
