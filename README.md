joomla-usersched
================

A Joomla! 3.x component based on the DHX (DHTMLX) Scheduler

This is a calendar component that uses the Scheduler product from DHX (http://dhtmlx.com/docs/products/dhtmlxScheduler).

An added feature is event alerts (SMS/email) sent to configured alertees. _Cron_ capability is needed for alerts to be sent (see README.txt). The calendar can also have events auto-populated from Joomla user profile birthdays and holidays from Google calendar feeds.

The component uses SQLite3 databases for calendar information, allowing separate calendars for all users, groups, or site-wide. The companion plugin, [plg_system_rjuserd](http://github.com/ron4mac/joomla_plg_rjuserd), should be used to manage the storage location for users and groups. To download a single, auto-generated package installer for the latest release versions of both com_usersched and plg_system_rjuserd, [click here](http://rjcrans.net/git/com_usersched/packager2/).
