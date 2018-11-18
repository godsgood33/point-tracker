<?php
/**
 * File: constants.php
 * Author: Ryan Prather
 * Purpose: Store any constants used in the plugin
 */

/**
 * Constant string sent to users when the coordinator approves their access to a challenge
 *
 * @var string
 */
define('PT_USER_APPROVED', 'Greetings,
The challenge coordinator has approved you for the challenge you requested to join.  '.
    'Next time you login, you will see it listed on the "Challenge List" page and you will be '.
    'able to apply activity to that challenge.');

/**
 * Constant string sent to users who have been manually added to a challenge
 *
 * @var string
 */
define('PT_USER_ADDED', 'Greetings,
The challenge coordinator has added to to a challenge they are running.  '.
    'Next time you login you will see it listed on the "Challenge List" page and you will be '.
    'able to apply activity to that challenge.

Challenge Name: {name}
Description: {desc}

If you have any questions, please e-mail <a href="mailto:'.get_option('admin_email', '').'">'.get_option('admin_email', '').'</a>');

/**
 * Constant string sent to users when the coordinator denies their access to a challenge
 *
 * @var string
 */
define('PT_USER_DENIED', 'Greetings,
The challenge coordinator has denied your request for access to the challenge.  '.
    'Please follow up with them if you think this was done in error. '.
    '<a href="mailto:'.get_option('admin_email', '').'">'.get_option('admin_email', '').'</a>');

/**
 * Constant string sent to new users when the admin adds them to a challenge
 *
 * @var string
 */
define('PT_NEW_USER', "Greetings,
At your request, we have added a new account for you at ".site_url().".
    You can now login with the credentials below:

Username: {username}
Password: {password}

If you have any questions please e-mail
    <a href='mailto:".get_option('admin_email', '')."'>".get_option('admin_email', '')."</a>");

/**
 * Constant defining the email content used to notify the admin that a new participant has joined the challenge
 *
 * @var string
 */
define('PT_NEW_PARTICIPANT', "Hey Admin, a participant joined a challenge
Name: {name}
Challenge: {chal}");
