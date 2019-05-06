server {
    server_name {{ app.value.servername }};

    listen 80;

{% if letsencrypt is defined and letsencrypt.apps[app.key] is defined %}
    root {{ letsencrypt.webroot_path }};

    # Add a separate location section to redirect calls for certificate renewal
    location ~ /.well-known {
        allow all;
    }
{% endif %}

    # Redirect all other locations to the HTTPS version
    location / {
        return 301 https://$host$request_uri;
    }
}

server {
    server_name {{ app.value.servername }};

    listen 443 ssl http2;
    listen [::]:443 ssl http2;

    # Setup the project root
    root {{ app.value.public }};

    # Setup error logging
    error_log /var/log/nginx/{{ app.key }}_error.log;
    access_log /var/log/nginx/{{ app.key }}_access.log;

    # Setup SSL
    ssl on;
    ssl_certificate {{ app.value.certificate_path }};
    ssl_certificate_key {{ app.value.certificate_key_path }};
    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers "ECDHE-RSA-AES256-GCM-SHA384:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA256:ECDHE-RSA-AES256-SHA:ECDHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES128-SHA256:DHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES256-GCM-SHA384:AES128-GCM-SHA256:AES256-SHA256:AES128-SHA256:AES256-SHA:AES128-SHA:DES-CBC3-SHA:HIGH:!aNULL:!eNULL:!EXPORT:!DES:!MD5:!PSK:!RC4";
    ssl_prefer_server_ciphers on;

{% if app.value.htpasswd is defined %}
    # Include basic authentication configuration
    include conf.d/htpasswd-{{ app.key }};
{% endif %}

    # Include security settings
    include conf.d/security.conf;

    # Try to serve files directly, redirect to backend if we cannot find them
    location / {
         try_files $uri @backend;
    }

    # The @backend location gets redirected to the entrypoint
    location @backend {
         rewrite ^(.*)$ /app_dev.php/$1 last;
    }

    # DEV
    location ~ ^/(app_dev|config)\.php(/|$) {
        fastcgi_pass {{ app.value.fastcgi_pass }};
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
