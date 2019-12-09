Using PHP Codesniffer for QA reviews of WordPress projects
=====================================================
[![Build Status](https://travis-ci.org/jrfnl/QA-WP-Projects.png?branch=master)](https://travis-ci.org/jrfnl/QA-WP-Projects)

* [Introduction](#introduction)
* [Talks](#talks)
* [Installation](#installation)
* [Using the installed rulesets](#using-the-installed-rulesets)
* [Reviewing WordPress plugins and themes](#reviewing-wordpress-plugins-and-themes)
    + [Before running the tool](#before-running-the-tool)
    + [How to run ?](#how-to-run-)
    + [How to interpret the results ?](#how-to-interpret-the-results-)
* [License](#license)


## Introduction

This project primarily was created as an example / proof of concept for talks about how to use a variety of PHPCS rules and standards to get an indication of code quality for WordPress plugins and themes.

As a secondary use-case, this project can be used to install all relevant WordPress related [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) rulesets in one go.

**_Note: This repository will not be actively maintained._**


## Talks

Presentations which feature this repo:

Date | Event | Slides | Video
--- | --- | --- | ---
Dec 2019 | WP Leiden meetup | [Slides](https://speakerdeck.com/jrf/your-code-can-be-poetry-too) |
Mar 2018 | WordCamp Rotterdam | [Slides](https://speakerdeck.com/jrf/leveraging-the-wordpress-coding-standards-to-review-plugins-and-themes-1) | [Video](https://wordpress.tv/2018/04/18/juliette-reinders-folmer-leveraging-the-wordpress-coding-standards-to-review-plugins-and-themes/)
Nov 2017 | WordCamp Utrecht | [Slides](https://speakerdeck.com/jrf/leveraging-the-wordpress-coding-standards-to-review-plugins-and-themes) | [Video](https://wordpress.tv/2018/01/05/juliette-reinders-folmer-leveraging-the-wordpress-coding-standards-to-review/)
Sep 2017 | WP Fryslân meetup | [Slides](https://speakerdeck.com/jrf/for-non-developers) |


## Installation

### Requirements

* PHP 5.4+.
* [Composer](https://getcomposer.org/download/)

### Installation for a specific project

> #### About project based installation:
> Using this method, the tooling will only be available to that specific project.
>
> **Pros**: Using a project based install, you document what tooling the project uses and make it easy for other contributors to the project to install that tooling.
>
> **Cons:** For each additional project, you will need to do the installation again.

From the project root, run the following command:
```bash
composer require --dev jrfnl/qawpprojects
```

The PHP_CodeSniffer command will now be available from the project root as `vendor/bin/phpcs`.

To update the install in the future, run the following command from the project root:
```bash
composer update jrfnl/qawpprojects --with-dependencies
```

### Global installation

> #### About global installation:
> Using this method, the tooling will be available from anywhere on your system.
>
> **Pros**: You only need to install it once.
>
> **Cons:** If you use the tooling for your projects and the projects are open to other contributors, it will be unclear what tooling you expect them to use.

Run the following command from anywhere on your system:
```bash
composer global require --dev jrfnl/qawpprojects
```

Make sure the Composer "home" `vendor/bin` directory is in your system path.
To find out what the Composer "home" directory is, run `composer config --list --global` and see what's listed under `home`.
Take that directory, add `/vendor/bin` to it and make sure it's in your operating system's `$PATH` variable.

The PHP_CodeSniffer command will now be available from anywhere on your computer as `phpcs`.

To update the install in the future, run the following command from anywhere on your system:
```bash
composer global update jrfnl/qawpprojects --with-dependencies
```

### Verify the install succeeded

Once the installation is finished, run the following command to verify the installation was succesfull:
```bash
# For a project based install:
vendor/bin/phpcs -i

# For a global install:
phpcs -i
```

The output should look like this:
```
The installed coding standards are MySource, PEAR, PSR1, PSR12, PSR2, Squiz, Zend, WP-QA-Basic, WP-QA-Strict,
PHPCompatibility, PHPCompatibilityParagonieRandomCompat, PHPCompatibilityParagonieSodiumCompat, PHPCompatibilityWP,
WordPress, WordPress-Core, WordPress-Docs, WordPress-Extra and WPThemeReview
```


## Using the installed rulesets

After installation, you can use any of the above listed PHPCS rulesets, or a combination of them, for your projects.

* `WordPress-Core` checks code based on the code-style and best practice guidelines described in the [WordPress PHP Coding Standards Handbook](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/).
* `WordPress-Docs` checks code documentation based on the guidelines described in the [WordPress PHP Documentation Standards Handbook](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/).
* `WordPress-Extra` is `WordPress-Core` + extra checks for common best practices, both for code in general, as well as WordPress specific best practices.
* `WordPress` is a combination of the above three rulesets.

For plugins and themes, using either `WordPress` or `WordPress-Extra` is recommended.

* [`PHPCompatibilityWP`](https://github.com/PHPCompatibility/PHPCompatibilityWP) checks code for being PHP cross-version compatible while preventing false positives for PHP features polyfilled in WordPress itself.
    This is the recommended PHPCompatibility ruleset to use for WordPress projects. The other PHPCompatibility rulesets listed are included in this ruleset.

* [`WPThemeReview`](https://github.com/WPTRT/WPThemeReview) is specifically for WordPress themes and checks the code against the guidelines for submission to the Theme repository on wordpress.org as described in the [Theme Handbook](https://make.wordpress.org/themes/handbook/review/)

It is strongly recommended to document the settings you use in a custom ruleset and to add some minimal sniff configuration for optimal results.

An example ruleset which you can place in the root of your project, including documentation on what you should adjust, can be found in the [sample-project-ruleset](https://github.com/jrfnl/QA-WP-Projects/tree/master/sample-project-ruleset) directory.


## Reviewing WordPress plugins and themes

This repository comes with two native rulesets - `WP-QA-Basic` and `WP-QA-Strict` - which are specifically intended for reviewing WordPress plugins and themes without much knowledge of code.

These rulesets do not look at the code style consistency of code. They will only evaluate whether code is well documented, tested and whether there are any code quality issues detected.

When you run either of these rulesets over a project, a customized report will be displayed to give you a [fingerspitzengefuhl](https://en.wikipedia.org/wiki/Fingerspitzengef%C3%BChl) of the code quality of a project.


### Before running the tool

To use this tool to review WordPress plugins and themes, the tool needs a little bit of information about the plugin/theme you want to review.

* Download the plugin/theme and unzip it.
* Check with your webhost on which **version of PHP** the site for which you want to use the plugin/theme is running.
* Open the `readme.txt` file in the root of the plugin/theme directory and check what the **minimum supported WP version** is for the plugin/theme.
* Open the plugin main file or the theme `functions.php` file and check if it has a `Text Domain: my-plugin` header. If it has, make a note of the text domain. Otherwise, use the plugin/theme slug.
* "Guess" the prefixes the plugin/theme will use. Often this is the plugin/theme slug or an acronym based on the plugin/theme slug. For instance for `bbPress`, the prefix might be (and is): `bbp`.


### How to run ?

Run the tool from the project root like so (command based on global install):
```bash
phpcs . --standard=WP-QA-Basic --basepath=./ --runtime-set testVersion 5.6- --runtime-set minimum_supported_wp_version 4.5 --runtime-set prefixes plugin_prefix,plugin_acronym --ignore=*/node-modules/* --runtime-set text_domain plugin-slug
```

_:warning: Do replace the various values in the command with the values you looked up in the previous step._

The output will look something like this:
```
WORDPRESS PROJECT QA REPORT
====================================================================================================
This report highlights potential problem areas in the scanned code.
It is advisable to let an experienced developer assess whether the highlighted issues are actually
problematic.
This report is intended solely as soft advise, not as a hard judgement.
----------------------------------------------------------------------------------------------------

====================================================================================================
GENERAL INFORMATION ABOUT THE ANALYSED CODE BASE.
====================================================================================================

File Type |  Files  |     Lines *  |        Code         |       Comments
------------------------------------------------------------------------------
plugin    |     272 |        90640 |       57752 (63.7%) |       33160 (36.6%)
test      |      31 |          202 |         174 (86.1%) |          59 (29.2%)
vendor    |       0 |            0 |                     |
==============================================================================
Totals    |     303 |        90842 |       57926 (63.8%) |       33219 (36.6%)

* These stats exclude all blank lines.

====================================================================================================
ISSUES FOUND PER CATEGORY *
====================================================================================================
PLUGIN FILES                 | Errors   | % of LOC | Warnings | % of LOC
------------------------------------------------------------------------
hard errors                  |        1 |    0.00% |    -     |
dangerous code               |        2 |    0.00% |    -     |
untestable code              |       32 |    0.04% |       55 |    0.06%
outdated code                |    -     |          |        3 |    0.00%
messy code                   |    -     |          |       32 |    0.04%
incompatible code - PHP      |       75 |    0.08% |        2 |    0.00%
incompatible code - WP       |    -     |          |    -     |
potentially conflicting code |      173 |    0.19% |       24 |    0.03%
========================================================================
Total                        |      283 |    0.31% |      116 |    0.13%

TEST FILES                   | Errors   | % of LOC | Warnings | % of LOC
------------------------------------------------------------------------
hard errors                  |    -     |          |    -     |
dangerous code               |    -     |          |    -     |
untestable code              |    -     |          |    -     |
outdated code                |    -     |          |    -     |
messy code                   |    -     |          |    -     |
incompatible code - PHP      |    -     |          |        1 |    0.50%
incompatible code - WP       |    -     |          |    -     |
potentially conflicting code |        9 |    4.46% |    -     |
========================================================================
Total                        |        9 |    4.46% |        1 |    0.50%
----------------------------------------------------------------------------------------------------
```

> :bulb: **Pro-tip**
>
> Use a custom ruleset instead of this long command. See the [sample-project-ruleset](https://github.com/jrfnl/QA-WP-Projects/tree/master/sample-project-ruleset) for a starting point and replace `<rule ref="WordPress"/>` in the `Set the rules` section with the `WP-QA` ruleset you want to use.
>
> Once you have a custom ruleset set up, the command simply becomes:
> ```bash
> vendor/bin/phpcs
> ```



### How to interpret the results ?

If the resulting report is a mystery to you, I'd recommend watching the [video](https://wordpress.tv/2018/04/18/juliette-reinders-folmer-leveraging-the-wordpress-coding-standards-to-review-plugins-and-themes/) from the talk at WordCamp Rotterdam to hear how to interpret the results.


## License

This code is released under the [MIT License](LICENSE).
