# Procurement Portal (Laravel 12 + PHP 8 + MySQL)

A Laravel 12 implementation of the procurement workflow based on the uploaded HTML design and ERD-driven database.

## What is included

- Authentication: login, register, logout
- Role-based dashboards for requesters and purchasing/admin
- Purchase request form with dynamic item details
- RFQ creation flow and vendor selection
- Quotation status management and final quotation form
- MySQL-ready migrations for users, purchase requests, RFQs, vendors, and quotations

## Setup

1. Copy `.env.example` to `.env` if necessary.
2. Configure MySQL settings in `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=internship_website`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=`
3. Create the database manually if needed:
   - `CREATE DATABASE internship_website;`
4. Install dependencies:
   - `composer install`
5. Generate the application key:
   - `php artisan key:generate --force`
6. Run migrations:
   - `php artisan migrate`
7. Seed the default users and vendors:
   - `php artisan db:seed`

## Default accounts

- Admin / Purchasing: `admin@purchasing.local` / `password`
- Requester 1: `requester@company.local` / `password`
- Requester 2: `john.smith@company.local` / `password`
- Requester 3: `sarah.johnson@company.local` / `password`

## Main routes

- `/login`
- `/register`
- `/dashboard`
- `/purchase-request/create`
- `/rfq/create`
- `/vendor/select/{rfq}`
- `/quotation/status/{rfq}`
- `/quotation/final/{rfq}`

## Notes

- The current `.env` is configured for MySQL.
- The database connection must be available before running migrations.
- Use the dashboard page after login to navigate the procurement workflow.
