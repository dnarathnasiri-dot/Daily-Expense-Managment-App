-- ============================================================
--  DEMS – Daily Expense Management System
--  Run this in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS dems
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dems;

-- ── Users ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    first_name          VARCHAR(50)  NOT NULL,
    last_name           VARCHAR(50)  NOT NULL,
    email               VARCHAR(100) UNIQUE NOT NULL,
    password            VARCHAR(255) NOT NULL,
    email_notifications TINYINT(1)   DEFAULT 1,
    daily_summary       TINYINT(1)   DEFAULT 0,
    budget_alerts       TINYINT(1)   DEFAULT 1,
    created_at          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ── API Tokens (Bearer auth – replaces Laravel Sanctum) ────
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT         NOT NULL,
    token      VARCHAR(80) UNIQUE NOT NULL,
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Categories ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id    INT AUTO_INCREMENT PRIMARY KEY,
    name  VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(20) DEFAULT '#3B82F6'
);

INSERT IGNORE INTO categories (name, color) VALUES
    ('Food',           '#3b82f6'),
    ('Transportation', '#22c55e'),
    ('Entertainment',  '#f59e0b'),
    ('Shopping',       '#ef4444'),
    ('Utilities',      '#8b5cf6'),
    ('Health',         '#06b6d4'),
    ('Other',          '#6b7280');

-- ── Expenses ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS expenses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    amount      DECIMAL(10,2) NOT NULL,
    category_id INT           NOT NULL,
    date        DATE          NOT NULL,
    description TEXT,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);
