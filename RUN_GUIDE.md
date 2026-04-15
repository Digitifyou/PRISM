# SMM Automation - Setup & Run Guide

This document outlines the steps required to set up and run the SMM Automation project on your local machine.

## Prerequisites

Before you begin, ensure you have the following installed on your system:
- **PHP** (v8.2 or higher recommended)
- **Composer** (PHP dependency manager)
- **Node.js** & **NPM** (Javascript package manager)
- **MySQL** Server (XAMPP/MAMP/WAMP or native installation)

## 1. Initial Setup

1. **Install PHP Dependencies**:
   Navigate to the project root and run:
   ```bash
   composer install
   ```

2. **Install Node/Frontend Dependencies**:
   ```bash
   npm install
   ```

3. **Environment Configuration**:
   The `.env` file is already present in your project. For fresh clone installations, copy `.env.example` to `.env`.
   Generate the app key if not already generated:
   ```bash
   php artisan key:generate
   ```

## 2. Database Configuration

1. Make sure your MySQL server is running.
2. In your `.env` file, ensure the database settings match your local MySQL configuration:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=smm_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
3. Create an empty database named `smm_db` in your MySQL server.
4. **Run Migrations & Seeders** to create the tables in your database:
   ```bash
   php artisan migrate --seed
   ```

## 3. Running the Application

To fully run the application, you need to start several dependent processes. **Open multiple terminal windows/tabs** in the `SMM Automation` directory.

### Terminal 1: Laravel Backend Server
Start the local PHP server:
```bash
php artisan serve
```
*(The app will be accessible at http://127.0.0.1:8000)*

### Terminal 2: Vite Frontend Server
If you are modifying templates or doing development, compiling CSS/JS via Vite is necessary:
```bash
npm run dev
```

### Terminal 3: Queue Worker
Since the project uses automated jobs for social media posting based on AI (`QUEUE_CONNECTION=database` in your `.env`), you need to keep a queue worker running in the background so it can process those tasks:
```bash
php artisan queue:work
```

## Additional Notes

- **AI Keys**: Ensure that your AI API keys (`GEMINI_API_KEY`, etc.) and Social Media keys (`TAVILY_API_KEY`, etc.) are correctly filled out in the `.env` file for all automations to run.
- **Cache**: If you encounter issues with old configurations, clear all caches with `php artisan optimize:clear`
