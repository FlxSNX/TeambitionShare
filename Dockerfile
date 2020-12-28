FROM php:7.0-apache

RUN apt-get update && \
    apt-get clean

RUN a2enmod rewrite

COPY ./ /var/www/html/

