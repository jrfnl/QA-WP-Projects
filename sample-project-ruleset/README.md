## About this `phpcs.xml.dist` file

This is a sample ruleset which can be used as a starting point for a PHPCS ruleset for a WordPress project.

Whether you are reviewing other people's code or working on your own code, it is useful to have such a ruleset in place to:
- Document the settings used both for your future self as well as for other contributors to the project.
- Ensure that everyone uses the same settings when reviewing the code.
- Make life easier as you no longer will have to type in a long range of command line parameters.

Before you use this ruleset, make sure to customize the following:
- The ruleset name and description.
- The supported PHP versions as set in the value for `testVersion`.
    For information on how to set the value for `testVersion`, please see the [PHPCompatibility readme](https://github.com/PHPCompatibility/PHPCompatibility#sniffing-your-code-for-compatibility-with-specific-php-versions).
- The minimum supported WP version `minimum_supported_wp_version`.
    For more information about this setting, see the [WordPressCS wiki](https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#minimum-wp-version-to-check-for-usage-of-deprecated-functions-classes-and-function-parameters).
- The `text-domain` used by the project.
    For more information about this setting, see the [WordPressCS wiki](https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#internationalization-setting-your-text-domain).
- The `prefixes` used by the project.
    For more information about this setting, see the [WordPressCS wiki](https://github.com/WordPress/WordPress-Coding-Standards/wiki/Customizable-sniff-properties#naming-conventions-prefix-everything-in-the-global-namespace).

For more information about PHPCS rulesets in general, see the [PHP_CodeSniffer wiki](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Annotated-Ruleset).
