#!/bin/bash
mysqldump -uroot -proot z_tp > /var/www/html/db/beifen_$(date +%Y%m%d_%H%M%S).sql
#!/bin/bash
mysqldump -uroot -proot z_tp | gzip > /var/www/html/db/beifen_$(date +%Y%m%d_%H%M%S).sql.gz
