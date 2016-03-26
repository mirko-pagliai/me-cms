# 2.x branch
## 2.4 branch
### 2.4.7
* fixed bug for localized static pages.

### 2.4.6
* fixed some bugs.

### 2.4.5
* logged users can view future posts, future pages and drafts;
* the status of a record is properly shown in the admin panel, including publication in the future;
* added filter for tags;
* added patterns table for database backups;
* now you can restore database backups;
* now you can download and deletes logs.

### 2.4.4
* fixed a major bug for the menu builder;
* fixed bug on log advanced viewer.

### 2.4.3
* added `Logs` controller and templates. Log management has improved. The log can be displayed as plain or serialized;
* added "who is" and "map" functionalities for IP addresses on logs;
* each time that is called, the `SerializedLog` adapter writes the normal log and a serialized copy of the log.  

### 2.4.2
* fixed bug on "popular tags" widget.

### 2.4.1
* logs are turned into arrays when they are written. The system supports both plain logs and logs as array.

### 2.4.0
* now the `Assets` plugin is used.

## 2.3 branch
### 2.3.0
* now the `Thumbs` plugin is used.

## 2.2 branch
### 2.2.4
* you can create database backups.

### 2.2.3
* added DatabaseBackup plugin;
* fixed tmp viewer and tmp cleaner;
* updated to CakePHP 3.2.

### 2.2.2
* fixed bug in "album" and "posts categories" widgets;
* widgets now use a common view. Rewritten the code of all widgets;
* you can specify the minimum font, the maximum font and the tag prefix for the "popular tags" widget.

### 2.2.1
* added index for tags;
* add the button to clear all temporary files with a single command;
* fixed a major bug for tags. The hyphen is no longer accepted;
* fixed a bug when editing tags.

### 2.2.0
* you can now edit a photo or a banner immediately after it has been uploaded;
* added "popular tags" widget;
* rewritten the log viewer. Now log files are parsed, with style;
* added BBCode examples;
* you can add post tags as keywords meta-tag;
* added support for the "theme color" (the toolbar color for some mobile browser);
* the favicon is automatically added to the layout. No need to manually add;
* deleted ExceptionRenderer class and errors templates and layout. From now, errors will be managed only by the app.

## 2.1 branch
### 2.1.9
* banners have "created" and "modified" fields. Photos have "modified" field;
* fixed bug for cookies policy functionality;
* fixed bug, now the preview image of the post is displayed correctly in RSS;
* added some utility links on the footer;
* added routes for "posts of today" and "posts of yesterday";
* improved View classes;
* the code for backend menus and the frontend widgets has been rewritten;
* backend topbar and backend menus now use cache.

### 2.1.8
* added the cookies policy functionality;
* tags use space instead of the hyphen;
* you can use static pages from plugins. The code for static pages has been rewritten;
* now photos have the "created" date and are ordered using that;
* support for some BBCode;
* with the "<!-- read-more -->" tag, you can indicate manually where to truncate a text;
* added Facebook's tag.

### 2.1.7
* an exception is now properly thrown when a record is not found;
* tags can be 30 characters long;
* fixed bug. Now managers can access "system" menu.

### 2.1.6
* the User shell can now list users and user groups;
* added "latest photos" widget;
* added support for Ajax requests.

### 2.1.5
* fixed a lot of strings and translations.

### 2.1.4
* improved related posts. Now you can also show images;
* added the User shell, to manage users;
* added backward compatibility for old URLs;
* fixed a serious bug for static pages.

### 2.1.3
* added related posts for each post;
* now you can list and edit tags and lists posts by tags;
* improved the system checkup;
* now assets are automatically generated when required;
* fixed bug for sorting some tables;
* improved queries for filters;
* fixed some bugs.

### 2.1.2
* fixed a serious bug when trying to re-sort the results of paginated records;
* filter forms are automatically hidden;
* fixed a bug with forms on Firefox;
* the integration with KCFinder has been improved. Now all directories are read automatically;
* small improvements for display on mobile devices.

### 2.1.1-RC3
* in the admin panel, some views have been linked together;
* tags can contain the dash;
* added page options, including Shareaholic;
* increased the limit for uploading images to 10MB;
* added the installer console;
* now you can use layouts from application or layouts with different names;
* fixed bug in the display of tags in the frontend;
* fixed the title of some actions;
* fixed bug filtering users by group.

### 2.1.0-RC2
* now you can add tags to posts;
* now you can list posts by date;
* added support for Shareaholic;
* jQuery-cookie, Fancybox and KCFinder are installed via Composer;
* improved the logs management;
* added error layout and templates;
* fixed small bugs.

## 2.0 branch
### 2.0.1-RC1
* fixed bug, now the cache is flushed automatically, if there's a post-dated post to be published;
* fixed bug on login with cookie;
* fixed permissions;
* it automatically adds the meta tag for RSS resources;
* the backend menus are generated fully automatically. You no longer need any configuration;
* now you can choise which post details display using the configuration file;
* now you can set the timezone using the configuration file;
* fixed bug with the posts date.

### 2.0.0-alpha
* all the code has been completely rewritten for CakePHP 3.x. Several optimizations have been applied;
* uploading/adding files (for example, banners and photos) is much simplified and is optimized;
* the application can now rewrite the cache configuration;
* engine and configuration for widgets have been simplified;
* the plugin accesses easier configuration;
* every layout has an optimized copy of Bootstrap;
* the backend makes greater use of cache;
* updated Bootstrap to 3.3.5.

# 1.x branch
## 1.2 branch
### 1.2.3
* added the "albums" widget;
* improved the code for widgets. Widgets call some methods to retrieve data;
* improved the system checkup, now it displays the directories path.

### 1.2.2
* added a filter form for banners, pages, posts and users;
* added a contact form;
* pending users may require the activation email is sent again;
* you can pass options to widgets;
* some widgets accept the `limit` option, which indicates the number of records to show.

### 1.2.1
* full support for reCAPTCHA. It's used for signup and to reset passwords;
* users can signup. You can set up as an account should be activated;
* users can reset their own password;
* an email is sent when the user changes his password;
* administrators can manually activate accounts;
* the configuration has been improved.

### 1.2.0
* added the log viewer and the changelogs viewer;
* fixed bug on the backend topbar. The topbar is entirely shown only on mobile devices. Added the sidebar;
* widgets are hidden on the pages that contain the same information or the same data;
* checks if the latest search has been executed out of the minimum interval;
* shows the version of Apache and PHP;
* KCFinder permissions are based on MeCms users permissions;
* many buttons are disabled after the click, to prevent some actions are performed repeatedly;
* usernames and the name of user groups cannot be changed. Improved permissions about users and user groups;
* added the changelog file.