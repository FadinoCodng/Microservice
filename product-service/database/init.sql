CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 10,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (id, name, price, stock, description)
VALUES
(1, 'Keyboard Mekanikal', 450000.00, 25, 'Keyboard mekanikal switch blue'),
(2, 'Mouse Wireless', 150000.00, 40, 'Mouse wireless ergonomis'),
(3, 'Monitor 24 Inch', 1800000.00, 10, 'Monitor 24 inch IPS 75Hz');
