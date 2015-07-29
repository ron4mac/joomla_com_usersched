Installation and Usage:

Install this component as you would any other Joomla! extension. Create menu items for calendar (scheduler) 
instances. When creating a menu item, choose whether the instance will belong to the user, a group or the site. 
For group and site instances, choose who can edit the instance.


Calendar event alert notification:

Calendars can be configured to have email/SMS alerts attached to events. For this to function, a CRON job must 
set on the server to run periodically (every 5 minutes is probably best). The most efficient method is to run 
PHP directly to execute the task. And it could be beneficial to use a separate php.ini file for the run.

Sample CRON job setting:
<PHP PATH>/php -q -c <SOME PATH>/cron_php.ini  <JOOMLA BASE>/components/com_usersched/cron.php &>> <SOME PATH>/cronmsg.txt

Sample PHP ini file (cron_php.ini):
< - -- --- ---- clip ---- --- -- - >
[PHP]
memory_limit = 1024M

; may need these access sqlite Db when PHP is run from the shell
extension = "pdo.so"
extension = "pdo_sqlite.so"

; may need to set the correct time zone to be in sync with the calendar
;date.timezone = America/New_York
< - -- --- ---- clip ---- --- -- - >