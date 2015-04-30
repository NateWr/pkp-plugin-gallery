Data Schema
===========

This document outlines how data will be stored about the submitted plugins. It is written as a point of departure for discussion as well as an introduction for future contributors.

Table of Contents
-----------------

1. Introduction to WordPress data
2. Schema
  * Post Types
  * Post Statuses
  * Taxonomies
  * Post Meta
  * User Meta
3. Submission and Approval Process
4. Updates

1. Introduction to WordPress data
---------------------------------

WordPress data management is centered around a `post_type` which stores basic data like `title`, `date`, and `content` details. Extra data is then attached to the post as `post_meta` which is stored in a separate database. Posts are grouped under `taxonomies`.

So a `pkp_plugin` post type might have a `_homepage` `post_meta` field attached and the post might be asigned to the `ojs` term in the `software` `taxonomy`. Many taxonomy terms can be assigned to a single post if desired.

While `post_meta` is useful for attaching data, it's use should be restricted to data that doesn't need to be queried against. Lookups by `post_meta` are much slower than `taxonomies`. In general, if a piece of data needs to be queried against, `taxonomies` are preferred as long as grouping makes sense. Of course, there are exceptions.

In addition to the `post` structure, WordPress includes a similar structure for `users` and `user_meta`, which will be used to link plugins to a user entry in the database.

Each `post` is assigned a `post_status`, which ushers posts from `draft` to `publish` status. This should be used for managing the approval workflow.

2. Schema
---------

### Post Types

`pkp_plugin`

`pkp_plugin_release`

Each plugin will generate a `pkp_plugin` post. Each release of the plugin will generate a `pkp_plugin_release` post, which is associated with it's `pkp_plugin` post through the `post_parent` column.

The following data will be stored in the `wp_posts` table alongside each post.

**pkp_plugin**

`name` stored as `post_title`

`product` stored as `post_name`

`summary` stored as `post_excerpt`

`description` stored as `post_content`

`maintainer` user id stored in `post_author`

**pkp_plugin_release**

`version` stored as `post_title`

`release_date` stored as `post_date`

`description` stored as `post_content`

### Post Statuses

**pkp_plugin** and **pkp_plugin_release**

`submission` - Initial submission

`publish` - Approved

`update` - User-submitted update

`disable` - Plugins or releases which have been temporarily made unavailable by an admin

An attempt will be made to use the built-in `update` post status to manage user updates. If it doesn't cause any conflicts, it will allow us to take advantage of WordPress's built-in update `diff` generator.

### Taxonomies

**pkp_plugin**

`pkp_application` - Compatible application (terms: ojs|omp|etc). This is a hierarchical taxonomy and child terms will reference specific versions (terms: ojs2.4.6|ocs1.1.1-1|etc)

`pkp_certification` - Level of trust afforded plugin (terms: partner|reviewed|official)

`pkp_category` - Type of plugin (terms: themes|gateways|auth|etc)

**pkp_plugin_release**

`pkp_application` - Compatible application (terms: ojs|omp|etc). This is a hierarchical taxonomy and child terms will reference specific versions (terms: ojs2.4.6|ocs1.1.1-1|etc)

`pkp_certification` - Level of trust afforded plugin (terms: partner|reviewed|official)

When a `pkp_application` term is assigned to a `pkp_plugin_release` it will be automatically assigned to its parent `pkp_plugin` post.

### Post Meta

**pkp_plugin**

`_homepage` - (string) Link to homepage for the plugin

`_installation` - (string) Installation requirements and/or instructions

**pkp_plugin_release**

`_md5` - (string) MD5 hash of the verified file

`_package` - (string) URL to the download package

### User Meta

`_institution` - (string) Name of associated institution

3. Submission and Approval Process
----------------------------------

To make a new submission, a visitor must register for a user account. Once registered, they can fill out a form, which will create the associated `pkp_plugin` and `pkp_plugin_version` posts, and assign them a `submission` post status.

These submissions will then be approved by PKP staff. Once approved the submission post status will change to `publish`.

The user who submitted the plugin will then be able to view a small dashboard where they can submit changes to previously submitted plugins and new releases. These submissions will be marked with a `update` post status. PKP staff will then be able to view these update and publish (`publish`) new releases or merge updated data.

4. Updates
------------

When a user submits a modification to a published plugin or release, it will be assigned the `update` post status. Updates refer to the update or plugin they are an update to by their `post_parent` value. Updates are displayed in the admin editing panel, where they can be reviewed, edited, saved for later or committed. When committed, they'll replace their parent post and be deleted.
