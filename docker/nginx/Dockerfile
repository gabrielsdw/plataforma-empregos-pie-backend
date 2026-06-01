FROM nginx:alpine

WORKDIR /var/www

RUN rm /etc/nginx/conf.d/default.conf

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

COPY . /var/www
# Expõe a porta 80
EXPOSE 80 443

# Comando padrão do nginx
CMD ["nginx", "-g", "daemon off;"]