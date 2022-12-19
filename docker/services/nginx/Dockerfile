ARG NGINX_VERSION
FROM ${NGINX_VERSION}

ARG TZ
ARG NGINX_VERSION
ARG CONTAINER_PACKAGE_URL
ARG NGINX_INSTALL_APPS
ARG DOMAIN

ENV INSTALL_APPS=",${NGINX_INSTALL_APPS},"

RUN if [ "${CONTAINER_PACKAGE_URL}" != "" ]; then \
        sed -i "s/dl-cdn.alpinelinux.org/${CONTAINER_PACKAGE_URL}/g" /etc/apk/repositories; \
    fi

COPY ./certbot/checkssl.sh /tmp

RUN if [ -z "${INSTALL_APPS##*,certbot,*}" ]; then \
        echo "---------- Install certbot ----------"; \
        apk add --no-cache certbot; \
        if [ -n "${DOMAIN}" ]; then \
            sed -i "s/example.com/${DOMAIN}/g" /tmp/checkssl.sh; \
            mv /tmp/checkssl.sh /etc/periodic/15min; \
            chmod -R +x /etc/periodic/15min;\
        fi; \
    fi

WORKDIR /www
