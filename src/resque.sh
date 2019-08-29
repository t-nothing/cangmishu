#!/bin/bash
# Program:仓秘书队列
# History:
# 2016/07/24    @胡子锅   First release
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH

php artisan queue:work redis --queue=cangmishu_emails --tries=3  >> ./storage/logs/cangmishu_emails.log 2>&1 &

php artisan queue:work redis --queue=cangmishu_push --tries=3>> ./storage/logs/cangmishu_push.log 2>&1 &

php artisan queue:work redis --queue=cangmishu_push_third_party --tries=1>> ./storage/logs/cangmishu_push_third_party.log 2>&1 &

