CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, price, description)
VALUES
('Laptop', 10000000, 'Laptop untuk kebutuhan kerja'),
('Mouse', 150000, 'Mouse wireless'),
('Keyboard', 300000, 'Keyboard mechanical');