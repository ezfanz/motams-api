
# MOTAMS Project

## Table of Contents
1. [Introduction](#introduction)
2. [Prerequisites](#prerequisites)
3. [Setup](#setup)
4. [Environment Configuration](#environment-configuration)
5. [Database Migrations and Seeders](#database-migrations-and-seeders)
6. [Authentication Configuration (JWT)](#authentication-configuration-jwt)
7. [Running the Application](#running-the-application)
8. [API Documentation](#api-documentation)

---

### Introduction

**MOTAMS** is a Laravel-based application for managing attendance, leave requests, complaints, and other workflows within an organization. This project includes roles, permissions, and JWT authentication.

### Prerequisites

- **PHP** >= 8.1
- **Composer** >= 2.0
- **MySQL** or **PostgreSQL** (or other databases supported by Laravel)
- **Node.js** and **npm** (for frontend packages, if applicable)

### Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/motams.git
   cd motams
   ```

2. **Install Composer dependencies:**
   ```bash
   composer install
   ```

3. **Install NPM dependencies** (if frontend assets are included):
   ```bash
   npm install && npm run dev
   ```

### Environment Configuration

1. **Copy the example `.env` file:**
   ```bash
   cp .env.example .env
   ```

2. **Set up the database:**
   - Open the `.env` file and configure your database connection:
     ```dotenv
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=your_database_name
     DB_USERNAME=your_database_user
     DB_PASSWORD=your_database_password
     ```

3. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

### Database Migrations and Seeders

1. **Run the migrations** to set up the database structure:
   ```bash
   php artisan migrate
   ```

2. **Run the seeders** to populate the database with initial data (roles, permissions, statuses, etc.):
   ```bash
   php artisan db:seed
   ```

### Authentication Configuration (JWT)

This project uses JSON Web Tokens (JWT) for authentication.

1. **Generate a JWT secret key:**
   ```bash
   php artisan jwt:secret
   ```

2. **Add JWT settings to `.env`:**
   - Open the `.env` file and ensure the following JWT configurations:
     ```dotenv
     JWT_SECRET=your_generated_secret
     JWT_TTL=60
     ```

### Running the Application

1. **Start the local development server:**
   ```bash
   php artisan serve
   ```

2. **Access the application:**
   By default, the application will be available at `http://127.0.0.1:8000`.

### API Documentation

Below are some key API endpoints:

#### 1. **Authentication**
   - **Login**: `POST /v1/auth/login`
   - **Register**: `POST /v1/auth/register`

#### 2. **Attendance**
   - **View Attendance Records**: `GET /v1/attendance-records`
   - **Create Attendance Record**: `POST /v1/attendance-records`

#### 3. **Leave Requests**
   - **Create Leave Request**: `POST /v1/office-leave-requests`
   - **List Leave Types**: `GET /v1/leave-types`

#### 4. **Complaints**
   - **Submit Complaint**: `POST /v1/complaints`
   - **List Complaint Types**: `GET /v1/complaints/types`

For further API details, please refer to the Bruno Api Repo [API Documentation](https://github.com/ezfanz/motams-bruno) section.

---

### Troubleshooting

1. **JWT Errors**: If JWT tokens are not working, ensure you have set up the JWT secret in `.env` using `php artisan jwt:secret`.
2. **Database Connection Issues**: Double-check the `.env` file for correct database credentials.

### Additional Notes

- **Role and Permission Setup**: The seeder sets up default roles (`Semakan`, `Pengesahan`, etc.) with permissions (`review`, `approve`).
- **Frontend Integration**: If using a frontend framework, ensure it can handle JWT tokens and integrates with Laravel’s API properly.

---

Enjoy working with the MOTAMS project! For any issues or further assistance, refer to the official [Laravel Documentation](https://laravel.com/docs) or contact the project maintainer.
