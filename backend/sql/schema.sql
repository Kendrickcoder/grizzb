-- Pharmacy Management System schema
-- Run against your backend's external database (phpMyAdmin, hosting
-- panel's SQL tool, or: mysql -h HOST -u USER -p DBNAME < schema.sql)

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    fullname   VARCHAR(100) NOT NULL,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS medicines (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    quantity     INT NOT NULL DEFAULT 0,
    expiry_date  DATE NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sales (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id     INT NOT NULL,
    medicine_name   VARCHAR(150) NOT NULL,
    quantity_sold   INT NOT NULL,
    price_per_unit  DECIMAL(10,2) NOT NULL,
    total_amount    DECIMAL(10,2) NOT NULL,
    staff_id        INT NOT NULL,
    staff_name      VARCHAR(100) NOT NULL,
    sold_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Login tokens for the frontend (Bearer auth), since frontend & backend
-- live on different domains and can't reliably share cookies.
CREATE TABLE IF NOT EXISTS sessions (
    token      CHAR(64) PRIMARY KEY,
    user_id    INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Registration only ever creates 'staff' accounts. Create your first
-- admin manually:
--   php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"
-- INSERT INTO users (fullname, username, password, role)
-- VALUES ('Admin User', 'admin', 'PASTE_HASH_HERE', 'admin');
