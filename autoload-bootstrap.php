<?php
/**
 * Autoload the autoload files of the individual standards.
 *
 * @category PHP
 * @package  QA-WP-Projects
 * @author   Juliette Reinders Folmer <qa_wp_projects_nospam@adviesenzo.nl>
 */

if ( file_exists( __DIR__ . '/vendor/phpcompatibility/php-compatibility/PHPCSAliases.php' ) ) {
	require_once __DIR__ . '/vendor/phpcompatibility/php-compatibility/PHPCSAliases.php';
}

if ( file_exists( __DIR__ . '/vendor/wp-coding-standards/wpcs/WordPress/PHPCSAliases.php' ) ) {
	require_once __DIR__ . '/vendor/wp-coding-standards/wpcs/WordPress/PHPCSAliases.php';
}

/**
 * Register an autoloader to be able to load the custom report based
 * on a Fully Qualified (Class)Name.
 *
 * @param string $class Class being requested.
 */
spl_autoload_register( function ( $class ) {
    // Only try & load our own classes.
    if ( stripos( $class, 'WPQA' ) !== 0 ) {
        return;
    }

	// The only class(es) this standard has, are in the Reports directory.
    $class = str_replace( 'WPQA\\', 'Reports\\', $class );
    $file  = realpath( __DIR__ ) . DIRECTORY_SEPARATOR . strtr( $class, '\\', DIRECTORY_SEPARATOR ) . '.php';

    if ( file_exists( $file ) ) {
        include_once $file;
    }
}, true );
