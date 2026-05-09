# Academic Printing & Distribution System (Multi-University)

A comprehensive, multi-tenant platform designed to manage the printing and distribution of books and educational materials across multiple universities. This system streamlines the workflow between university administrations, central warehouses, and distribution delegates, ensuring absolute accuracy in inventory tracking and student deliveries.

---

## 📝 Project Overview
This system provides a robust solution for academic institutions to manage their educational material supply chain. It supports multiple universities within a single instance, allowing each institution to maintain its own hierarchy of colleges, departments, and students while benefiting from a unified technological core.

---

## 🚀 Key Features

### 1. Multi-Tenant University Management
- Support for multiple universities in a single platform.
- Dedicated administrative control for each university.
- Isolation of data (Students, Inventory, Staff) between institutions.

### 2. Academic Hierarchy Management
- Flexible structure: University -> Colleges -> Departments -> Batches/Levels.
- Subject management linked to specific academic paths.

### 3. Smart Student Distribution
- Batch upload of student lists via **Excel**.
- Automatic section assignment based on ID ranges.
- Real-time tracking of material reception status for every student.

### 4. Advanced Inventory & Custody
- Centralized warehouse management for each university.
- Digital custody transfer between staff with full audit trails.
- Real-time stock level monitoring and automated balance clearing.

### 5. Delivery & Audit System
- Streamlined mobile-responsive interface for distribution delegates.
- Comprehensive Activity Logs for security and accountability.
- Live analytics dashboard for distribution progress and stock status.

---

## 🛠️ Tech Stack
- **Backend:** Laravel (PHP) - Scalable and secure architectural core.
- **Frontend:** Vue.js 3 with Inertia.js - Modern, reactive SPA experience.
- **Styling:** Tailwind CSS - Responsive and clean design system.
- **Database:** MySQL - Efficient relational data storage.
- **Integration:** Maatwebsite Excel - Robust file processing for large datasets.

---

## ⚙️ Requirements
- PHP >= 8.2
- MySQL
- Composer
- Node.js & NPM

---

## 🏗️ Installation Steps

1. **Clone & Install Dependencies:**
   ```bash
   composer install
   npm install
   ```

2. **Environment Configuration:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup:**
   ```bash
   php artisan migrate --seed
   ```

4. **Running the Development Server:**
   ```bash
   npm run dev
   # In a separate terminal
   php artisan serve
   ```

---

## 🎓 Development & Vision
This platform is designed to support the digital transformation of academic administration, moving away from paper-based tracking to a secure, transparent, and efficient digital ecosystem.

---
*Developed with excellence to empower educational institutions through technology.*
