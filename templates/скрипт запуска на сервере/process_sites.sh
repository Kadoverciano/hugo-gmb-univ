#!/bin/bash
#-xv

STAGING_DIR="/home/svc_user/hugo-site"
FAILED_DIR="/home/svc_user/hugo-fail"
WWW_PATH="/data/www"
LOG_FILE="/home/svc_user/hugo_process_sites.log"
HUGO_BIN="/home/svc_user/hugo"

log_message() {
  DOMAIN=$1
  STATUS=$2
  MESSAGE=$3
  LOG_ENTRY="$DOMAIN;$STATUS;$(date '+%d.%m.%Y;%H:%M:%S');$MESSAGE"
  echo "$LOG_ENTRY" >> "$LOG_FILE"
  echo "LOG: $LOG_ENTRY"
}

set_perm() {
  SITE_PATH=$1
  setfacl -R -b "$SITE_PATH"
  chown -R dev_user:dev_user "$SITE_PATH"
  chmod 700 "$SITE_PATH"
  find "$SITE_PATH" \( -type f -exec chmod 600 {} + \) -o \( -type d -exec chmod 700 {} + \)
  setfacl -d -R -m u:dev_user:rwx,u:svc_user:rwx,u:www-data:r-x,g::---,o::---,m::rwx "$SITE_PATH"
  find "$SITE_PATH" \( -type f -exec setfacl -m u:www-data:r--,u:svc_user:rw-,u:dev_user:rw- {} + \) -o \( -type d -exec setfacl -m u:www-data:r-x,u:svc_user:rwx,u:dev_user:rwx {} + \)
  setfacl -R -m m::rwx "$SITE_PATH"
}

add_robots() {
  SITE_PATH=$1
  DOMAIN=$2
  cat <<EOF > "$SITE_PATH/public/robots.txt"
User-agent: *
Allow: /files/*.css
Allow: /files/*.js
User-agent: Googlebot
Allow: /files/*.css
Allow: /files/*.js
Sitemap: https://$DOMAIN/sitemap.xml
EOF
}

add_xml() {
  SITE_PATH=$1
  DOMAIN=$2
  DATE=$(date "+%F")
  cat <<EOF > "$SITE_PATH/public/sitemap.xml"
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url>
<loc>https://$DOMAIN/</loc>
<lastmod>$DATE</lastmod>
<priority>1.0</priority>
</url>
</urlset>
EOF
}

add_toml(){
  SITE_PATH=$1
  DOMAIN=$2
  TEMPLATE_NAME=$3
  cat <<EOF > "$SITE_PATH/hugo.toml"
baseURL = "https://$DOMAIN/"
languageCode = "ru"
title = ''
theme = "$TEMPLATE_NAME"
enableRobotsTXT = true

defaultContentLanguage = "ru"

[languages]
  [languages.ru]
    languageName = "Russian"
    weight = 1

[taxonomies]

[params]
  author = "Casinous"
  logo = "/images/casinous/logo.png"
  description = ""

[markup]
  [markup.goldmark]
    [markup.goldmark.renderer]
      unsafe = true
EOF
}

conf_site() {
  SITE_PATH=$1
  DOMAIN=$2
  cat <<EOF > /etc/nginx/sites-enabled/$DOMAIN.site
server {
  server_name www.$DOMAIN;
  return      301 http://$DOMAIN\$request_uri;
}
server {
  if (\$bad_bot = '1') { return 403; }
  listen      80;
  server_name $DOMAIN;
  root        $SITE_PATH/public;
  index       index.html index.php;
  access_log  /var/log/nginx/$DOMAIN.access.log main buffer=512k flush=5s;
  error_log   /var/log/nginx/$DOMAIN.buzz.error.log error;
# BEGIN GO.PHP
  location ^~ /go/ {
    rewrite ^/go/(.*)\$ /go.php?prt=\$1 last;
  }
  location ^~ /next/ {
    rewrite ^/next/(.*)\$ /next.php?prt=\$1 last;
  }
# END GO.PHP
  location ^~ /images/ {
    alias /data/www/cdn-hub.buzz/images/;
    access_log off;
    log_not_found off;
    expires max;
    add_header Cache-Control "public, max-age=31536000, immutable";
    autoindex off;
    try_files \$uri =404;
  }
  location = /favicon.ico {
    access_log    off;
    log_not_found off;
  }
  location = /robots.txt {
    allow         all;
    access_log    off;
    log_not_found off;
  }
  location / {
    try_files \$uri \$uri/ =404;
  }
  location ~ \.php$ {
    try_files                 \$uri =404;
    include                   fastcgi_params;
    fastcgi_pass              127.0.0.1:9000;
    fastcgi_intercept_errors  on;
    fastcgi_param             SCRIPT_FILENAME \$request_filename;
  }
  location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf)\$ {
    access_log    off;
    expires       max;
    log_not_found off;
  }
}
EOF
}

echo "--- $(date): Запуск скрипта обработки сайтов ---"

if ! ls "$STAGING_DIR"/*_update >/dev/null 2>&1; then
    echo "Нет заявок для обработки (*_update папки не найдены)."
    exit 0
fi

for staging_dir in "$STAGING_DIR"/*_update; do

  DOMAIN=$(basename "$staging_dir" _update)
  SITE_PATH="$WWW_PATH/$DOMAIN"
  JSON_SOURCE="$staging_dir/placeholders.json"
  INDEX_SOURCE="$staging_dir/_index.md"
  CONTENT="$staging_dir/content"
  TEMPLATE_NAME="$(cat $staging_dir/template-name.md)"
  THEME_PATH="/home/svc_user/hugo-themes/$TEMPLATE_NAME"

  echo ""
  echo "--- Обрабатываем заявку для домена: $DOMAIN ---"

  if [ ! -f "$JSON_SOURCE" ]; then
    log_message "$DOMAIN" "failure" "Файл placeholders.json не найден в заявке."
    rm -rf "$FAILED_DIR/$(basename "$staging_dir")"
    mv -f "$staging_dir" "$FAILED_DIR/"
    continue
  fi

  if [ -d "$SITE_PATH" ]; then
    log_message "$DOMAIN" "info" "Папка сайта существует. Выполняем зачистку и пересоздание..."
    rm -rf "$SITE_PATH"/*
    rm -rf "$SITE_PATH"/.* 2>/dev/null
  else
    log_message "$DOMAIN" "info" "Сайт не найден. Начинаем процесс создания..."
  fi

  mkdir -p "$SITE_PATH"
  "$HUGO_BIN" new site "$SITE_PATH" --force
  cp -r "$THEME_PATH" "$SITE_PATH/themes/"
  mkdir -p "$SITE_PATH/data"
  cp "$JSON_SOURCE" "$SITE_PATH/data/placeholders.json"
  cp -r "$CONTENT" "$SITE_PATH/"
  add_toml "$SITE_PATH" "$DOMAIN" "$TEMPLATE_NAME"
  conf_site "$SITE_PATH" "$DOMAIN"
  sudo systemctl reload nginx

  if HUGO_ENV=production "$HUGO_BIN" -s "$SITE_PATH" -b "https://$DOMAIN"; then
    # add_xml "$SITE_PATH" "$DOMAIN"
    add_robots "$SITE_PATH" "$DOMAIN"
    set_perm "$SITE_PATH"
    log_message "$DOMAIN" "success" "Сайт успешно создан и собран."
    rm -rf  "$staging_dir"
  else
    log_message "$DOMAIN" "failure" "Ошибка сборки Hugo при создании."
    rm -rf "$FAILED_DIR/$(basename "$staging_dir")"
    mv "$staging_dir" "$FAILED_DIR/"
  fi

done

echo ""
echo "--- Скрипт завершил работу ---"
