# 2.x branch
## 2.30 branch
### 2.30.1
* if Recaptcha is not used, an exception is no longer throwned when configuration
    keys are missing;
* added `CreateSamplePostCommand`, this creates a sample post during installation;
* fixed the `MenuBuilderHelper` and the admin sidebar, menus are now better displayed;
* a major improvement of the descriptions, thanks to `phpstan`;
* updated for `php-tools` 1.5.1, `me-tools` 2.20.1 and `phpuseragentparser` 1.

### 2.30.0
* starting from this release, it no longer contains the code for banners and photos,
    which has been split into [me-cms-banners](//github.com/mirko-pagliai/me-cms-banners)
    and [me-cms-photos](//github.com/mirko-pagliai/me-cms-photos) suggested plugins;
* `MeCms\Controller\Admin\AppController` class: the last valid referer is saved
    in session by the `beforeFilter()` event and no longer by the `beforeRender()`
    event (which has been removed). The `referer()` method was removed (this
    will directly call the method provided by CakePHP) and the
    `redirectMatchingReferer()` method was added instead;
* the topbar element will use the `TopbarHelper` from APP to build links, if
    that helper exists. Otherwise it will use the helper provided by MeCms, with
    the helper of any other plugin;
* the sitemap classes have been moved into the `MeCms\Utility\Sitemap` namespace.
    Added the `SitemapBase` abstract class, that provides the `parse()` method
    and which now needs to be extended by the `Sitemap` class;
* `Plugin::console()` is now able to auto-discover commands. The
    `me_cms.fix_elfinder` command is now `me_cms.fix_el_finder`;
* `AppView::userbar()` has been removed;
* ready for `phpunit` 9.0.

## 2.29 branch
### 2.29.5
* `AppView::userbar()` is deprecated. Use instead `addToUserbar()`;
* the `CopyConfigCommand` now uses the `CONFIG_FILES` configuration value,
    defined in the bootstrap;
* fixed bug for virtual fields for posts and pages;
* fixed bug in the formatting of some dates when editing posts and pages;
* updated for `cakephp-recaptcha-mailhide` `1.4.6`; no longer needs extra repositories;
* removed `js-cookie` as package, added static asset;
* fixed little bug for the fronend topbar;
* fixed all template files for them to use the `get()` method to access properties.

### 2.29.4
* `SitemapBuilder::getMethods()` returns a collection and it's now public;
* `StaticPage::all()` returns a collection;
* `AppView::userbar()` always returns the userbar as array;
* `Plugin::all()` method now takes the `mecms_core` option, which excludes
    plugins automatically requested by MeCms;
* fixed `TestSuite` classes, `MenuBuilderHelper` and some template files, improved
    to use with other plugins;
* fixed old bug on loading theme plugins;
* fixed little bug for the `description` field for `Banner` and `Photo` entities;
* fixed composer script to extract POT files;
* updated for `php-tools` 1.4.5;
* removed redundant code in routes;
* added `phpstan`, so fixed some code and descriptions.

### 2.29.3
* fixed a bug for `FixElFinderCommand`. Now the `elfinder-cke.html` file works.

### 2.29.2
* fixed a bug for `FixElFinderCommand`. Now the `elfinder-cke.html` file is also
    created;
* updated for `me-tools` 2.19.7 and `php-tools` 1.4.1.

### 2.29.1
* added `AppController::setPaging()` method and updated controllers. This solves
    a serious bug in pagination;
* updated some dependencies.

### 2.29.0
* `KcFinder` has been completely replaced with `ElFinder` and all its classes
    have been removed. `\MeCms\Command\Install\FixElFinderCommand` and
    `\MeCms\Utility\Checkups\ElFinder` classes have been added;
* uses and suggests `npm-asset/fancyapps-fancybox` [github](https://github.com/fancyapps/fancybox)
    instead of `newerton/fancy-box`.
* admin "checkup" function and all its classes have been removed.

## 2.28 branch
### 2.28.1
* no longer loads the cache configuration from the application. To set custom
    cache parameters, use the application bootstrap;
* prevents the plugins bootstrap from loading multiple times;
* `Apache::version()` renamed as `Apache::getVersion()`, `Plugin::versions()`
    renamed as `Plugin::getVersions()`;
* removed useless `AbstractCheckup` class;
* added `PHP::getVersion()`;
* no longer forces debug for localhost. Instead, use your `app_local.php` file;
* updated `Command` tests for `cakephp` 4.0.5.

### 2.28.0
* updated for `cakephp` 4 and `phpunit` 8;
* added `MenuHelperTestCase`.

## 2.27 branch
### 2.27.7
* fixed title for admin pages;
* little fixes.

### 2.27.6
* fixed little bug for `Icon` helper;
* fixed little bug for static pages.

### 2.27.5
* `\MeCms\Controller\Admin\AppController::referer()`, unlike the original method,
    can return the `index` action of the same controller (if it has been
    indicated as the `$default` parameter), preserving also the query string;
* replaced the `Validator::allowEmpty()` method that will be deprecated.

### 2.27.4
* added Fancybox photo preview in the admin panel;
* fixed bug, the album view now correctly shows the title and the number of
    photos contained in each album;
* fixed bug for changelogs reader;
* fixed little bug for the admin sidebar.

### 2.27.3
* updated for `cakephp-thumber` `1.8.0`.

### 2.27.2
* `PostsTable::getRelated()` returns a `Collection` of entities;
* `GetPreviewsFromTextTrait::getPreviews()` returns a `Collection` of entities;
* added `url` virtual field for `Page`, `PagesCategory`, `Photo`, `PhotosAlbum`,
    `Post`, `PostsCategory` and `Tag` entities;
* virtual fields throw an exception if the necessary properties are missing;
* wide simplification of many template files;
* uses `dereuromark/cakephp-feed` to generate RSS;
* updated for `me-tools` `2.18.14`.

### 2.27.1
* fixed a serious bug for `beforeFilter()` methods. The bug prevented some
    redirects;
* added `AppController::getPaging()` method.
* added `\MeCms\Controller\AppController::__get()` method. In addition to the
    method provided by CakePHP, it can also auto-load the associated tables;
* added `\MeCms\ORM\Query` class. The tables that extend
    `\MeCms\Model\Table\AppModel` will use this query class as default. This
    class overrides the `cache()` method and uses the `getCacheName()` table
    method to get the default name of the cache config to use, if that method
    exists.

### 2.27.0
* requires at least PHP `7.0`, `phpunit` `6.0` and `CakePHP` `3.8`. Added tests
    for lowest dependencies;
* all the code has been made compatible with Postgres and Sqlite drivers.
    The fixtures code has been simplified. Added tests for Postgres driver.
* pages and posts can display the last modified date (as default). Added entries
    in the configuration file;
* added `\MeCms\Controller\Admin\AppController` class. All admin controllers now
    extend this class;
* the `MenuBuilderHelper::renderAsCollapse()` method takes the `$idContainer`
    parameter and is now able to generate all the necessary code without
    javascript code. All methods from `MenuHelper` now return an array with a
    fourth value (an array with the controllers handled by that menu);
* largely simplified the code for `PostsWidgetsCell::months()` method;
* fixed bug for `PhotosAlbumsController::index()`. Now the album photos are
    randomly ordered after being retrieved from the cache;
* for `StaticPage` class, `getSlug()` is now public, `title()` becomes
    `getTitle()`, `getAllPaths()` becomes as `getPaths()` and is now public;
* javascript functions are now "camelCase": `tag_exist()` becomes `tagExists()`,
    `add_tags()` becomes `addTags()`, `remove_tag()` becomes `removeTag()` and
    `update_output_text()` becomes `updateOutputText()`;
* many small fixes;
* updated for `me-tools` `2.18.13`.

## 2.26 branch
### 2.26.7
* added a basic style sheet for printing;
* fixed bug for some no existing photos;
* improved code for `PostsTable::queryForRelated()` method;
* `add` and` edit` template files for `Posts`, `PostsCategories`, ` Pages` and
    `PagesCategories` have been merged into the `form` templates respectively;
* added `admin/priority-badge` template element;
* updated for `php-tools` `1.2.8` and `me-tools` `2.18.11`.

### 2.26.6
* added `AppTable::deleteAll()` method. This automatically clears the cache
    associated with the table, when possible;
* added `AppTable::clearCache()`;
* fixed the `StaticPage` utility. The `getPath()` method becomes `getPaths()` and
    now returns an array with all possible paths, even if they do not exist;
* uses `getRequest()`/`setRequest()` methods instead of `$request` property
    whenever possible;
* removed useless `AppController::isOffline()` method;
* removed `AppTable::beforeSave()`, added `AppTable::beforeMarshal()`;
* `StaticPage::getAppPath()` and `StaticPage::getPluginPath()` been replaced by
    the `StaticPage::getPath()` method;
* improved and fixed the `AuthHelper`. Now it is loaded from the view (instead of
    from the controller) requires an array with the `user` key as configuration;
* improved and fixed the `KcFinderComponent`;
* improved validation rules. Removed some useless validation methods;
* uses `league/commonmark` package instead of `gourmet/common-mark`;
* added `MeCms\AuthTrait`. It provides some methods for classes that need to
    verify the data of the logged in user;
* added `BannerAndPhotoValidator`, `PageAndPostValidator` and `CategoryValidator`
    abstract classes;
* updated for `php-tools` `1.2.7`.

### 2.26.5
* it uses the `cakephp-stop-spam` package to detect spammers;
* the `ContactUs` form checks if the email address used was reported as a spammer;
* `AppController::isBanned()` method renamed as `isSpammer()`;
* updated for `me-tools` 2.18.7;
* added `MeCms\ORM\PostAndPageEntity` abstract class;
* added `MeCms\TestSuite\BannersAndPhotosAdminControllerTestCase` abstract class;
* `PostsAndPagesTables` class moved to `MeCms\ORM` namespace;
* removed useless `ControllerTestCase::assertHasComponent()` method;
* updated for `php-tools` 1.2.5.

### 2.26.4
* fixed bug for login cookies;
* fixed a little bug for pages slug;
* improved and updated cookie writing/reading;
* `ControllerTestCase::assertHasComponent()` can take an array as argument;
* it suggest the `mirko-pagliai/me-cms-link-scanner` package;
* updated for `php-tools` 1.2.

### 2.26.3
* added the Disqus comment system for pages and posts. By default, comments are
    enabled for posts;
* added `enable_comments` field to `Pages` and `Posts` tables.
    Added `VersionUpdatesCommand::addEnableCommentsField()` method;
* fixed bug for static pages with locales.

### 2.26.2
* it is now possible to disable the sitemap for some content, using the MeCms
    configuration file;
* fixed bug for tables width on posts and pages;
* added `VersionUpdatesCommand::deleteOldDirectories()` method;
* updated for `php-tools` 1.1.12.

### 2.26.1
* the length of the tags has been increased to 40 characters;
* added `VersionUpdatesCommand`, that performs some updates to the database or
    files needed for versioning. `RunAllCommand` executes this command;
* improved tag validation for posts. Now errors on multiple tags are shown at the
    same time and the tag name that generated the error is shown;
* `PostValidator::validTagsLength()` and `PostValidator::validTagsChars()` have
    been replaced with the `PostValidator::validTags()` method. `TagValidatorTrait`
    has been removed and its methods has been moved into `TagValidor` class;
* fixed a bug when loading plugins, when the `Asset` plugin does not exist.

### 2.26.0
* `InstallShell` has been replaced with console commands. Every method of the
    previous class is now a `MeCms\Command\Install` class;
* `UserShell` has been replaced with console commands. Every method of the
    previous class is now a `MeCms\Command` class;
* fixed bug for `MeCms\Controller\Admin\PostsController::isAuthorized()` method;
* the `$cache` property for tables is now protected. Added `AppTable::getCacheName()`
    method to get the cache configuration name used by the table. It can also
    returns the names for the associated tables;
* removed `SerializedLog` class. Use instead `EntityFileLog\Log\Engine\EntityFileLog`;
* `IntegrationTestCase` has been removed and its methods have been moved to
    `ControllerTestCase`. You can also use `IntegrationTestTrait` provided by MeTools;
* `ComponentTestCase`, `ConsoleIntegrationTestCase` and `HelperTestCase` have
    been removed, use instead classes provided by MeTools;
* added tests for Windows. Fixed bug in solving slug static pages on Windows;
* removed `ME_CMS` constants. It no longer uses also `ASSETS`,
    `DATABASE_BACKUP`, `ME_CMS_CACHE`, `ME_TOOLS`, `RECAPTCHA_MAILHIDE` and
    `THUMBER` constants;
* updated for CakePHP 3.7.1 and fixed all deprecations;
* updated for `php-tools` 1.1.10 and `me-tools` 2.18.1.

## 2.25 branch
### 2.25.4
* fixed bug for `Photo::_getPath()` and `Photo::_getPreview()` methods;
* `Mailer::getEmailInstance()` is now public;
* added `CellTestCase`, `ControllerTestCase`, `EntityTestCase`,
    `PostAndPageEntityTestCase` and `TableTestCase` abstract classes;
* fixed `PostsAndPagesTablesTestCase` and `ValidationTestCase` abstract classes;
* all the tests have been improved.

### 2.25.3
* photo albums are now indexed by creation date. The creation date is shown in
    the admin panel;
* fixed bug in indexing photo albums on mobile devices;
* updated for me-tools 2.17.4.

### 2.25.2
* optimized `MeCms\Controller\PagesCategoriesController::view()`, now it
    executes a single query and pages are contained in the category entity;
* simplified a lot of controllers code.

### 2.25.1
* `AuthComponent` now uses the `auth` find method (`UsersTable::findAuth()`);
* fixed bug on required data check for mailer classes;
* uses the `cakephp-entity-file-log` package, so now `SerializedLog` extends
    `EntityFileLog\Log\Engine\EntityFileLog`. In a future release
    `SerializedLog` will be removed;
* updated for `php-tools` 1.1.

### 2.25.0
* added  `MeCms\Form\Form` class. This solves issue
    [12024](https://github.com/cakephp/cakephp/issues/12024) and allows to
    upgrade CakePHP to a version higher than 3.6.1.
* fixed `LoginRecorderComponent` class, some methods have changed their name;
* fixed `SerializedLog` class`.
* `mirko-pagliai/php-tools` package replaces `mirko-pagliai/serialized-array`;
* some fixes for Font Awesome icons.

## 2.24 branch
### 2.24.0
* `AppValidator` moved from `MeCms\Model\Validation` to `MeCms\Validation`;
* `SerializedLog::getLogAsObject()` method returns a log as `Entity`;
* `LoginRecorderComponent::write()` method writes logins as `Entity`;
* `LogsController::index()` action sets a collection of entities;
* some fixes for PHP 7.2;
* updated for CakePHP 3.6, php-tools 1.0.9, Font Awesome 5.1, me-tools 2.17 and
    cakephp-database-backup 2.5.

## 2.23 branch
### 2.23.1
* now it uses the `mirko-pagliai/php-tools` package. This also replaces
    `mirko-pagliai/reflection`;
* updated for cakephp-thumber 1.4 and me-tools 2.16.8.

### 2.23.0
* added the `UpdateShell`. This shell provides subcommands to update your
    application;
* `GetPreviewFromTextTrait` class renamed as `GetPreviewsFromTextTrait` and
    `firstImage()` and `getPreview()` methods have been replaced by
    `extractImages()` and `getPreviews()` methods. The `getPreviews()` method
    now returns an array of `Entity`. This allows you to get all the previews,
    not just the first one;
* `GetPreviewsFromTextTrait::getPreviews()` returns an array of `Entity` with
    `url`, `width` and `height` properties;
* `MeCms\Model\Entity\Photo::_getPreview()` method returns an `Entity` with
    `url`, `width` and `height` properties;
* added `PostsAndPagesTables` and `PostsAndPagesTablesTestCase` classes, with
    methods and tests common to `PagesTable` and `PostsTable` classes;
* added `\MeCms\Database\Type\JsonEntityType`, to convert an array of `Entity`
    as json data;
* added `MeCms\Model\Table\PostsTable::queryForRelated()` method;
* fixed bug, some previews were not correctly displayed;
* fixed a bug in the common view of the userbar;
* updated for CakePHP 3.5.13.

## 2.22 branch
### 2.22.8
* added `MeCms\Utility\Checkup` class and some classes under the
    `MeCms\Utility\Checkups` namespace;
* the system checkup checks the version of KCFinder and if the `.htaccess` file
    exists;
* fixed bug in measuring the elements height of the admin panel via javascript;
* added `PostsTable::findForIndex()` method;
* fixed a bug for static pages;
* removed `php-simple-html-dom-parser` package. The
    `GetPreviewFromTextTrait::firstImage()` method now uses only DOM functions.

### 2.22.7-RC4
* the backend and the frontend (admin panel) both have the userbar, which now
    uses a common view and the same css;
* fixed a bug that prevented showing the user picture for some actions.

### 2.22.6-RC3
* users can change their picture from the admin panel;
* the user's picture can be shown next to each post. It is also shown in the
    user's profile in the admin panel and in the user bar;
* `User` entity has the `picture` virtual field, which contains the path of the
    user's picture or, alternatively, a default image;
* the admin topbar is no longer cached;
* updated for `cakephp-thumber` 1.3.0;
* updated for MeTools 2.16.5-RC3 and so for Bootstrap 4 beta 3.

### 2.22.5-RC2
* `Page` and `Post` entities have the `plain_text` virtual field. `Photo` entity
    has the `plain_description` virtual field;
* static pages are entities;
* updated for MeTools 2.16.4-RC2.

### 2.22.4-RC1
* when the search function is used, the searched text is highlighted;
* updated for MeTools 2.16.3-RC1.

### 2.22.3-beta
* updated for MeTools 2.16.2-beta;
* updated for Bootstrap 4 beta 2.

### 2.22.2-beta
* fixed little bugs on templates and css rules.

### 2.22.1-beta
* using javascript, it sets the footer to `fixed` position when needed, that is
    when the document body is lower than the window height;
* fixed little bugs on templates;
* fixes on templates and css rules.

### 2.22.0-beta
* a massive improvement of pages and posts templates;
* fixed all templates and layouts for Bootstrap 4;
* fixed `MenuBuilderHelper` for Bootstrap 4;
* added the `post-preview` view element;
* `MenuHelper` class returns menus as arrays, without transforming them into
    html.

## 2.21 branch
### 2.21.1
* added `PhotosAlbum::_getPreview()` method (`preview` virtual field);
* all accessors methods (`_get()` methods) no longer check if the properties
    used are not empty, except those methods that use properties that belong to
    associated models;
* the `bootstrap` file sets the default format used when type converting
    instances of this type to string.

### 2.21.0
* updated for CakePHP 3.5;
* `UserShell::add()` now returns `true`. The created user id is shown through a
    successful message;
* uses `ConsoleIntegrationTestCase`. Console tests have been simplified.

## 2.20 branch
### 2.20.2
* only one bootstrap file is used. Deleted `config/bootstrap_base.php`;
* fixed bug for traslating i18n constants;
* fixed bug for creating thumbnails.

### 2.20.1
* many i18n constants have been added. These make the code cleaner and more
    comprehensible;
* fixed bug on `LoginRecorderComponent`.

### 2.20.0
* added initial schema of the plugin database. Removed `BaseUpdateConsole` and
    `UpdateShell` classes;
* added `getConfigOrFail()` global function;
* added `IntegrationTestCase` and `ValidationTestCase` classes. Removed
    `AuthMethodsTrait` class;
* `firstImage()` is no longer a global function, but a method provided by the
    `GetPreviewFromTextTrait` class;
* removed `LogsMethodsTrait`, moved to MeTools;
* `TagValidatorTrait` moved to `MeCms\Model\Validation\Traits`;
* the MIT license has been applied;
* significantly improved all tests.

## 2.19 branch
### 2.19.2
* fixed bug in the list of active pages for each page category;
* fixed bug for widgets: they do not show anything if there are no records.

### 2.19.1
* added configuration for reCAPTCHA (`config/recaptcha.php`);
* fixed bug for `upload()` methods. Now all errors are handled properly;
* uses `crabstudio/Recaptcha` and `mirko-pagliai/cakephp-recaptcha-mailhide`
    plugins for reCAPTCHA;
* updated for MeTools 2.13;
* updated for dropzone 5.1;
* updated for cakephp-database-backup 2.1.0.

### 2.19.0
* cakephp-mysql-backup has been replaced with cakephp-database-backup, 2.0.0
    version. Now you can send backups via mail;
* `config()` global function becomes `getConfig()` The function accepts a second
    parameter as the default value in case the configuration is empty.

### 2.18.2
* the default directories are created automatically, if they do not exist;
* fixed ajax/json layouts;
* updated for MeTools 2.12.5;
* updated for cakephp-thumber 1.1.0;
* updated for cakephp-mysql-backup 1.0.3.

### 2.18.1
* `BannersController::upload()` and `PhotosController::upload()` methods return
    errors with a json response;
* fixed bug. The view class to be used is set by the
    `AppController::beforeFilter()` method;
* improved/fixed the code of all template files.

### 2.18.0
* the tags index now uses pagination;
* widgets (cells) use collections;
* fixed little bugs and improved code for `BannersController` and
    `PhotosController` admin classes;
* by default, cookies are not encrypted;
* `AppTable::getList()`, `AppTable::getTreeList()` and
    `UsersTable::getActiveList()` now return a `Query` object;
* added tests for all admin classes.

### 2.17.5
* added `GetStartAndEndDateTrait`;
* added `LogsMethodsTrait`.

### 2.17.4
* added `PostsController::getStartAndEndDate()` method;
* removed `PostsController::indexCompatibility()` method;
* `UserController::activateAccount()` becomes `activation()`,
    `UserController::forgotPassword()` becomes `passwordForgot()`,
    `UserController::resetPassword()` becomes `passwordReset()`,
    `UserController::resendActivation()` becomes `activationResend()`,
    `UserMailer::activateAccount()` becomes `activation()`,
    `UserMailer::forgotPassword()` becomes `passwordForgot()`;
* fixed bug on page preview;
* fixed bug: `PostsTable::getRelated()` returns an empty array if there are no
    related post;
* fixed the `Categories` alias for `Pages` and `Posts` tables;
* fixed some little bugs and improved code for `UsersController` class;
* added tests for `PostsCategoriesController`, `PostsController`,
    `PostsTagsController` and `UsersControllerTest` classes.

### 2.17.3
* added `PhotosTable::findPending()` method;
* fixed all `find()` methods;
* removed `active` field from `PhotosAlbums` table and the `preview` action
    from its controller;
* added tests for `PhotosAlbumsController` and `PhotosController` classes.

### 2.17.2
* fixed bug on `firstImage()` global method;
* fixed bug on the sitemap: pending pages, photos and posts are now excluded;
* added `AppTable::findPending()` and `UsersTable::findPending()` methods;
* improved `isAuthorized()` method: any registered user can access public
    functions; only admin and managers can access all admin actions.

### 2.17.1
* fixed bug for `isAuthorized()` method, simple users could not access some
    denied actions as `preview`;
* fixed bug, the `created` field is formatted correctly in `edit` templates;
* improved `StaticPage` class, added `_appPath()` and `_pluginPath()` methods;
* added tests for `BannersController`, `PagesCategoriesController` and
    `PagesController` classes.

### 2.17.0
* fixed bug when using multiple widgets that have the same name;
* added `og:image:width` and `og:image:height` meta tags for previews;
* added `preview` field to pages and posts tables, which contains url, width
    and height of the preview, encoded with json;
* added `preview` virtual field to photos;
* improved `KcFinderComponent`. Now the class has `_getDefaultConfig()` and
    `initialize()` method;
* improved `AuthComponent`. Now the class has the `initialize()` method;
* `BaseUpdateShell` renamed as `BaseUpdateConsole`. This creates less confusion.

## 2.16 branch
### 2.16.1
* managers can delete photos and photos albums;
* fixed bug on redirect from the old address of the "contact us" form;
* fixed bug in getting the active users list. Now it shows the full name;
* fixed bug on `StaticPage::paths()`. Now it returns only existing paths;
* `LoginRecorder` is now a component and returns the correct IP on localhost.
    You must first set the user ID with the `config()` method and the `user`
    value;
* fixed bug https://github.com/cakephp/cakephp/pull/10417;
* added `AppTable::beforeSave()`. It checks if the `created` property is an
    instance of `Time`;
* static pages uses cache. `StaticPage::all()` returns an array of `Entity`;
* improved `MeCms\Controller\Admin\LogController`, added `_read()` method and
    removed the `viewSerialized` action (the `view` action can use the
    `view_as_serialized` template);
* added `MeCms\Model\Table\UsersTable\beforeMarshal()` method;
* added `MeCms\Controller\Traits\CheckLastSearchTrait` trait;
* added `MeCms\Model\Entity\Traits\PreviewAccessorTrait` trait;
* added `MeCms\Model\Table\Traits\IsOwnedByTrait` trait;
* added `MeCms\Model\Table\Traits\NextToBePublishedTrait` trait;
* the preview image for pages and posts always contains a full url;
* global function `firstImageFromText()` renamed as `firstImage()`;
* removed `AppController::_download()` method;
* added tests for `isAuthorized()` and `download()` methods of all admin
    controllers;
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
* renamed `MECMS` as `'MeCms'` constant.

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
