# 🚀 Starter Kit REST API CodeIgniter 4

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D%208.1-777BB4.svg?logo=php&logoColor=white)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.0-EF4223.svg?logo=codeigniter&logoColor=white)](https://codeigniter.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg?logo=docker&logoColor=white)](https://www.docker.com/)

A robust, production-ready **RESTful API Boilerplate** built with **CodeIgniter 4**.
This project moves beyond the basic MVC structure, implementing a **Service Layer Architecture** to ensure scalability, maintainability, and clean code principles similar to robust Node.js/Java architectures.

---

## ✨ Features

- **🧠 Service Layer Architecture**: Business logic is separated from Controllers using Services.
- **🛡️ Clean Data Entities**: Uses CI4 `Entities` for clean object-oriented data handling.
- **🔒 JWT Authentication**: Secure, stateless authentication using `firebase/php-jwt`.
- **🚦 Robust Middleware**:
    - `JwtAuth`: Bearer token verification.
    - `RoleCheck`: Role-based Access Control (RBAC).
    - `RateLimiter`: Built-in throttling to prevent abuse.
    - `Cors`: Configurable Cross-Origin Resource Sharing.
- **🗄️ Database Agnostic**: Compatible with **MySQL** and **SQLite** using Migrations.
- **🐳 Docker Ready**: Full containerization support with persistent volumes.
- **📝 API Documentation**: Integrated **Swagger UI** (OpenAPI 3.0).
- **🧪 Automated Testing**: Includes Python-based API test scripts.
- **✨ Best Practices**: Input validation, centralized error handling, and standardized JSON responses.

---

## 🛠️ Project Structure

We follow a strict separation of concerns:

```text
app/
├── Config/             # App Configuration (Routes, Auth, Database)
├── Controllers/        # HTTP Layer: Handles Inputs, Validation, and Responses
├── Entities/           # Data Layer: Represents Database Rows as Objects
├── Filters/            # Middleware: JWT Auth, Roles, CORS, Rate Limits
├── Helpers/            # Utilities: Standardized JSON Response Helper
├── Models/             # Database Access: Query Builder & Connection
├── Services/           # Business Logic: The "Brain" of the application
├── Validation/         # Custom Validation Rules
└── Views/              # Swagger UI HTML
public/                 # Web Root (Index & Swagger YAML)
```

---

## 🚀 Getting Started (Local Development)

**Recommended:** Run the project locally first to understand the flow before containerizing.

### Prerequisites
- PHP >= 8.1
- Composer
- Python 3.x (for testing scripts)

### 1. Installation

```bash
# Clone the repository
git clone https://github.com/mnabielap/starter-kit-restapi-codeigniter.git
cd starter-kit-restapi-codeigniter

# Install PHP dependencies
composer install
```

### 2. Environment Setup

Copy the example environment file. By default, it is configured for **SQLite** (zero config).

```bash
cp env .env
```
*Note: If you want to use MySQL locally, edit `.env` and change `database.default.DBDriver` to `MySQLi`.*

### 3. Database Setup

Run the migrations to create tables and the seeder to create the initial Admin account.

```bash
# Create tables
php spark migrate

# Seed Admin User (Email: admin@example.com / Pass: password123)
php spark db:seed AdminSeeder
```

### 4. Run Server

```bash
php spark serve
```
The API is now running at `http://localhost:8080`.

---

## 🐳 Running with Docker (Production/Staging)

This project separates the Application and Database into two containers communicating via a custom network.

### 1. Preparation

Create the network and persistent volumes so your data survives container restarts.

```bash
# Create Network
docker network create restapi_codeigniter_network

# Create Volumes
docker volume create restapi_codeigniter_db_volume
docker volume create restapi_codeigniter_media_volume
```

### 2. Configure Environment

Create a `.env.docker` file. **Ensure there are no spaces around the `=` sign.**

```ini
# .env.docker
CI_ENVIRONMENT=production
app.baseURL=http://localhost:5005

# Database Config (Connects to MySQL container)
database.default.hostname=restapi-codeigniter-mysql
database.default.database=starter_kit_db
database.default.username=user
database.default.password=userpassword
database.default.DBDriver=MySQLi
database.default.port=3306

# Security
JWT_SECRET=complex_secret_key_here
```

### 3. Start Database Container (MySQL)

```bash
docker run -d \
  --name restapi-codeigniter-mysql \
  --network restapi_codeigniter_network \
  -e MYSQL_ROOT_PASSWORD=rootpassword \
  -e MYSQL_DATABASE=starter_kit_db \
  -e MYSQL_USER=user \
  -e MYSQL_PASSWORD=userpassword \
  -v restapi_codeigniter_db_volume:/var/lib/mysql \
  mysql:8.0
```

### 4. Build & Run App Container

This container runs on port **5005**. It automatically waits for the database to be ready and runs migrations on startup.

```bash
# Build Image
docker build -t restapi-codeigniter-app .

# Run Container
docker run -d -p 5005:5005 \
  --env-file .env.docker \
  --network restapi_codeigniter_network \
  -v restapi_codeigniter_db_volume:/var/www/html/writable \
  -v restapi_codeigniter_media_volume:/var/www/html/writable/uploads \
  --name restapi-codeigniter-container \
  restapi-codeigniter-app
```

### 5. Initialize Admin User
Since the MySQL database is new, create the admin user:

```bash
docker exec -it restapi-codeigniter-container php spark db:seed AdminSeeder
```

The API is now accessible at `http://localhost:5005`.

---

## 🕹️ Docker Management Cheat Sheet

Useful commands for managing your containers.

#### View Logs
Check application logs (migrations, errors, access logs).
```bash
docker logs -f restapi-codeigniter-container
```

#### Stop Container
```bash
docker stop restapi-codeigniter-container
```

#### Restart Container
```bash
docker start restapi-codeigniter-container
```

#### Remove Container
Must be stopped first.
```bash
docker rm restapi-codeigniter-container
```

#### Manage Volumes
```bash
# List volumes
docker volume ls

# ⚠️ DELETE VOLUME (PERMANENT DATA LOSS)
docker volume rm restapi_codeigniter_db_volume
```

---

## 🧪 Automated API Testing

Forget Postman! We have included a suite of Python scripts to test every endpoint in a real-world scenario.

### Setup
1. Open `api_tests/utils.py`.
2. Ensure `BASE_URL` matches your running server (e.g., `http://localhost:8080` or `http://localhost:5005`).

### Running Tests
Execute the scripts in order. Tokens are automatically saved to `api_tests/secrets.json` and reused by subsequent scripts.

```bash
# 1. Register a new user
python api_tests/A1.auth_register.py

# 2. Login as Admin (required for User CRUD)
python api_tests/A2.auth_login.py

# 3. Create a User via Admin
python api_tests/B1.users_create.py

# 4. Get All Users
python api_tests/B2.users_get_all.py
```

---

## 📚 API Documentation

Interactive documentation is available via **Swagger UI**.

*   **Local URL:** `http://localhost:8080/v1/docs`
*   **Docker URL:** `http://localhost:5005/v1/docs`

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.