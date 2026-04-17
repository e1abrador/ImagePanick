FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Build dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    wget \
    pkg-config \
    libpng-dev \
    libjpeg-dev \
    libtiff-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    apache2 \
    php \
    libapache2-mod-php \
    && rm -rf /var/lib/apt/lists/*

# Install Ghostscript 10.06.0 from source
RUN cd /tmp && \
    wget -q https://github.com/ArtifexSoftware/ghostpdl-downloads/releases/download/gs10060/ghostscript-10.06.0.tar.gz && \
    tar xzf ghostscript-10.06.0.tar.gz && \
    cd ghostscript-10.06.0 && \
    ./configure --prefix=/usr/local && \
    make -j$(nproc) && \
    make install && \
    ldconfig && \
    rm -rf /tmp/ghostscript*

# Install ImageMagick 7.1.2-13 from source
RUN cd /tmp && \
    wget -q https://github.com/ImageMagick/ImageMagick/archive/refs/tags/7.1.2-13.tar.gz && \
    tar xzf 7.1.2-13.tar.gz && \
    cd ImageMagick-7.1.2-13 && \
    ./configure --prefix=/usr/local --with-gs-font-dir=/usr/local/share/ghostscript/fonts && \
    make -j$(nproc) && \
    make install && \
    ldconfig && \
    rm -rf /tmp/ImageMagick* /tmp/7.1.2-13.tar.gz

# Configure Apache
RUN a2enmod rewrite

# Deploy web app
COPY index.php /var/www/html/index.php
COPY upload.php /var/www/html/upload.php
RUN rm -f /var/www/html/index.html && \
    chown -R www-data:www-data /var/www/html && \
    chmod 777 /var/www/html

EXPOSE 80

CMD ["apachectl", "-D", "FOREGROUND"]
