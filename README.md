# Ticket v. E 1.0

Version: 1.0

## Description
Ticket v. E 1.0 is a robust and scalable ticketing system built with Laravel. It provides comprehensive user management, ticket tracking, and notification features.

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/elkingphp/Ticket-v-E-1.0.git
    cd Ticket-v-E-1.0
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    npm run build
    ```

3.  **Environment Setup:**
    By default, the `.env` file is not included. Copy `.env.example` to `.env`:
    ```bash
    cp .env.example .env
    ```
    Then generate the application key:
    ```bash
    php artisan key:generate
    ```

4.  **Database Migration:**
    Configure your database settings in `.env` and run migrations:
    ```bash
    php artisan migrate --seed
    ```

## Environment Requirements

-   PHP >= 8.1
-   Composer
-   Node.js & NPM
-   MySQL or compatible database
-   Web server (Nginx/Apache) or Laravel Valet/Sail

## License
MIT License. See LICENSE file for details.
