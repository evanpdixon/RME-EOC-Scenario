FROM wordpress:latest

# Install composer and TCPDF
RUN apt-get update && apt-get install -y unzip && rm -rf /var/lib/apt/lists/*
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install TCPDF into the plugin
WORKDIR /var/www/html/wp-content/plugins/rme-eoc-scenario
RUN composer require tecnickcom/tcpdf --no-interaction 2>/dev/null || true

WORKDIR /var/www/html
