# Symfony Companies Management API

This is a REST API application built with Symfony 8.0 and PHP 8.4, designed to manage Companies, Employees, Projects, and Users. It uses JWT for authentication and runs in a Docker environment.

## 🚀 Tech Stack

- **PHP**: 8.4
- **Framework**: Symfony 8.0
- **Database**: MySQL 8.0.32
- **Web Server**: Nginx
- **Authentication**: Lexik JWT Authentication Bundle
- **ORM**: Doctrine ORM
- **Containerization**: Docker & Docker Compose

## 🛠 Prerequisites

- Docker
- Docker Compose

## 📦 Installation & Setup

1. **Clone the repository:**
    ```bash
    git clone https://github.com/StanislavHlukhanych/symfony_test_task.git
    cd symfony_test_task
    ```

2. **Start the Docker containers:**
    ```bash
    docker compose up -d
    ```

3. **Install PHP dependencies:**
    ```bash
    docker compose exec php composer install
    ```

4. **Set up the database:**
    ```bash
    docker compose exec php bin/console doctrine:database:create
    docker compose exec php bin/console doctrine:migrations:migrate
    ```

5. **Load fixtures (dummy data):**
    ```bash
    docker compose exec php bin/console doctrine:fixtures:load
    ```

6. **Generate JWT keys (if not present):**
    The project includes JWT keys in `config/jwt/`, but if you need to regenerate them:
    ```bash
    docker compose exec php bin/console lexik:jwt:generate-keypair
    ```

## 🔑 Authentication

The API uses JWT (JSON Web Token) for authentication.

### 1. Register a new user
**Endpoint:** `POST /api/register`
**Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

### 2. Login to get a token
**Endpoint:** `POST /api/login_check`
**Body:**
```json
{
  "username": "user@example.com",
  "password": "password123"
}
```
**Response:**
```json
{
    "status": "success",
    "data": {
        "token": "eyJ0eXAi..."
    }
}
```

### 3. Access protected routes
Include the token in the `Authorization` header of your requests:
```
Authorization: Bearer <your_token>
```

## 📡 API Endpoints

All endpoints below require authentication.

### Companies
- `GET /api/companies` - List all companies
- `POST /api/companies` - Create a company
- `GET /api/companies/{id}` - Get a specific company
- `PUT /api/companies/{id}` - Update a company
- `DELETE /api/companies/{id}` - Delete a company

### Employees
- `GET /api/employees` - List all employees
- `POST /api/employees` - Create an employee
- `GET /api/employees/{id}` - Get a specific employee
- `PUT /api/employees/{id}` - Update an employee
- `DELETE /api/employees/{id}` - Delete an employee

### Projects
- `GET /api/projects` - List all projects
- `POST /api/projects` - Create a project
- `GET /api/projects/{id}` - Get a specific project
- `PUT /api/projects/{id}` - Update a project
- `DELETE /api/projects/{id}` - Delete a project
