CREATE DATABASE sensor_data;

USE sensor_data;

CREATE TABLE sensor_values (
    id SERIAL PRIMARY KEY,
    timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    temperature FLOAT,
    humidity FLOAT,
    pressure FLOAT,
    air_quality FLOAT,
    lluvia_detectada FLOAT,
    acumulado_lluvia FLOAT,
    velocidad_viento FLOAT
);
