=== Plugin Name ===
Point Tracker
Contributors: godsgood33
Tags: team activities, challenge, content
Requires at least: 4.4.2
Requires PHP: 5.6
Tested up to: 5.0.2
Stable tag: 1.6
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

This plugin will allow site admins to create challenges and then participants can enter their activity.

== Description ==

This plugin does not require but works well with membership plugins.  The admin can create a challenge, share the challenge link with whom they wish.  Those wishing to participate can click on the link and opt to join the challenge. Once the participant has joined the challenge, they can enter activity against that challenge upto daily and receive points.

== Installation ==

This section describes how to install the plugin and get it working.

1. Extract the 'point-tracker.zip' file in the '/wp-content/plugins/' directory or install using the plugin installer in WP
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The Point Tracker admin menu is used to administer the challenges, its activities, and participants.
4. Navigate to the Point Tracker -> Point Tracker admin menu
5. Fill out the form and create a challenge (add a name, start and end dates, and a description) then click "Save"
6. Copy the link that appears just above the description box
7. Navigate to the Point Tracker -> Activities admin menu
8. Select the challenge you just created from the drop down
9. Fill out the boxes and create your first activity for that challenge and click "Save"
10. Send the link to whomever you wish so they can join 
11. Under Settings -> Point Tracker there are global options that you can enable, right now, it is only requiring an account for those wanting to participate in a challenge

== Frequently Asked Questions ==

= Why is the coordinator portion behind the admin? =

I felt the creation or modification of a challenge was an administrative function and so it deserved to be behind the
admin dashboard.  If you want to allow another user on your site to create or modify a challenge, they will have to have
the 'manage_options' permission set.

= What happens if I initially set a challenge for approval required, then change it? =

The challenge will automatically approve all pending participants and any future participants will automatically be approved

= Once the challenge is over what happens? =

You will need to visit the Point Tracker -> Participants page to see who has the most points

= A participant entered some wrong information, what do I do? =

You can either delete it yourself or if they visit the View My Activity page (/my-activity/?chal={linkcode}), they will be able to delete it themselves

= What is a "hidden" activity? =

This is an activity that will not be scored.  The points will be defaulted to 0.  They will also not be displayed on any public displays other than the challenge form asking the user for their answer.  They are ideal for collecting information like feedback that you don't necessarily want to reward.

= What is an activity "group"? =

Activity groups allow you to group the activities into sections with a header.  This allow the participants to more easily find the questions they are wanting to answer.  They are optional.

= How can I create a custom page =

After you've created a challenge, copy/paste the unique code for the challenge into a new shortcode `[challenge chal={challenge code you copied}][/challenge]`, then publish the page.  You can also create custom pages for the My Activity and Leader Board pages using their respective shortcodes (`[my_activity chal={code}][/my_activity]` and `[leader_board chal={code]][/leader_board]`).

= I'd like a leader board =

One will be available in Point Tracker Pro at https://wppointtracker.com/point-tracker-pro/

== Screenshots ==

1. This is a screenshot of the new menu that Point Tracker creates
2. This is a screenshot of the Challenge editor
3. This is a screenshot of the Activity editor
4. This is a screenshot of the Participant list
5. This is a screenshot of the Entry log
6. This is a screenshot of an example challenge and activities
7. This is a screenshot of the "View My Activity" page after a test user has saved some activities

== Changelog ==

= 1.6 =
* Made Gutenberg and WP 5+ compatible

= 1.5 =
* Added Long Text as an activity type
* Added checkbox to allow for hidden activities
* Added ability to group activities
* Added dashboard widget
* Added beginnings of contextual help dropdowns

= 1.4 =
* Fix bug in admin with start and end date formats
* Add documentation for creating custom challenge and activity pages
* Add Upgrade admin submenu (not linked right now)
* Extended the size of the label for radio buttons and checkboxes and answer
* Added a span below text fields to show character count

= 1.3.1 =
* Set the challenge end date to be no earlier than the start date (can't end before you start)
* Fixed bug in showing challenge list

= 1.3 =
* Changed shortcode to [challenge] instead of [challenge_page]
* Added "chal" parameter to shortcode to allow for creating custom pages
* Bug fixes

= 1.2 =
* More bug fixes
* Fixed incompatibility with plugins that have a save_post hook

= 1.1 =
* Couple bug fixes
* Add screenshots

= 1.0 =
* Initial release

== Upgrade Notice ==

Upgrade section