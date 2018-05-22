#!/bin/bash
mysqldump -uroot -pqlguoji ql > /var/www/db/beifen_$(date +%Y%m%d_%H%M%S).sql
#!/bin/bash
mysqldump -uroot -pqlguoji ql | gzip > /var/www/db/beifen_$(date +%Y%m%d_%H%M%S).sql.gz
