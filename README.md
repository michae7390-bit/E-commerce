# E-commerce App

An ecommerce application built with Laravel.

## Overview

This is a full-featured ecommerce platform developed using the Laravel framework, combined with Blade templating for a seamless user experience.

## Technology Stack

- **Backend**: PHP (90.1%)
- **Templating**: Blade (9.9%)
- **Framework**: Laravel

## Features

- Product catalog and management
- Shopping cart functionality
- Secure checkout process
- Order management
- User authentication and profiles
- Responsive design

## Installation

### Prerequisites

- PHP 8.0 or higher
- Composer
- Laravel CLI
- MySQL or compatible database

### Setup

1. Clone the repository:
```bash
git clone https://github.com/michae7390-bit/E-commerce.git
cd E-commerce
```

2. Install dependencies:
```bash
composer install
```

3. Create a `.env` file:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure your database in `.env` and run migrations:
```bash
php artisan migrate
```

5. Start the development server:
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Project Structure

- `/app` - Application logic and models
- `/resources/views` - Blade template files
- `/routes` - Application routes
- `/database` - Database migrations and seeders
- `/public` - Publicly accessible assets

## Usage

Once installed and running, you can:
- Browse the product catalog
- Add items to your cart
- Complete purchases through the checkout
- Manage your account and order history

## Development

To contribute or modify the application:

1. Create a feature branch
2. Make your changes
3. Test thoroughly
4. Submit a pull request

## License

This project is open source and available under the MIT License.

## Support

For questions or issues, please open an issue on the GitHub repository.
