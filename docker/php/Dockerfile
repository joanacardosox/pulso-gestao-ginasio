FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Sessões mais seguras
RUN { \
      echo "session.cookie_httponly=1"; \
      echo "session.cookie_samesite=Lax"; \
      echo "session.use_strict_mode=1"; \
      echo "display_errors=Off"; \
      echo "log_errors=On"; \
    } > /usr/local/etc/php/conf.d/pulso.ini
