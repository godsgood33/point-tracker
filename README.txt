=== Plugin Name ===
Plugin Name: Point Tracker
Contributors: godsgood33
Tags: team activities
Requires at least: 4.4.2
Requires PHP: 5.6
Tested up to: 4.9.7
Stable tag: 4.9.7
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

This plugin will allow site admins to create challenges and then participants can enter their activity.

== Description ==

This plugin does not require but works well with membership plugins.  The admin can create a challenge,
share the challenge link with whom they wish.  Those wishing to participate can click on the link and opt to join the challenge.
Once the participant has joined the challenge, they can enter activity against that challenge upto daily and receive points.
Leader boards are available if the admin opts to have the system make one available.

== Installation ==

This section describes how to install the plugin and get it working.

1. Extract the 'point-tracker.zip' file in the '/wp-content/plugins/' directory or install using the plugin installer in WP
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The Point Tracker admin menu is used to administer the challenges, its activitie, and participants.
4. There is a PT Settings page under the Settings menu to control options for the Point Tracker

== Frequently Asked Questions ==

= Why is the coordinator portion behind the admin? =

I felt the creation or modification of a challenge was an administrative function and so it deserved to be behind the
admin dashboard.  If you want to allow another user on your site to create or modify a challenge, they will have to have
the 'manage_options' permission set.

= What happens if I initially set a challenge for approval required, then change it? =

The challenge will automatically approve all pending participants and any future participants will automatically be approved

== Screenshots ==

1. This is a screenshot of the new menu that Point Tracker creates
2. This is a screenshot of the Challenge editor
3. This is a screenshot of the Activity editor
4. This is a screenshot of the admin Leader Board
5. This is a screenshot of the participant log

== Changelog ==

= 1.0 =
* Initial release

= 2.0 =
* Add start and end date to challenge activities
* Cleaned up admin and public UI
* Allow non-site members to join a challenge and track their points
* Add and use DataTables

== Upgrade Notice ==

Upgrade section