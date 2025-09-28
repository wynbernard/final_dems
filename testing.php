CREATE TABLE evacuation_record_table (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    barangay_id INT NOT NULL,
    event_id INT NOT NULL,              -- Links to disaster event
    disaster_type VARCHAR(100),         -- e.g., Flood, Typhoon, Earthquake
    start_date DATETIME NOT NULL,
    end_date DATETIME DEFAULT NULL,
    population INT NOT NULL,            -- Total population of barangay
    at_risk_population INT,             -- Population in hazard area
    total_solo INT DEFAULT 0,           -- Solo evacuees
    total_family INT DEFAULT 0,         -- Family evacuees
    total_infant INT DEFAULT 0,
    total_toddler INT DEFAULT 0,
    total_pre_school INT DEFAULT 0,
    total_school_age INT DEFAULT 0,
    total_teenage INT DEFAULT 0,
    total_adult INT DEFAULT 0,
    total_senior INT DEFAULT 0,
    total_evacuation INT DEFAULT 0,     -- SUM of all evacuees
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);