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



<!-- MADE UP DATA -->

<!-- USERS -->
INSERT INTO users (full_name, email, phone_number, password, role) VALUES
('Maria Santos', 'maria.santos@email.com', '+63 912 345 6789', '1234abcd', 'admin'),
('John Doe', 'john.doe@email.com', '+63 932 234 5678', 'j0hn!234', 'customer'),
('Ella Reyes', 'ella.reyes@email.com', '+63 933 876 5432', 'ella#2024', 'therapist'),
('Mark Perez', 'mark.perez@email.com', '+63 918 765 4321', 'mark2024!', 'customer'),
('Lisa Tan', 'lisa.tan@email.com', '+63 917 654 3210', 'l!saTn123', 'therapist'),
('Daniel Cruz', 'daniel.cruz@email.com', '+63 915 123 4567', 'danielC@2024', 'therapist'),
('Sophia Lim', 'sophia.lim@email.com', '+63 916 987 6543', 'S0ph!aLim', 'therapist'),
('James Lee', 'james.lee@email.com', '+63 919 876 5432', 'j@m3sL33!', 'therapist');



<!-- SERVICES -->
INSERT INTO services (service_id, service_name, description, duration, price)
VALUES
(1, 'Foot Massage', 'A relaxing massage focused on the feet to relieve stress.', 30, 800.00),
(2, 'Aromatherapy Massage', 'A full-body massage using essential oils to enhance relaxation.', 60, 1500.00),
(3, 'Head, Neck & Shoulder Massage', 'Targeted massage for the head, neck, and shoulders.', 45, 1200.00),
(4, 'Add-Ons: Hot Towel Treatment', 'An add-on treatment to any massage session, including hot towels.', 15, 500.00),
(5, 'Full Body Massage', 'A comprehensive massage that addresses all major muscle groups.', 90, 1800.00),
(6, 'Relaxing Facial', 'A rejuvenating facial treatment using natural products.', 60, 2000.00),
(15, 'Hot Stone Massage', 'Hot stone massage spa.', 75, 1800.00);