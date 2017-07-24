FROM php:7-cli
COPY . /usr/src/kuaidi100
WORKDIR /usr/src/kuaidi100
ENV TZ "Asia/Shanghai"
RUN docker-php-ext-install mysqli
CMD [ "php", "./worker.php" ]
