<?php
ob_start();
echo '/usr/local/bin/php -q -c /home/customer/www/rfourm.net/public_html/cron_php.ini /home/customer/www/rfourm.net/public_html/joom3dev/components/com_usersched/cron.php >> /home/customer/www/rfourm.net/public_html/cron/dusersched.txt';
phpinfo();
file_put_contents('pinf.html', ob_get_contents());
ob_end_clean();
