FROM nginx:1.19

ENV TZ=Asia/Shanghai
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /etc/nginx/ssl
##正式服服配置
COPY  ./ssl/*.cangmishu.com.key  /etc/nginx/ssl
COPY  ./ssl/fullchain.cer  /etc/nginx/ssl
