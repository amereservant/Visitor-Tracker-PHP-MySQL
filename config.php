<?php
/**
 * Database Configuration
 *
 * Enter the correct values for your database connection.
 */
define('SQLITE_FILE', 'tracker.sqlite');

/**
 * Database DSN
 *
 * If you prefer MySQL/etc., then you can change it here.
 *
 * Note: SQL syntax may not be compatible with other database types since this was
 * written for SQLite.
 */
define('DB_DSN', 'sqlite:'. SQLITE_FILE);

/**
 * Exit Script On Error
 *
 * Determines if the script should stop on error.  This is used by the db class.
 *
 * Set to TRUE for debugging, FALSE for production.
 */
define('EXIT_ON_ERROR', TRUE);
