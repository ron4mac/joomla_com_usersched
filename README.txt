Installation and Usage:

Install this component as you would any other Joomla! extension. Create menu items for calendar (scheduler) 
instances. When creating a menu item, choose whether the instance will belong to the user, a group or the site. 
For group and site instances, choose who can edit the instance.


Calendar event alert notification:

Calendars can be configured to have email/SMS alerts attached to events. For this to function, a CRON job must 
be set on the server to run periodically (every 5 minutes is probably best).

Sample CRON job setting:
/usr/bin/wget -q -O - "<JOOMLA_URL>/index.php?option=com_usersched&task=Raw.cron&format=raw" >> <SOME_PATH>/cronmsg.txt
