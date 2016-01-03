joomla-usersched
================

A Joomla! 3.x component based on the DHX (DHTMLX) Scheduler

This is a calendar component that uses the Scheduler product from DHX (http://dhtmlx.com/docs/products/dhtmlxScheduler).

An added feature is event alerts (SMS/email) sent to configured alertees. _Cron_ capability is needed for alerts to be sent (see README.txt). The calendar can also have events auto-populated from Joomla! user profile birthdays and holidays from Google calendar feeds.

The component uses SQLite3 databases for calendar information, allowing separate calendars for all users, groups, or site-wide.

PLEASE NOTE: The folowing requirement is only for versions prior to 0.9.9. The *rjuserdata* library is deprecated. See the *rjuserd* plugin (http://github.com/ron4mac/joomla-plg-rjuserd) for userdata storage management.

**Requires** that the *rjuserdata* library is installed in the Joomla! instance (find separately http://github.com/ron4mac/joomla-lib-rjuserdata).
To download a single, auto-generated package installer for the latest release versions of both com_usersched and lib_rjuserdata, [click here](http://rjcrans.net/git/com_usersched/packager/).
