-- Drop existing tables if they exist
DROP TABLE IF EXISTS credit_cards;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS transactions;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL
);

-- Create accounts table
CREATE TABLE accounts (
    account_id INT PRIMARY KEY,
    user_id INT NOT NULL,
    account_type VARCHAR(50) NOT NULL,
    balance DECIMAL(10, 2) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create credit_cards table with user_agent field
CREATE TABLE credit_cards (
    card_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_number VARCHAR(16) NOT NULL UNIQUE,
    expiry_date DATE NOT NULL,
    cvv INT NOT NULL,
    user_agent VARCHAR(255), -- Added user_agent column
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create transactions table
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    transaction_type ENUM('credit', 'debit') NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id) ON DELETE CASCADE
);

-- Create otps table
CREATE TABLE otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- Insert initial admin and customer data
INSERT INTO users (username, password, role) VALUES 
('admin', 'password', 'admin'),
('customer1', '123456789', 'customer'),
('customer2', '654321', 'customer');

-- Create accounts for initial users
INSERT INTO accounts (account_id, user_id, account_type, balance) VALUES 
(1, 1, 'checking', 0),
(2, 2, 'checking', 1000.00),
(3, 3, 'checking', 2000.00);

-- Create credit cards for initial users with user_agent data
INSERT INTO credit_cards (user_id, card_number, expiry_date, cvv, user_agent) VALUES 
(1, '4111111111110001', '2027-12-31', 123, 'Mozilla/5.0'),
(2, '4111111111110002', '2027-12-31', 456, 'Mozilla/5.0'),
(3, '4111111111110003', '2027-12-31', 789, 'Mozilla/5.0');

-- Create sample transactions
INSERT INTO transactions (account_id, transaction_type, amount) VALUES
(1, 'credit', 1000.00),
(1, 'debit', 500.00),
(2, 'credit', 2000.00),
(2, 'debit', 1000.00);
