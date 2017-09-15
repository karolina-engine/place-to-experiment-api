<?php
/**
 * Diferent roles needed for testing.
 *
 * Test: A test user that is created everytime the tests are run.
 * Visitor: A visitor that looks.
 * Creator: A user that creates and looks.
 * Admin: An admin user that does anything.
 *
 * Admin user account is presumed to exist in the DB.
 * For Visitor and Creator, if the user account is not found, the test will try to register it.
 */
define ('test_password', 'testpass123');
define ('test_first_name', 'Robot');
define ('test_last_name', 'Tester');
define ('test_short_description', 'This is a test account.');
define ('test_profile_description', 'Welcome to my profile! I am a friendly tester robot.');
define ('visitor_email', 'visitor@example.com');
define ('visitor_password', 'visitorpass123');
define ('visitor_first_name', 'Robot');
define ('visitor_last_name', 'Visitor');
define ('creator_email', 'creator@example.com');
define ('creator_password', 'creatorpass123');
define ('creator_first_name', 'Robot');
define ('creator_last_name', 'Creator');
define ('admin_email', 'admin@example.com');
define ('admin_password', 'adminpass123');
define ('lang', 'en');

//TODO: add arrays for role and status validation needed in Api.php
