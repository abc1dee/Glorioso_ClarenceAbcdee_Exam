# PurpleBug Internship Exam - E-Commerce Web Application

## Introduction
Welcome to my PurpleBug Internship Exam project! This is a full-stack **E-commerce web application** showcasing both Customer and Administrator functionalities. It is built natively on a **Laravel 11** backend API and a **Vue 3** frontend environment. 

The application strictly complies with exam requirements, including secure authentication, timed session locks (after 5 failed logins), and automatic inactivity timeouts, alongside comprehensive CRUD product management and a dynamic shopping cart.

---

## Tools & Technologies Used
### Backend (API)
* **PHP 8+** (Runs via XAMPP)
* **Laravel 11** (Framework)
* **Laravel Sanctum** (For secure SPA/API token authentication)
* **MySQL** (Database via XAMPP phpMyAdmin)

### Frontend (SPA)
* **Vue 3** (Composition API)
* **Vite** (Build tool and local dev server)
* **Tailwind CSS v3** (Utility-first styling framework)
* **Pinia** (State management for Authentication and Cart)
* **Vue Router** (Client-side routing)
* **Axios** (Making requests to the Laravel API)
* **Lucide Vue Next** (Clean vector icon set)

---

## Features
### 1. Security & Authentication (Exam Requirements)
* **Role-Based Access**: Distinguishes between `Admin` and `Guest/User`.
* **Failed Login Lockout**: Automatically locks a user's account for 5 minutes after 5 consecutive failed login attempts.
* **Inactivity Kick**: Users are securely logged out (tokens revoked) if inactive for a certain period of time (configured via the custom `CheckInactivity.php` middleware).
* **Route Protection**: Certain pages are masked. E.g., Guests can browse products but must log in to add items to their cart.

### 2. Admin Dashboard
* **Product Management**: Full CRUD capability perfectly matching the design mockups. Supports uploading locally stored product images.
* **User Management**: Administrators can add, edit, and safely delete users. Includes a toggle to manually suspend guest accounts via Active/Inactive statuses.
* **Orders Management**: Track user orders and seamlessly shift between statuses (Pending, Delivery, Delivered, Cancelled).
* **Activity Logs**: Integrated event tracking to record user activities.

### 3. Customer Storefront
* **Product Browsing**: Clean product landing view with searching and price sorting.
* **Shopping Cart**: Dynamic cart management explicitly keeping track of stock.
* **Checkout Flow**: Interactive "Thank You" modal upon successful purchase placement.

---

## Installation Guide

If you are evaluating this project on your local machine using Windows and **XAMPP**, follow these step-by-step instructions.

### Prerequisites
Make sure you have downloaded and installed the following:
1. **[XAMPP](https://www.apachefriends.org/index.html)** (For Apache and MySQL database)
2. **[Composer](https://getcomposer.org/)** (For managing Laravel PHP dependencies)
3. **[Node.js](https://nodejs.org/)** (For running Vite and Vue frontend dependencies)

### Step 1: Start XAMPP Server
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.
*(Make sure both are highlighted green and do not show port errors).*

### Step 2: Set Up the MySQL Database
1. In your browser, go to `http://localhost/phpmyadmin`
2. Click on the **Databases** tab.
3. In the "Database name" text field, type: `purplebug_exam` (or your preferred local DB name, but remember it).
4. Click **Create**.

### Step 3: Run the Laravel Backend (`purplebug-exam`)
Open a terminal (e.g., Command Prompt, PowerShell, or VS Code terminal) and navigate inside the **backend folder**:

```bash
cd purplebug-exam
```

1. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

2. **Environment Configuration**:
   * Find the file named `.env.example` inside the `purplebug-exam` folder.
   * Rename it to exactly `.env`
   * Open `.env` in a text editor and change the database values to correspond to XAMPP:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=purplebug_exam
     DB_USERNAME=root
     DB_PASSWORD=
     ```

3. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

4. **Migrate and Seed the Database**:
   * This command creates the tables (users, carts, products, etc.) and injects the default Admin account.
   ```bash
   php artisan migrate --seed
   ```

5. **Link File Storage (For Uploaded Images)**:
   ```bash
   php artisan storage:link
   ```

6. **Start the Laravel API Server**:
   ```bash
   php artisan serve
   ```
   *(Keep this terminal open. It runs typically at `http://localhost:8000`)*

### Step 4: Run the Vue Frontend (`purplebug-frontend`)
Open a **second, completely new terminal window** and navigate to the **frontend folder**:

```bash
cd purplebug-frontend
```

1. **Install JavaScript Dependencies**:
   ```bash
   npm install
   ```

2. **Start the Vite Frontend Server**:
   ```bash
   npm run dev
   ```
   *(The terminal will provide a local link, typically `http://localhost:5173`. Ctrl+Click this link to open the app!)*

---

## How to Access the Application

### Guest / Customer
* Simply open your Vite URL (e.g., `http://localhost:5173/`).
* You can instantly see the product catalog.
* To Add to Cart, click **LOGIN** or **SIGN UP** at the top right of the navbar.

### Administrator Accounts
Because we ran the database seeder above, a master Admin account has already been injected for evaluating the system. 

* **Email:** `admin@purplebug.com`
* **Password:** `Admin@1234`
*(Click LOGIN in the UI, enter these credentials, and you will be immediately routed to the private Admin Dashboard).*

---

## System Screenshots

### Customer Storefront

**Product Landing Page**
*(Product landing of the website)*
![Product Landing](ProductListing.png)

**Customer Cart (With Products)**
*(Customer's cart with products on it)*
![Cart with Products](Cart.png)

**Empty Cart (After Checkout)**
*(Customer's cart after placing order)*
![Empty Cart](CartEmpty.png)

**Checkout Success Modal**
*(Thank you popup modal)*
![Thank You Modal](Thankyou.png)

**Guest "My Orders" Modal**
*(Customer's order history and statuses)*
![My Orders](MyOrders.png)

### Authentication & Exam Security Requirements

**Registration Page**
*(Registration page of the site)*
![Register Page](Register.png)

**Unique Email Validation**
*(Verification showing that only unique emails can be registered)*
![Unique Email](UniqueEmail.png)

**Account Locked (5 Failed Attempts for 5 minutes)**
*(Verification showing account is locked for 5 mins)*
![Account Locked](AccountLocked.png)

**Inactivity Timeout (Session Expired)**
*(Screen where it shows session expired due to inactivity)*
![Inactivity Timeout](Inactivity.png)

### Admin Dashboard Access

**Products Management**
*(Admin's side of products management. Admin can see product name, price, number of stocks, and do actions onto it)*
![Products Management](ProductsManagement.png)

**Admin Adding a Product**
*(When admin is adding a product)*
![Add Product](AddProduct.png)

**Orders Dashboard (List of Orders overview)**
*(Admin's dashboard of orders showing the list of orders with corresponding full name, products, status, and actions)*
![Orders Dashboard](Orders2.png)

**Update Order Status**
*(Admin can change the status of the user's order, whether pending for delivery, delivered, or canceled)*
![Update Order Status](Orders.png)

**User Management Dashboard**
*(Admin's dashboard of users where the admin can see their full names, emails, statuses, and edit actions)*
![User Management](UserManagement.png)

---
*Developed by Clarence ABCDEE Glorioso as the evaluation project for the PurpleBug Internship Application (April 2026).*