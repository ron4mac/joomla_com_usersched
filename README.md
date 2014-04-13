joomla-usersched
================

A Joomla! 2.x-3.x component based on the DHX (DHTMLX) Scheduler

This is a calendar component that uses the Scheduler product from DHX (http://dhtmlx.com/docs/products/dhtmlxScheduler).

An added feature is event alerts (SMS/email) sent to configured alertees. _Cron_ capability is needed for alerts to be sent. The calendar can also have events auto-populated from Joomla! user profile birthdays and holidays from Google calendar feeds.

The component uses SQLite3 databases for calendar information, allowing separate calendars for all users, groups, or site-wide.

**Requires** that the *rjuserdata* library is installed in the Joomla! instance (find separately http://github.com/ron4mac/joomla-lib-rjuserdata).
To download a single, auto-generated package installer for the latest versions of both com_usersched and lib_rjuserdata, [click here](http://rnp-web.net/git/usersched/packager/).