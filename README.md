# Task Management System - Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [System Requirements](#system-requirements)
3. [Installation Guide](#installation-guide)
4. [API Endpoints](#api-endpoints)
5. [Database Schema](#database-schema)

## Project Overview
A RESTful API for managing tasks with user authentication, task dependencies, and status tracking.

## System Requirements
- PHP 8.0+
- Composer
- MySQL 5.7+
- Laravel 11.x

## Installation Guide

1. **Clone the repository**
   ```bash
   git clone https://github.com/EsraaEissa123/task-management-api-softxpert.git
   cd task-management-system
   ```
2. **Install dependencies**
   ```bash
   composer install
   ```
3. **Configure environment**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` file with your database credentials.
4. **Generate application key**
   ```bash
   php artisan key:generate
   ```
5. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```
6. **Start development server**
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`

## API Endpoints

### Authentication
| Method | Endpoint       | Description       |
|--------|--------------|------------------|
| POST   | `/api/register` | Register new user |
| POST   | `/api/login`    | User login       |
| POST   | `/api/logout`   | User logout      |

### Users
| Method | Endpoint        | Description       |
|--------|----------------|------------------|
| GET    | `/api/users`     | List all users   |
| GET    | `/api/users/{id}` | Get specific user |
| PUT    | `/api/users/{id}` | Update user      |
| DELETE | `/api/users/{id}` | Delete user      |

### Tasks
| Method | Endpoint        | Description       |
|--------|----------------|------------------|
| GET    | `/api/tasks`     | List all tasks   |
| POST   | `/api/tasks`     | Create new task  |
| GET    | `/api/tasks/{id}` | Get specific task |
| PUT    | `/api/tasks/{id}` | Update task      |
| DELETE | `/api/tasks/{id}` | Delete task      |

### Task Dependencies
| Method | Endpoint        | Description       |
|--------|----------------|------------------|
| POST   | `/api/tasks/{id}/dependencies` | Add dependency to task |
| DELETE | `/api/tasks/{id}/dependencies/{depId}` | Remove dependency from task |

## Database Schema
(Include an entity relationship diagram or table structures here.)
