# Use the official PHP image as the base image
FROM php:8.1-apache

# Install any necessary packages (none for MongoDB or MySQL now)
RUN apt-get update && \
    apt-get install -y libcurl4-openssl-dev pkg-config libssl-dev

# Copy the current directory contents into the container
COPY . /var/www/html/

# Expose port 80 to the Docker host
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
