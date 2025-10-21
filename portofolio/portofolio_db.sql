CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL
);

-- Tabel Profil
CREATE TABLE IF NOT EXISTS profile (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(100),
    about TEXT,
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    photo VARCHAR(255)
);

-- Tabel Sertifikat
CREATE TABLE IF NOT EXISTS certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    issued_by VARCHAR(100),
    issue_date DATE,
    photo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pengalaman Kerja
CREATE TABLE IF NOT EXISTS experiences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    is_current BOOLEAN DEFAULT FALSE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Skills
CREATE TABLE IF NOT EXISTS skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    skill_name VARCHAR(100) NOT NULL,
    level INT DEFAULT 50,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admin (username, password, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@portfolio.com');

-- Insert sample profile
INSERT INTO profile (name, title, about, phone, email, address) 
VALUES ('Your Name', 'Web Developer', 'Saya adalah seorang web developer dengan pengalaman dalam berbagai teknologi.', '081234567890', 'your.email@example.com', 'Jakarta, Indonesia');