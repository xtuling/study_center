FROM nginx:latest

ARG local_host_ip

ADD nginx.conf /etc/nginx/nginx.conf
ADD fastcgi.conf /etc/nginx/fastcgi.conf
ADD sites-enabled/* /etc/nginx/conf.d/
RUN mkdir -p /cyphp/log && mkdir -p /cyphp/log/nginx
RUN chown -R www-data.www-data /cyphp

EXPOSE 80
VOLUME ["/cyphp"]
