FROM php:5.6-cli

RUN docker-php-ext-install pdo pdo_mysql

RUN apt-get update && \
  apt-get install -y git zip unzip ssmtp && \
  apt-get clean

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ADD . /opt/serwer

RUN cd /opt/serwer && composer install --prefer-dist --no-interaction

RUN chmod -R 700 /opt/serwer
RUN chmod -R 777 /opt/serwer/logs

ENV DB_HOST clients-invoices-mysql
ENV DB_USER root
ENV DB_PASSWORD secret
ENV DB_NAME clients
ENV DIR_CODE /opt/serwer
ENV SMTP_USERNAME clientinvoicestest@gmail.com
ENV SMTP_PASSWORD secret12345

EXPOSE 80

CMD ["php", "/opt/serwer/import.php", "users.csv", "invoices.csv"]