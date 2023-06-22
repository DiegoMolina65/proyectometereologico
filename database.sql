CREATE DATABASE sensor_data;

USE sensor_data;

CREATE TABLE sensor_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    distance FLOAT,
    temperature FLOAT,
    humidity FLOAT,
    wind_quality FLOAT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);