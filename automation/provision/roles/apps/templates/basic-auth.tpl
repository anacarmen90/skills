# Setup basic auth for the applications
satisfy any;

allow 127.0.0.1;
allow 89.38.134.100/32;
allow 192.168.1.1/16;
deny  all;

auth_basic           "{{ app.key }}";
auth_basic_user_file conf.d/htpasswd-{{ app.key }}-password;
