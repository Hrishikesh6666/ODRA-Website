# ODRA Road Sweepers — Production Sales & Quotation Portal
Website for the ODRA Sweeper Sales Team to build and manage equipment quotations.

A secure, enterprise multi-user web application for **ODRA Road Sweepers** (Challenger Manufacturing Ltd.).

This platform manages the complete sweeper quotation lifecycle, allowing authorized sales representatives to configure sweepers (MT4H & MS5H), apply commercial or cooperative pricing contracts (Sourcewell, H-GAC, BuyBoard, Minnesota State), compute territory freight estimates, and generate official contract-compliant PDF proposals.

---

## 🚀 Key Features

* **Multi-Tiered Authentication**:
  * Microsoft Entra ID (Office 365) Single Sign-On (OAuth 2.0).
  * Company ID & Office Email fallback with **Argon2id** password hashing.
  * Brute-force protection: 5 attempts/min rate-limiting with 15-minute account lockouts.
* **Role-Based Access Control (RBAC)**:
  * **Sales Representatives**: Access Quote Builder, manage drafts, finalize proposals, and download PDFs.
  * **HQ Administrators**: Manage base equipment models, pricing masters, enhancements catalog, freight rates matrix, sales team provisioning, and security audit logs.
* **Per-Representative Data Isolation (IDOR Defense)**:
  * Sales reps can only view, edit, and export their own quotes.
* **Immutable Snapshot & Versioning**:
  * Finalized quotations permanently freeze catalog pricing and customer specifications in an immutable JSON snapshot. Future price changes in the admin panel never alter historical finalized quotes.
  * Revisions (`v1`, `v2`, `v3`...) preserve previous finalized versions.
* **Quote Builder Engine**:
  * Real-time calculation of unit prices, base quantity, optional discount inflations, enhancement totals, and territory freight calculations.
  * High-resolution vector PDF generation via embedded `html2pdf.js`.
  * PDF proposals securely stored in private disk storage.
* **Enterprise Security Audit Trail**:
  * Complete, append-only audit trail recording logins, failed attempts, catalog updates, quote creation, finalization, and PDF downloads.

---

## 🛠️ Technology Stack

* **Backend Framework**: Laravel 11 / 13 (PHP 8.3+)
* **Database**: MySQL 8.0 / MariaDB
* **Authentication**: Laravel Session Guard + Laravel Socialite (Microsoft Entra ID)
* **Frontend**: Blade, Responsive CSS & JavaScript (Vanilla JS + html2pdf.js)
* **Testing**: PHPUnit / Laravel Feature Testing (24 Automated Tests, 100% Pass Rate)

---

## 📦 Getting Started

### Prerequisites

* PHP >= 8.2 with PDO MySQL, OpenSSL, Mbstring, and Tokenizer extensions.
* MySQL 8.0+
* Composer 2.x

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Hrishikesh6666/ODRA-Website.git
   cd ODRA-Website/odra-sales-portal
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Configure environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Update `.env` with your MySQL credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=odra_sales_portal
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

4. **Run database migrations and seeders**:
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Start local development server**:
   ```bash
   php artisan serve --port=8000
   ```

6. Open your browser and navigate to `http://localhost:8000`.

---

## 🔐 Default Seeded Accounts

### 1. HQ Administrator
* **Email**: `it.support@challengermfg.net`
* **Company ID**: `ADMIN001`
* **Password**: `OdraAdmin2026!`
* **Access**: `http://localhost:8000/admin/dashboard`

### 2. Sample Sales Representatives (16 accounts seeded)
* **John Wells**: `john.wells@odrasweeper.com` (Rep Code: `JW`)
* **Carlos Silva**: `carlos.silva@odrasweeper.com` (Rep Code: `CS`)
* **Password**: `OdraSales2026!`
* **Access**: `http://localhost:8000/sales/dashboard`

---

## 🧪 Running Automated Tests

Run the full feature and security test suite:
```bash
php artisan test
```

---

## 📄 License

Proprietary — Internal Commercial Application for ODRA Road Sweepers & Challenger Manufacturing Ltd.
>>>>>>> 6e07ab9 (ODRA Road Sweepers Sales Portal - Production Laravel 11 Application with Full Quote Lifecycle, RBAC, MySQL & Enterprise Security)
