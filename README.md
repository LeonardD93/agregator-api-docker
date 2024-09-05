# Agregator API

This project is a **News Aggregator API** built with Laravel, Docker, and Swagger for API documentation.

## Prerequisites

Make sure you have the following installed on your system:

- **Docker**
- **Docker Compose**

## Project Setup

### Step 1: Clone the Repository

First, clone the repository to your local machine using one of the following commands:

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

Use Docker Compose to build and start the application containers:

```
docker-compose up -d
```

This will start the following services:

- **PHP-FPM**: Runs Laravel.
- **Nginx**: Serves the Laravel application.
- **MySQL**: Database service.

### Step 3: Configure the Application

1. **Create the `.env` file** by copying the example `.env.example`:
```
   cp agregator-api/.env.example agregator-api/.env
```

2. **Enter the Docker container as the root user**:
```
   docker-compose exec --user root app bash
```

3. **Navigate to the laravel directory**:
```
   cd agregator-api
```

4. **Generate the application key and install dependencies**:
```
   composer install
   php artisan key:generate 
```

### Step 4: Set File Permissions

Open a new terminal and adjust the permissions for the `agregator-api` directory:
```
sudo chown -R www-data:www-data agregator-api/
```

### Step 5: Access the Application

Once the containers are running and the permissions are set, you can access the Laravel application in your browser:

http://localhost:8080

If everything is set up correctly, you should see the default Laravel welcome page.

<!-- ## Swagger API Documentation

This project includes Swagger for API documentation. You can access the Swagger UI by visiting:

http://localhost:8080/api/documentation

To regenerate the Swagger documentation after changes:
```
docker-compose exec app php artisan l5-swagger:generate 
```
-->

## Additional Commands

### Running Tests

To run the tests defined in the project:
```
docker-compose exec app php artisan test
```

### Troubleshooting

If you encounter file permission issues, you may need to reset the file permissions:

```
sudo chown -R www-data:www-data agregator-api/
```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.