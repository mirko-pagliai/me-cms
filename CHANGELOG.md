# 2.x branch
## 2.16 branch
### 2.16.1
* added `AppTable::beforeSave()`. It checks if the `created` property is an
    instance of `Time`;
* improved `MeCms\Controller\Admin\LogController`, added `_read()` method and
    removed the `viewSerialized` action (the `view` action can use the 
    `view_as_serialized` template);
* added `MeCms\Model\Table\UsersTable\beforeMarshal()` method;
* added `MeCms\Controller\Traits\CheckLastSearchTrait` trait;
* added `MeCms\Controller\Traits\DownloadTrait` trait;
* added `MeCms\Model\Entity\Traits\PreviewAccessorTrait` trait;
* added `MeCms\Model\Table\Traits\NextToBePublishedTrait` trait;
* the preview image for pages and posts always contains a full url;
* global function `firstImageFromText()` renamed as `firstImage()`;
* added tests for virtual fields.

### 2.16.0
* it uses `CollectionInterface::first()` instead of the global `firstValue()`;
* added `StaticPage::slug()` method. Renamed `StaticPage::_getPaths()` as
    `StaticPage::paths()`;
* renamed `/ContactForm.php` as `ContactUsForm` and `ContactFormMailer` as
    `ContactUsMailer`. This creates less confusion with the classes names;
* simplified the name of some methods of the `BaseUpdateShell` class.

## 2.15 branch
### 2.15.4
* improved code for `BaseUpdateShell` class.

### 2.15.3
* fixed code for `BackupForm` class. Added `_getBackupExportInstance()` method;
* fixed code and a little bug for `ContactForm` and `ContactFormMailer` classes;
* fixed code for `UserMailer` class;
* added tests for all `Form` classes;
* added tests for all `Mailer` classes.

### 2.15.2
* added `beforeMarshal()` method do `PostsTable` class. Removed
    `buildTagsForRequestData()`;
* added `TagValidatorTrait` class. It provides some methods shared by the
    validation classes;
* added `isBanned()` and `isOffline()` methods to the `AppController` class;
* fixed some little bugs for validation tags as string related to posts;
* fixed bug, the tags of 3 characters were not accepted;
* added tests for `AppController` class.

### 2.15.1
* fixed some minor bugs for `Users` validation and added tests;
* fixed `PostsTable::getRelated()` method and added tests;
* some minor improvements for table classes;
* updated for CakePHP 3.4.

### 2.15.0
* the layout is exclusively controlled by CakePHP. So, to override the layout
    provided by MeCms, you have to use the 
    `src/Template/Plugin/MeCms/Layout/default.ctp` file;
* the cells that act as widgets now have "Widgets" in the name, for class files
    and the template directory;
* added the `HtmlWidgetCell` class, with `display()` method. This method only
    renders a template file;
* the `BaseView` class has been renamed as `View`. This creates less confusion;
* renamed `MECMS` as `ME_CMS` constant.

## 2.14 branch
### 2.14.16
* the whole of the widget code has been rewritten and improved, making it more
    uniform and consistent;
* improved `AppView` and `WidgetHelper` classes;
* added tests for `AdminView`, `AppView`, `BaseView` and `WidgetHelper` classes;
* added tests for all cell classes.

### 2.14.15
* fixed (perhaps forever...) bug for sorting records in the admin panel;
* now the password can be shown/hidden even adding/editing a user;
* `_runOtherPlugins()` from `InstallShell` renamed as `runFromOtherPlugins` and
    now it's a public shell which can be run from the shell.

### 2.14.14
* the "last logins" table now shows the browser version and links to track the
    IP addresses;
* renamed the `LoginLogger` class as `LoginRecorder`. The class has been
    completely rewritten and several bugs have been fixed;
* fixed bugs and improved code for the `LoginLogger` class;
* improved the `SerializedLog` class;
* added tests for `LoginRecorder` and `SerializedLog` classes.

### 2.14.13
* fixed bug for sorting records in the admin panel;
* fixed bugs and improved code for fields that must be unique;
* improved some validation rules;
* added tests for `TreeBehavior` class;
* added tests for all validation classes.

### 2.14.12
* now the `PhotosAlbum` entity has the `path` virtual field;
* `getList()` and `getTreeList()` methods moved to `AppModel` class. So now
    each model has these methods;
* added `findActive()` method for `TagsTable`;
* improved `StaticPage` class;
* fixed a serious bug in the association of tokens with users;
* fixed a serious bug for the `AppTable::isOwnedBy()` method;
* fixed the `.htaccess` for KCFinder to work also with PHP 7;
* fixed bug for validator for pages categories;
* fixed bug for pages, now the next page to be published is set correctly;
* fixed bug for `BannersPositionsTable::getList()` method;
* fixed a little bug on tag slugs. Slug are now lower case;
* added `firstImageFromText()` global function;
* added tests for `Sitemap`, `SitemapBuilder` and `StaticPage` classes;
* added tests for all entity and all tables classes.

### 2.14.11
* fixed a several bug for `UserShell::add()` method. The method has been
    generally improved;
* added a button to delete all backup files;
* subcommand `installPackages` provided by `InstallShell` is no longer
    available. Instead, use suggested packages by Composer;
* added the `ADMIN_PREFIX` constant;
* updated for MeTools 2.11.1;
* added tests for `AuthComponent`, `AuthHelper`, `BaseUpdateShell`,
    `KcFinderComponent`, `InstallShell`, `MenuHelper`, `Plugin` and `UserShell`
    classes;
* added tests for global functions and request detectors.

### 2.14.10
* updated for MeTools 2.11.0.

### 2.14.9
* added `cakephp-tokens` plugin for handling tokens.

### 2.14.8
* added `cakephp-mysql-backup` plugin instead of `DatabaseBackup` plugin;
* fixed for MeTools 2.10.4.

### 2.14.7
* you can choose whether the banners should be displayed with a thumbnail or
    not. This allows you to view animated gif unchanged;
* banners have `www` virtual field.

### 2.14.6
* to generate thumbnails, uses the `fit()` method instead of `crop()`.
* fixed bug for Font Awesome on admin and login layouts.

### 2.14.5
* added `cakephp-thumber` plugin instead of `thumbs` plugin;
* updated for DatabaseBackup 1.1.4.

### 2.14.4
* fixed bootstrap code, also for tests;
* added `Gourmet/CommonMark` plugin;
* updated for CakePHP 3.3.4.

### 2.14.3
* added `WyriHaximus/MinifyHtml` plugin;
* updated for Assets 1.1.0.

### 2.14.2
* fixed little bug.

### 2.14.1
* added `all` and `latest` subcommands to the `update` shell. Added
    `_getAllUpdateMethods()` to `BaseUpdateShell` class;
* some view elements (eg. topbars, sidebars, footers) are cached only if
    debugging is disabled;
* fixed little bug on css code for photos block;
* updated for MeTools 2.10.1.

### 2.14.0
* added a button to show/hide passwords;
* `MenuBuilderHelper` class has been completely rewritten and now provides
    `generate()`, `getMenuMethods()`, `renderAsCollapse()` and
    `renderAsDropdown()` methods;
* added tests for `MenuBuilderHelper` class;
* the `name` field of the `banners_positions` table has been renames as `title`;
* updated for MeTools 2.10.0.

## 2.13 branch
### 2.13.2
* fixed bug adding tags on admin layout.

### 2.13.1
* added `is('add')`, `is('delete')`, `is('edit')`, `is('index')` and
    `is('view')` detectors;
* the admin sidebar is cached only if debugging is disabled;
* improved admin routes. They are automatically handled by CakePHP;
* fixed bug, posts were also cut ("read more") in the preview;
* fixed other code for CakePHP Code Sniffer;
* updated for MeTools 2.9.0.

### 2.13.0
* improved the `KcFinderComponent`;
* the system checkup checks for login logs;
* `isOffline()` is now a detector (`$this->request->is('offline')`);
* removed auto-links for posts and pages. It caused too many problems;
* updated for CakePHP 3.3;
* improved routes. Now `DashedRoute` is the default route class;
* fixed bug for `SerializedLog` when log file doesn't exist;
* checks if there are already routes with the same name, before declaring new;
* fixed code for CakePHP Code Sniffer;
* fixed several typos for page views.

## 2.12 branch
### 2.12.5
* removed auto-links for posts and pages. It caused too many problems.

### 2.12.4
* filter forms can now use records ID;
* fixed bug rendering admin views.

### 2.12.3
* for each user, the login log is saved. Each user can view his logs.

### 2.12.2
* you can download/delete logs from the log view;
* admin indexes display ID for all elements;
* updated Bootstrap to 3.3.7.

### 2.12.1
* added pagination for photos;
* improved photos index as grid;
* now you can empty logs from the logs index;
* fixed bug on photos filter (for albums).

### 2.12.0
* added autolinks for posts and pages;
* the view will automatically choose which layout is to be used;
* splitted some frontend (default) css files;
* from now, "frontend" is "default" and "backend" is "admin".

## 2.11 branch
### 2.11.0
* added breadcrumb;
* pages index has been removed;
* now you can hide the userbar.

## 2.10 branch
### 2.10.1
* now pages have categories, with category widget;
* now the install shell can create user groups;
* added links on userbar for posts categories and tags;
* added `userbar()` method for `AppViews`. This simplifies the code to add 
	elements to the userbar;
* added links to upload banners and photos from indexes of banner positions 
	and photo albums;
* routes have been split into multiple files; 
* fixed bug for "only published" field on filter forms;
* fixed cache code for widgets;
* fixed bug for rotated logs;
* fixed messages pluralized;
* strings to be translated were defined and simplified.

### 2.10.0
* now you can disable (published/unpublished) each photo;
* the code to list posts by date has been greatly improved and simplified;
* added preview for photos and albums;
* added userbar for albums;
* the banned ip list has been moved to a dedicated file 
	(`Config/banned_ip.php`);
* methods of the `UpdateShell` class are automatically detected and added to 
	the parser;
* now the installer also runs the installer of other plugins;
* tags are always sorted alphabetically;
* fixed bug. Now if you disable a photo album all its photos become disabled;
* fixed bug for filter forms;
* CakePHP will automatically set the locale based on the current user;
* added common templates for all normal views;
* `statics` action renamed as `index_statics`.

## 2.9 branch
### 2.9.1
* fixed serious bug on the created date of objects when editing.

### 2.9.0
* added action to list posts by month (year and month);
* added "posts by month" widget;
* `Photos::albums`, `Posts::categories` and `Posts::categories` widgets can 
	now render as form (default) or list;
* `PostsTags::popular` widget can now render as cloud (default), form or list;
* added common templates for all admin views;
* fixed titles for some admin templates;
* improved view classes;
* `index_by_date` action renamed as `index_by_day`.
* you can set a custom class for widgets.

### 2.8.1
* pages now have tags for Facebook;
* pages now have the `preview` property, just as for posts;
* improved login layout for extra small devices;
* added specific methods for previews. This improves the code.

### 2.8.0
* now uses the `UploaderComponent`;
* improved the `AuthHelper`. Now it has its own methods and this makes user 
	data safer;
* improved the `AuthComponent`.

## 2.7 branch
### 2.7.4
* improved the uploader.

### 2.7.3
* added some buttons for backend;
* fixed links for banners and photos as grid.

### 2.7.2
* tags have been moved below posts;
* fixed bug for restoring databases. The cache is properly cleaned;
* fixed titles.

### 2.7.1
* fixed bug for uploader;
* fixed bug for paginate of banners and photos;
* fixed bug for filter forms.

### 2.7.0
* new uploader (Dropzone);
* you can now check the mime type of uploaded files;
* updated js-cookie via Composer.

## 2.6 branch
### 2.6.4
* you can show banners and photos as list or as grid;
* you can filter banners and photos by created date;
* you can download banners and photos.

### 2.6.3
* fixed bug ordering posts and pages.

### 2.6.2
* improved the code to check the cache validity. Removed 
	`checkIfCacheIsValid()` and `getNextToBePublished()` methods;
* improved code for posts and pages that are drafts or to be published in the 
	future;
* fixed bug on MenuBuilder helper;
* updated Facebook's tags.

### 2.6.1
* added userbar for frontend. It allows to edit an delete posts, pages and 
	photos.

### 2.6.0
* you can now set the expiration of sitemap;
* now the sitemap uses the cache and handles `lastmod` and `priority` values;
* fixed bug for "contact form" and added the `ContactFormMailer` class;
* fixed bug for cleaning temporary files;
* you can now delete the sitemap;
* now all objects have "created" and "modified" fields;
* added the `Plugin` class, which improves the code;
* rewrote the code to generate the backend menus;
* fixed bug for `UpdateShell`;
* added `isBanned()` detector. Removed `SecurityComponent` class.

## 2.5 branch
### 2.5.1
* added functions to generate the site sitemap;
* now the photo urls contain the album slug.

### 2.5.0
* fixed "view" action for photos albums;
* added log for some users actions;
* the code for loading the configuration files has been optimized;
* fixed a lot of little bugs and codes.

## 2.4 branch
### 2.4.7
* improved code for banners and photos;
* fixed bug for localized static pages;
* fixed shell output style;
* fixed a lot of little bugs and codes.

### 2.4.6
* fixed some bugs.

### 2.4.5
* logged users can view future posts, future pages and drafts;
* the status of a record is properly shown in the admin panel, including 
	publication in the future;
* added filter for tags;
* added patterns table for database backups;
* now you can restore database backups;
* now you can download and deletes logs.

### 2.4.4
* fixed a major bug for the menu builder;
* fixed bug on log advanced viewer.

### 2.4.3
* added `Logs` controller and templates. Log management has improved. The log 
	can be displayed as plain or serialized;
* added "who is" and "map" functionalities for IP addresses on logs;
* each time that is called, the `SerializedLog` adapter writes the normal log 
	and a serialized copy of the log.  

### 2.4.2
* fixed bug on "popular tags" widget.

### 2.4.1
* logs are turned into arrays when they are written. The system supports both 
	plain logs and logs as array.

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
* you can specify the minimum font, the maximum font and the tag prefix for 
	the "popular tags" widget.

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
* added support for the "theme color" (the toolbar color for some mobile 
	browser);
* the favicon is automatically added to the layout. No need to manually add;
* deleted ExceptionRenderer class and errors templates and layout. From now, 
	errors will be managed only by the app.

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
* you can use static pages from plugins. The code for static pages has been 
	rewritten;
* now photos have the "created" date and are ordered using that;
* support for some BBCode;
* with the "<!-- read-more -->" tag, you can indicate manually where to 
	truncate a text;
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
* the integration with KCFinder has been improved. Now all directories are 
	read automatically;
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
* fixed bug, now the cache is flushed automatically, if there's a post-dated 
	post to be published;
* fixed bug on login with cookie;
* fixed permissions;
* it automatically adds the meta tag for RSS resources;
* the backend menus are generated fully automatically. You no longer need any 
	configuration;
* now you can choise which post details display using the configuration file;
* now you can set the timezone using the configuration file;
* fixed bug with the posts date.

### 2.0.0-alpha
* all the code has been completely rewritten for CakePHP 3.x. Several 
	optimizations have been applied;
* uploading/adding files (for example, banners and photos) is much simplified 
	and is optimized;
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
* some widgets accept the `limit` option, which indicates the number of 
	records to show.

### 1.2.1
* full support for reCAPTCHA. It's used for signup and to reset passwords;
* users can signup. You can set up as an account should be activated;
* users can reset their own password;
* an email is sent when the user changes his password;
* administrators can manually activate accounts;
* the configuration has been improved.

### 1.2.0
* added the log viewer and the changelogs viewer;
* fixed bug on the backend topbar. The topbar is entirely shown only on 
	mobile devices. Added the sidebar;
* widgets are hidden on the pages that contain the same information or the 
	same data;
* checks if the latest search has been executed out of the minimum interval;
* shows the version of Apache and PHP;
* KCFinder permissions are based on MeCms users permissions;
* many buttons are disabled after the click, to prevent some actions are 
	performed repeatedly;
* usernames and the name of user groups cannot be changed. Improved 
	permissions about users and user groups;
* added the changelog file.