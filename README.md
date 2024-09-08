# Agregator API

This project is a **News Aggregator API** built with Laravel, Docker, and Swagger for API documentation.

## Prerequisites

Ensure you have the following tools installed:

- **Docker**
- **Docker Compose**

## Project Setup

### Step 1: Clone the Repository

Clone the repository to your local machine:

```bash

# Clone using HTTPS  
git clone https://github.com/LeonardD93/agregator-api-docker.git  

# Or clone using SSH  
git clone git@github.com:LeonardD93/agregator-api-docker.git

```
Navigate to the project directory:

```bash

cd agregator-api-docker

```

### Step 2: Build and Start the Docker Containers

Build and run the Docker containers:

```
docker-compose build
docker-compose up -d
```

Services started by these commands:

- **PHP-FPM**: Runs Laravel.
- **Nginx**: Serves the Laravel application.
- **MySQL**: Database service.
- **Mailhog** For email testing.
- **Scheduler**:  Handles scheduled tasks.
- **Elasticsearch**: Search engine service.
- **PhpMyAdmin**: Web interface for MySQL (disable in production).

### Step 3: Configure the Application

1. **Copy the`.env` file** :
```
   cp agregator-api/.env.example agregator-api/.env
```

2. **Enter the Docker container**:
```
   docker-compose exec --user root app bash
```

3. **Navigate to the laravel directory**:
```
   cd agregator-api
```

4. **Install dependencies and generate an application key**:
```
   composer install
   php artisan key:generate 
```

### Step 4: Set File Permissions

n a new terminal, set proper permissions for the `agregator-api` directory:
```
sudo chown -R www-data:www-data agregator-api/
```

### Step 5: Access the Application

Visit the application in your browser at:

```
http://localhost:8080
```

If everything is set up correctly, the default Laravel welcome page should appear.

## API Documentation (Swagger)

To generate and view Swagger documentation:

### 1. Generate Swagger documentation:
```
docker-compose exec -w /var/www/agregator-api app php artisan l5-swagger:generate
```

### 2. Access the Swagger UI:
```
http://localhost:8080/api/documentation
```


## External API Tokens

Add external API tokens to the '.env' file:

- [News API](https://newsapi.org/docs/get-started)
- [The Guardian Open Platform](https://open-platform.theguardian.com/access/)
- [New York Times API](https://developer.nytimes.com/get-started)


## Additional Commands

### Running Tests

To run the tests:
```
docker-compose exec -w /var/www/agregator-api app php artisan test 
```

Alternatively, inside the container:

```
docker-compose exec app bash
cd agregator-api/
php artisan test
```
### Seeding the Database

If the database hasn't been seeded automatically, or for re-seeding:

```
docker-compose exec -w /var/www/agregator-api app php artisan app:fetch-news-articles
```

### Elasticsearch Reindexing

If Elasticsearch indexing fails, reindex articles with:

```
docker-compose exec -w /var/www/agregator-api app php artisan app:reindex-elasticsearch-article
```

Check the indexed articles:
```
http://localhost:9200/_search?size=10000
```

### Mailhog

Access Mailhog at:
```
http://localhost:8025/
```

### PhpMyAdmin

PhpMyAdmin is accessible at:
```
http://localhost:8081/ 
```
By default, the credentials are the same as those in the '.env.example' file

### Troubleshooting

If you encounter file permission issues, reset them using:
```
sudo chown -R www-data:www-data agregator-api/
```