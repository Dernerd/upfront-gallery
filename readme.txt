== Changelog ==

= 1.0.0 = 
* Updated for UpFront 1.2.0

= 0.0.1 =
* Initial plugin based on Headway Rocket Gallery plugin version 1.3.6



Migration from Headway Rocket Gallery plugin

Backup all.

Install modified UpFront, do not activate it
Install UpFront Gallery plugin, do not activate it
Install UpFront Lifesaver, activate this
Deactivate Headway Gallery

Run migration process.

Activate UpFront
Activate UpFront Gallery Plugin

Enter to phpMyAdmin and execute this query:

UPDATE wp_posts SET post_type = 'upfront_gallery' WHERE post_type = 'hwr_gallery';

"wp_" is the default prefix, on your site it could be something else like 'ql' (wp_posts -> ql_post)

Test the website
