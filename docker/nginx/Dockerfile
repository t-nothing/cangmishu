FROM nginx:1.11

ENV TZ=Asia/Shanghai
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /etc/nginx/ssl

COPY ./ssl/nle-tech.com_bundle.crt /etc/nginx/ssl
COPY ./ssl/nle-tech.com.key /etc/nginx/ssl
