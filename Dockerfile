# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libsqlite3-dev \
    unzip \
    git \
    zip \
    libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql pdo_sqlite mysqli zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# CHANGE APACHE PORT TO 5005
RUN sed -i 's/Listen 80/Listen 5005/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:5005/' /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Configure Apache DocumentRoot to point to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# --- FORCE ALLOW OVERRIDE ALL ---
RUN echo "<Directory /var/www/html/public>" >> /etc/apache2/apache2.conf && \
    echo "    Options Indexes FollowSymLinks" >> /etc/apache2/apache2.conf && \
    echo "    AllowOverride All" >> /etc/apache2/apache2.conf && \
    echo "    Require all granted" >> /etc/apache2/apache2.conf && \
    echo "</Directory>" >> /etc/apache2/apache2.conf

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 5005
EXPOSE 5005

# Use entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]