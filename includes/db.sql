CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('manager', 'employee') NOT NULL
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);


CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
);


CREATE TABLE tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_number INT UNIQUE NOT NULL,
    status ENUM('free', 'occupied') NOT NULL DEFAULT 'free'
);


CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT,
    menu_id INT,
    quantity INT NOT NULL,
    order_date DATETIME NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE
);
