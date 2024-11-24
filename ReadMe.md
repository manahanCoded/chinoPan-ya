CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'therapist', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    duration INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    therapist_id INT,
    service_id INT,
    appointment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'canceled') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id),
    FOREIGN KEY (therapist_id) REFERENCES Users(user_id),
    FOREIGN KEY (service_id) REFERENCES Services(service_id)
);

CREATE TABLE Payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'paypal'),
    payment_status ENUM('paid', 'unpaid', 'refunded') DEFAULT 'unpaid',
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id)
);

CREATE TABLE Availability (
    availability_id INT PRIMARY KEY AUTO_INCREMENT,
    therapist_id INT,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    FOREIGN KEY (therapist_id) REFERENCES Users(user_id)
);

CREATE TABLE Reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT,
    user_id INT,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id),
    FOREIGN KEY (user_id) REFERENCES Users(user_id)
);

CREATE TABLE booking (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id INT NOT NULL,
    therapist_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'paypal'),
    payment_status ENUM('paid', 'unpaid', 'refunded') DEFAULT 'unpaid',
    transaction_id VARCHAR(100),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    availability_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    
    FOREIGN KEY (appointment_id) REFERENCES Appointments(appointment_id) ON DELETE CASCADE,
    FOREIGN KEY (therapist_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

