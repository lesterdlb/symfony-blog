FROM nginx:1.19

ARG UID

RUN adduser -u ${UID} --disabled-password --gecos "" appuser

RUN mkdir -p /appdata/www/var/log/nginx \
    && touch /appdata/www/var/log/nginx/access.log \
    && touch /appdata/www/var/log/nginx/error.log

COPY default.conf /etc/nginx/conf.d/
