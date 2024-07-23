CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialidad VARCHAR(255) NOT NULL,
    experiecia VARCHAR(200) NOT NULL,
    descripcion TEXT
);
CREATE TABLE psicologos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    especialidad_id INT,
    Telefono VARCHAR(30) NOTT NULL,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
);
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(15) NOT NULL
);
CREATE TABLE horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    psicologo_id INT NOT NULL,
    dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    FOREIGN KEY (psicologo_id) REFERENCES psicologos(id)
);
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    psicologo_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    estado ENUM('Pendiente', 'Confirmada', 'Cancelada') DEFAULT 'Pendiente',
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (psicologo_id) REFERENCES psicologos(id)
);
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    fecha_pago DATE NOT NULL,
    foto_pago LONGBLOB NOT NULL,
    estado ENUM('Pendiente', 'Verificado', 'Rechazado') DEFAULT 'Pendiente',
    FOREIGN KEY (cita_id) REFERENCES citas(id)
);
CREATE TABLE costos_terapia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialidad_id INT NOT NULL,
    costo DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
);

CREATE TABLE recomendaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    psicologo_id INT NOT NULL,
    texto TEXT NOT NULL,
    fecha DATE NOT NULL,
    FOREIGN KEY (psicologo_id) REFERENCES psicologos(id)
);
CREATE TABLE articulos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    psicologo_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha DATE NOT NULL,
    FOREIGN KEY (psicologo_id) REFERENCES psicologos(id)
);
CREATE TABLE noticias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    psicologo_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    contenido TEXT NOT NULL,
    fecha DATE NOT NULL,
    FOREIGN KEY (psicologo_id) REFERENCES psicologos(id)
);
