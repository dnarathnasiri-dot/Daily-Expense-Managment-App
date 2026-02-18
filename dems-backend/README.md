# DEMS Backend – PHP + MySQL REST API

Pairs with: `dems-frontend` (React + Tailwind + Vite)

---

## 📁 Backend File Structure

```
dems-backend/
├── index.php                          ← Front controller / router
│
├── config/
│   └── database.php                   ← MySQL credentials (don't commit this)
│
├── includes/
│   ├── cors.php                       ← CORS headers (allows localhost:5173)
│   ├── auth.php                       ← Bearer token validator → requireAuth()
│   └── response.php                   ← jsonOk(), jsonError(), getBody()
│
├── handlers/
│   ├── auth/
│   │   ├── login.php                  ← POST /api/login
│   │   ├── register.php               ← POST /api/register
│   │   └── logout.php                 ← POST /api/logout
│   │
│   ├── dashboard.php                  ← GET /api/dashboard
│   │
│   ├── expenses/
│   │   ├── list.php                   ← GET    /api/expenses?search=
│   │   ├── create.php                 ← POST   /api/expenses
│   │   ├── update.php                 ← PUT    /api/expenses/{id}
│   │   └── delete.php                 ← DELETE /api/expenses/{id}
│   │
│   ├── reports/
│   │   ├── summary.php                ← GET /api/reports/summary
│   │   ├── daily.php                  ← GET /api/reports/daily?days=7
│   │   ├── category.php               ← GET /api/reports/category
│   │   └── monthly.php                ← GET /api/reports/monthly
│   │
│   └── settings/
│       ├── profile.php                ← PUT    /api/settings/profile
│       ├── password.php               ← PUT    /api/settings/password
│       ├── notifications.php          ← PUT    /api/settings/notifications
│       ├── export.php                 ← GET    /api/settings/export?token=
│       └── account.php                ← DELETE /api/settings/account
│
└── sql/
    └── dems.sql                       ← Full schema + categories seed
```

---

## 🔑 Complete API Reference

| Method   | Endpoint                      | Auth | Description                  |
|----------|-------------------------------|------|------------------------------|
| POST     | /api/login                    | ✗    | Login → returns token + user |
| POST     | /api/register                 | ✗    | Register → returns token + user |
| POST     | /api/logout                   | ✓    | Deletes token                |
| GET      | /api/dashboard                | ✓    | Stats, pie data, recent tx   |
| GET      | /api/expenses                 | ✓    | List all (with ?search=)     |
| POST     | /api/expenses                 | ✓    | Create expense               |
| PUT      | /api/expenses/{id}            | ✓    | Update expense               |
| DELETE   | /api/expenses/{id}            | ✓    | Delete expense               |
| GET      | /api/reports/summary          | ✓    | Total, avg, category count   |
| GET      | /api/reports/daily?days=7     | ✓    | Daily totals (last N days)   |
| GET      | /api/reports/category         | ✓    | Totals per category          |
| GET      | /api/reports/monthly          | ✓    | Monthly totals (last 12mo)   |
| PUT      | /api/settings/profile         | ✓    | Update name/email            |
| PUT      | /api/settings/password        | ✓    | Change password              |
| PUT      | /api/settings/notifications   | ✓    | Toggle notification prefs    |
| GET      | /api/settings/export?token=   | ✓    | Download CSV                 |
| DELETE   | /api/settings/account         | ✓    | Delete account + all data    |

---

## ✅ Step-by-Step Setup Guide

---

### STEP 1 — Install Required Tools

1. **XAMPP** → https://apachefriends.org — start **Apache** and **MySQL**
2. **Node.js v18+** → https://nodejs.org (for the React frontend)
3. **Git** → https://git-scm.com
4. **VS Code** → https://code.visualstudio.com (recommended editor)

---

### STEP 2 — Setup the Database

1. Open your browser → `http://localhost/phpmyadmin`
2. Click **New** in the left panel
3. Name: `dems` → click **Create**
4. Click the `dems` database → click the **SQL** tab
5. Open `sql/dems.sql`, paste the entire contents → click **Go**

This creates:
- `users` table
- `personal_access_tokens` table (Bearer auth — replaces Laravel Sanctum)
- `categories` table (pre-seeded with 7 categories)
- `expenses` table

---

### STEP 3 — Place Backend Files

Copy the `dems-backend/` folder to your XAMPP htdocs:

```
C:\xampp\htdocs\dems-backend\
```

> ⚠️ The backend does NOT run through XAMPP Apache.
> It runs via PHP's built-in server on port 8000 (to match vite.config.js).

---

### STEP 4 — Configure Database Connection

Open `config/database.php` and confirm:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');       // XAMPP default = blank
define('DB_NAME', 'dems');
```

---

### STEP 5 — Start the Backend Server

Open a terminal (Git Bash or Command Prompt) in the `dems-backend/` folder:

```bash
cd C:/xampp/htdocs/dems-backend
php -S localhost:8000 index.php
```

You should see:
```
PHP Development Server (http://localhost:8000) started
```

> Keep this terminal open while developing.

---

### STEP 6 — Start the Frontend

Open a **second terminal** in your `dems-frontend/` folder:

```bash
cd path/to/dems-frontend
npm install       # first time only
npm run dev
```

Frontend runs at: `http://localhost:5173`
API calls proxy to: `http://localhost:8000`

---

### STEP 7 — Test It

1. Go to `http://localhost:5173`
2. Click **Create an account** → register a new user
3. You'll be redirected to the Dashboard
4. Add some expenses and explore all pages

---

### STEP 8 — GitHub Setup

```bash
# In your project root (contains both dems-frontend/ and dems-backend/)
git init
git add .
git commit -m "Initial commit: DEMS full stack"

# Create repo on GitHub, then:
git remote add origin https://github.com/YOUR_USERNAME/DEMS.git
git branch -M main
git push -u origin main
```

Create a `.gitignore` in the root:
```
# Never commit DB credentials
dems-backend/config/database.php

# Frontend build output
dems-frontend/node_modules/
dems-frontend/dist/

# OS files
.DS_Store
Thumbs.db
```

---

### STEP 9 — Daily Development Workflow

```bash
# Terminal 1 — Backend (keep open)
cd dems-backend
php -S localhost:8000 index.php

# Terminal 2 — Frontend (keep open)
cd dems-frontend
npm run dev

# Terminal 3 — Git (use as needed)
git add .
git commit -m "feat: your change description"
git push
```

---

## 🔧 How Auth Works (No Laravel Required)

The frontend stores a token in `localStorage` as `dems_token` and sends it as:
```
Authorization: Bearer <token>
```

The backend validates this token against the `personal_access_tokens` table on every protected request. This is the same pattern Laravel Sanctum uses — just implemented in plain PHP.

---

## 🔧 Troubleshooting

| Problem | Fix |
|---------|-----|
| CORS error in browser | Make sure `php -S localhost:8000 index.php` is running |
| 404 on all routes | Ensure you start with `index.php` as the router: `php -S localhost:8000 index.php` |
| DB connection failed | Check credentials in `config/database.php`, ensure XAMPP MySQL is running |
| Token invalid after refresh | This is normal — `requireAuth()` checks DB each request |
| Export CSV opens blank | Make sure `?token=` is appended correctly in Settings.jsx |

---

## 📋 JSON Shapes (Quick Reference)

**Login/Register response:**
```json
{
  "token": "abc123...",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "email_notifications": true,
    "daily_summary": false,
    "budget_alerts": true
  }
}
```

**Dashboard response:**
```json
{
  "today_expenses": 45.50,
  "month_expenses": 581.98,
  "total_transactions": 8,
  "avg_expense": 72.75,
  "by_category": [
    { "category": "Food", "total": 116.00 }
  ],
  "recent": [
    { "id": 1, "amount": 45.50, "date": "2026-02-16",
      "description": "Lunch", "category": "Food" }
  ]
}
```

**Expense object:**
```json
{ "id": 1, "amount": 45.50, "date": "2026-02-16",
  "description": "Lunch at restaurant", "category": "Food" }
```
