CREATE TABLE user_image_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    caption TEXT,
    submission_complete TINYINT(1) DEFAULT 0,
    UNIQUE(email, image_name)  
);
