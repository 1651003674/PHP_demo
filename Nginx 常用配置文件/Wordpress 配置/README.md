# WordPress Config

```
server {
        listen 80;
        # 配置 wordpress 项目根目录
        root /root/wordpress;

        # 注意我们添加了 index.php
        index index.php index.html index.htm index.nginx-debian.html;

        server_name laimikaer.cn www.laimikaer.cn;

        # uri 重写 配置
        location / {

                try_files $uri $uri/ /index.php?$query_string;
        }

        # pass PHP scripts to FastCGI server
        # PHP 文件 解析配置
        location ~ \.php$ {

                try_files $uri /index.php =404;

                fastcgi_split_path_info ^(.+\.php)(/.+)$;

                fastcgi_pass unix:/var/run/php/php7.1-fpm.sock;

                fastcgi_index index.php;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                include fastcgi_params;
        }
}

```