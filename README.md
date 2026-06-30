# 🛡️ CyberShield — Cybersecurity Management System

A full-stack **Cybersecurity Management System** built with PHP, MySQL, and vanilla JavaScript. Developed as a Database Systems course project (227301).

---

## 👥 Team

| Name | Role |
|------|------|
| Taina Khan | Developer |
| Naba Gohar | Developer |
| Areeba Qamar | Developer |

---

## 📸 Features

- 🎯 **Threat Management** — Log, classify, and track security threats by severity
- 🚨 **Incident Response** — Full incident lifecycle with priority and resolution tracking
- 🖥️ **Asset Management** — Inventory with real-time vulnerability scoring and scan history
- 👥 **User Management** — Role-based access control (Admin · Analyst · Viewer)
- 📊 **Security Dashboard** — Live stats pulled from the database
- 🔐 **Authentication** — Login, Signup, and Password Reset flows

---

## 🗄️ Database Design

**7 normalized tables with full relational integrity:**

```
users ──────────┐
                ├──── threats    (AssignedTo → users)
                ├──── incidents  (AssignedTo → users)
                ├──── scans      (InitiatedBy → users)
                └──── audit_log  (UserID → users)

assets ─────────┐
                ├──── scans      (AssetID → assets)
                └──── alerts     (RelatedAsset → assets)

threats ────────└──── alerts     (RelatedThreat → threats)
```

**Normalization:** 1NF → 2NF → 3NF applied throughout.
**Relationships visible** in phpMyAdmin → Designer tab.

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Database | MySQL (via WAMP) |
| Backend | PHP 8.x with PDO |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Server | Apache (WAMP) |
| DB GUI | phpMyAdmin |

---

## ⚙️ Local Setup Guide

### Step 1 — Requirements

- [WAMP Server](https://www.wampserver.com/) (Windows) or XAMPP
- PHP 8.0+
- MySQL 5.7+
- A modern browser

---

### Step 2 — Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/CyberShield.git
```

Move the `CyberShield` folder into your WAMP www directory:

```
C:\wamp64\www\CyberShield\
```

---

### Step 3 — Import the Database

1. Start WAMP — make sure Apache & MySQL are green
2. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
3. Click **Import** tab
4. Choose file: `database/cybershield_db.sql`
5. Click **Go**

✅ This creates the database, all 7 tables, and sample data automatically.

---

### Step 4 — Configure Database Connection

Open `api/config.php` and update if needed:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3308');   // Change to 3306 if using default MySQL port
define('DB_NAME', 'cybershield_db');
define('DB_USER', 'root');
define('DB_PASS', '');       // Add your MySQL password if set
```

---

### Step 5 — Configure Frontend API URL

Open `js/app.js` and update line 1:

```js
const API_BASE = 'http://localhost/CyberShield/api';
```

> ⚠️ The folder name in the URL must exactly match your folder on disk. Case-sensitive on some systems.

---

### Step 6 — Run the App

Open your browser and go to:

```
http://localhost/CyberShield/index.html
```

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@cybershield.sec | admin123 |
| Analyst | analyst@cybershield.sec | admin123 |
| Viewer | k.lee@cybershield.sec | admin123 |

> 💡 You can also register a new account via the Signup page.

---

## 📁 Project Structure

```
CyberShield/
│
├── 📁 api/                   ← PHP REST API
│   ├── config.php            ← Database connection (edit port/password here)
│   ├── auth.php              ← Login & Signup
│   ├── assets.php            ← Assets CRUD
│   ├── threats.php           ← Threats CRUD
│   ├── incidents.php         ← Incidents CRUD
│   └── users.php             ← Users CRUD
│
├── 📁 database/
│   └── cybershield_db.sql    ← Full schema + sample data (import this)
│
├── 📁 js/
│   └── app.js                ← Shared JS: API URL, auth helpers, toast, modals
│
├── index.html                ← Landing page
├── login.html                ← Login
├── signup.html               ← Register
├── reset-password.html       ← Password reset (UI flow)
├── dashboard.html            ← Security overview
├── threats.html              ← Threat management
├── incidents.html            ← Incident management
├── assets.html               ← Asset inventory
└── users.html                ← User management
```

---

## 🔌 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/threats.php` | Get all threats |
| POST | `/api/threats.php` | Create threat |
| PUT | `/api/threats.php` | Update threat |
| DELETE | `/api/threats.php?id=1` | Delete threat |
| GET | `/api/incidents.php` | Get all incidents |
| POST | `/api/incidents.php` | Log incident |
| PUT | `/api/incidents.php` | Update incident |
| DELETE | `/api/incidents.php?id=1` | Delete incident |
| GET | `/api/assets.php` | Get all assets |
| POST | `/api/assets.php` | Register asset |
| PUT | `/api/assets.php` | Update asset |
| PATCH | `/api/assets.php` | Scan asset |
| DELETE | `/api/assets.php?id=1` | Delete asset |
| GET | `/api/users.php` | Get all users |
| POST | `/api/users.php` | Create user |
| PUT | `/api/users.php` | Update user |
| DELETE | `/api/users.php?id=1` | Deactivate user |
| POST | `/api/auth.php?action=login` | Login |
| POST | `/api/auth.php?action=signup` | Register |

---

## ❗ Common Issues

| Problem | Fix |
|---------|-----|
| "Cannot connect to database" | Make sure WAMP is running and MySQL is green |
| Wrong port error | Change `DB_PORT` in `api/config.php` to `3306` or `3308` |
| API returns 404 | Check folder name matches URL exactly — `CyberShield` not `cybershield` |
| Blank page / no data | Open browser console (F12) and check for errors |
| Login not working | Make sure you imported the SQL file first |

---

## 📄 License

This project was built for academic purposes as part of a Database Systems course (227301).

---

> Built with 💙 by Taina Khan, Naba Gohar & Areeba Qamar
