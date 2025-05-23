-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'cashier',
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create products table (without category_id)
CREATE TABLE IF NOT EXISTS products (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INTEGER DEFAULT 0,
    sku VARCHAR(255) UNIQUE NOT NULL,
    barcode VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create sales table
CREATE TABLE IF NOT EXISTS sales (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'cash',
    payment_status VARCHAR(50) DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create sale_items table
CREATE TABLE IF NOT EXISTS sale_items (
    id BIGSERIAL PRIMARY KEY,
    sale_id BIGINT REFERENCES sales(id),
    product_id BIGINT REFERENCES products(id),
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create receipts table
CREATE TABLE IF NOT EXISTS receipts (
    id BIGSERIAL PRIMARY KEY,
    sale_id BIGINT REFERENCES sales(id),
    receipt_number VARCHAR(50) UNIQUE NOT NULL,
    receipt_data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data

-- Create an admin user (password: admin123)
INSERT INTO users (name, email, password, role)
VALUES ('Admin User', 'admin@transactix.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Create a cashier user (password: cashier123)
INSERT INTO users (name, email, password, role)
VALUES ('Cashier User', 'cashier@transactix.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier');

-- Insert sample products (without category_id)
INSERT INTO products (name, description, price, stock_quantity, sku, barcode, is_active)
VALUES 
    ('Coca-Cola 2L', 'Refreshing cola beverage', 2.99, 50, 'BEV001', '8901234567890', true),
    ('Whole Wheat Bread', 'Freshly baked whole wheat bread', 3.49, 20, 'BAK001', '7890123456789', true),
    ('Canned Tuna', 'Tuna chunks in water', 1.99, 100, 'CAN001', '6789012345678', true),
    ('Milk 1L', 'Fresh whole milk', 2.49, 30, 'DAI001', '5678901234567', true),
    ('Rice 5kg', 'Premium long grain rice', 8.99, 15, 'DRY001', '4567890123456', true),
    ('Chicken Breast 1kg', 'Fresh boneless chicken breast', 7.99, 10, 'MEA001', '3456789012345', true),
    ('Apples 1kg', 'Fresh red apples', 3.99, 25, 'PRO001', '2345678901234', true),
    ('All-Purpose Cleaner', 'Multi-surface cleaning solution', 4.99, 40, 'CLE001', '1234567890123', true),
    ('Toothpaste', 'Mint flavored toothpaste', 2.99, 35, 'PER001', '0123456789012', true),
    ('Batteries AA 4-pack', 'Long-lasting alkaline batteries', 5.99, 30, 'OTH001', '9012345678901', true);
