FROM nginx:1.11

ENV TZ=Asia/Shanghai
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /etc/nginx/ssl
##正式服服配置
COPY  ./ssl/fullchain1.pem  /etc/nginx/ssl
COPY  ./ssl/privkey1.pem  /etc/nginx/ssl


##开发服配置
COPY ./ssl/fullchain.cer  /etc/nginx/ssl
COPY ./ssl/*.cangmishu.com.key /etc/nginx/ssl