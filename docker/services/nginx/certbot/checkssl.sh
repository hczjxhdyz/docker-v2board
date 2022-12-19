# #!/bin/sh
#自动检查域名ssl证书是否生成 并且加入定时更新任务当中
echo "开始运行" >> /tmp/log.txt
SSL_PATH=/etc/letsencrypt/live/
DOMAIN=example.com
echo "${SSL_PATH}${DOMAIN}"

if [ ! -d "${SSL_PATH}${DOMAIN}" ];
then
    # 如果目录不存在（该域名没有申请过任何证书）
    echo "开始申请SSL证书"
    certbot certonly --webroot --agree-tos --renew-by-default \
        --preferred-challenges http-01 --server https://acme-v02.api.letsencrypt.org/directory \
        --text --email mxdevit@gmail.com \
        -w /www/public -d ${DOMAIN}
    echo "判断证书是否申请成功"
    if [ -d "${SSL_PATH}${DOMAIN}" ];
    then
    sed -i "s/v2board\/v2board/${DOMAIN}\/${DOMAIN}/g" /etc/nginx/conf.d/v2board.conf
    fi
else
    # 执行证书更新
    echo "证书周期性检查更新"
    certbot renew
fi
