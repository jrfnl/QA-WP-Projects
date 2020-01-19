# Changelog for QA-WP-Projects

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/) and [Keep a CHANGELOG](https://keepachangelog.com/).

## [1.0.1] - 2020-01-19

### Changed
* Composer: Supported version of the [DealerDirect Composer PHPCS plugin] has been changed from `^0.5.0` to `^0.5 || ^0.6` to allow for the newly released version of the plugin.
* The [PHP_CodeSniffer] dependency has been updated to version `3.5.3`.
* The [PHPCompatibility] dependency has been updated to version `9.3.5`.


## [1.0] - 2019-12-09

### Added
* WP-QA-Basic ruleset: the new WPCS 2.1.0 `WordPress.PHP.IniSet` sniff.
* WP-QA-Strict ruleset: the new WPCS `WordPress.CodeAnalysis.EscapedNotTranslated`, `WordPress.PHP.TypeCasts`, `WordPress.NamingConventions.ValidPostTypeSlug` sniffs.
* Sample project ruleset, including explanation about its use.
* Composer: [WPThemeReviewCS] dependency.
    While not directly used by the WP-QA rulesets, including this dependency in the install allows for devs to install all relevant WP related PHPCS rulesets in one go.
* README + CHANGELOG documentation.

### Changed
* Composer: Supported version of [PHP_CodeSniffer] has been changed from `^3.3.2` to `^3.5.2`.
* Composer: Supported version of [WordPressCS] has been changed from `^1.1.0` to `^2.2.0`.
* Composer: Supported version of [PHPCompatibilityWP] has been changed from `^2.0.0` to `^2.1.0`.
* Minor update for compatibility with WordPressCS 2.0.0+.
* Improved Travis QA checks.


## [0.4] - 2018-11-11

### Added
* WP-QA-Strict ruleset: the new WPCS `WordPress.PHP.NoSilencedErrors` sniff.

### Changed
* Composer: Supported version of [PHP_CodeSniffer] has been changed from `^3.3.0` to `^3.3.2`.
* Composer: Supported version of [WordPressCS] has been changed from `^1.0.0` to `^1.1.0`.
* Composer: Supported version of [PHPCompatibilityWP] has been changed from `*` to `^2.0.0`, which uses [PHPCompatibility] `^9.0.0` under the hood.
* Composer: Supported version of the [DealerDirect Composer PHPCS plugin] has been changed from `^0.4.3` to `^0.5.0`.
* Both the rulesets, as well as the `WPQA` report have been updated for compatibility with PHPCompatibility 9.0.0.
* Improved Travis QA checks.


## [0.3] - 2018-09-06

### Added
* WP-QA-Basic ruleset: the new WPCS 1.0.0 `WordPress.WP.DeprecatedParameterValues` sniff.
* WP-QA-Strict ruleset: the new WPCS 1.0.0 `WordPress.Security.PluginMenuSlug`, `WordPress.Security.SafeRedirect` and `WordPress.PHP.PregQuoteDelimiter` sniffs.

### Changed
* Composer: [PHP_CodeSniffer] will now be loaded again from the canonical source as the custom report feature PR has been merged upstream.
* Composer: Switched from using the external [PHPCompatibility] standard to using [PHPCompatibilityWP] at version `1.0.0`.
* Composer: Supported version of [WordPressCS] has been changed from `~0.14.0` to `^1.0.0`.
* Both the rulesets, as well as the `WPQA` report have been updated for compatibility with WordPressCS 1.0.0.


## [0.2] - 2018-03-25

This package is now available on Packagist as [`jrfnl/qawpprojects`](https://packagist.org/packages/jrfnl/qawpprojects)

### Added
* A custom `WPQA` report type for use with the `WP-QA-Basic` and `WP-QA-Strict` rulesets.

### Changed
* Composer: Supported version of [PHP_CodeSniffer] has been updated from `^3.1.1` to `^3.3.0`.
    For the time being, the `PHP_CodeSniffer` dependency will be loaded from a fork of PHPCS which contains a specific new feature related to the custom report. This feature has been pulled to PHPCS upstream and is expected to be merged in due time.
* Composer: Supported version of [PHPCompatibility] has been changed from `^8.0.1` to `^8.2.0`.
* WP-QA-Basic ruleset: Replaced the WP `DiscourageGoto` sniff with the same sniff which has now been merged upstream.

### Removed
* Composer: The PHPLoc dependency.
* WP-QA-Basic ruleset: The `basepath` directive as this only has effect when the ruleset is in the root of the project being scanned.
* WP-QA-Basic ruleset: Exclusion of the `vendor` directory.
    If dependencies are shipped with a theme/plugin, the dependencies should also comply with the best practices.


## 0.1 - 2017-11-24

Initial release.


[PHP_CodeSniffer]: https://github.com/squizlabs/PHP_CodeSniffer/releases
[WordPressCS]: https://github.com/WordPress/WordPress-Coding-Standards/blob/develop/CHANGELOG.md
[PHPCompatibilityWP]: https://github.com/PHPCompatibility/PHPCompatibilityWP#changelog
[PHPCompatibility]: https://github.com/PHPCompatibility/PHPCompatibility/blob/master/CHANGELOG.md
[WPThemeReviewCS]: https://github.com/WPTRT/WPThemeReview/blob/develop/CHANGELOG.md
[DealerDirect Composer PHPCS plugin]: https://github.com/Dealerdirect/phpcodesniffer-composer-installer/releases

[0.5]: https://github.com/Yoast/yoastcs/compare/0.4...0.5
[0.4]: https://github.com/Yoast/yoastcs/compare/0.3...0.4
[0.3]: https://github.com/Yoast/yoastcs/compare/0.2...0.3
[0.2]: https://github.com/Yoast/yoastcs/compare/0.1...0.2
