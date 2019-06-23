FROM nginx:1.11

ENV TZ=Asia/Shanghai
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /etc/nginx/ssl

COPY ./ssl/fullchain.cer  /etc/nginx/ssl
COPY ./ssl/*.cangmishu.com.key /etc/nginx/ssl
