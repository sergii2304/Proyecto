-- Creación de la base de datos
CREATE DATABASE MayoMotors;
USE MayoMotors;

-- Creacion de las tablas

CREATE TABLE Usuarios (
    id_usuario VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100),
    telefono INT(9) NOT NULL,
    contrasena VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    administrador BOOLEAN DEFAULT FALSE
);

CREATE TABLE Provincias (
    id_provincia VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    UNIQUE KEY UK1_Provincias (nombre)
);

CREATE TABLE Marcas (
    id_marca VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    UNIQUE KEY UK1_Marcas (nombre)
);

CREATE TABLE Modelos (
    id_modelo VARCHAR(10) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    id_marca VARCHAR(10) NOT NULL,
    FOREIGN KEY (id_marca) REFERENCES Marcas(id_marca)
);

CREATE TABLE Coches (
    id_coche VARCHAR(20) PRIMARY KEY,
    matricula VARCHAR(10) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    color VARCHAR(30) NOT NULL,
    cambio VARCHAR(20) NOT NULL,
    ano INT NOT NULL,
    combustible VARCHAR(20) NOT NULL,
    cv INT NOT NULL,
    fecha DATE NOT NULL,
    id_usuario VARCHAR(20) NOT NULL,
    id_provincia VARCHAR(10) NOT NULL,
    id_modelo VARCHAR(10) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    descripcion TEXT,
    UNIQUE KEY UK1_Coches (matricula),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario),
    FOREIGN KEY (id_provincia) REFERENCES Provincias(id_provincia),
    FOREIGN KEY (id_modelo) REFERENCES Modelos(id_modelo)
);

CREATE TABLE Imagenes (
    id_imagen VARCHAR(30) PRIMARY KEY,
    url TEXT NOT NULL,
    id_coche VARCHAR(20) NOT NULL,
    FOREIGN KEY (id_coche) REFERENCES Coches(id_coche)
);

CREATE TABLE Guardar (
    id_usuario VARCHAR(20) NOT NULL,
    id_coche VARCHAR(20) NOT NULL,
    PRIMARY KEY (id_usuario, id_coche),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario),
    FOREIGN KEY (id_coche) REFERENCES Coches(id_coche)
);

-- Índices
CREATE INDEX idx_coches_id_usuario ON Coches(id_usuario);
CREATE INDEX idx_coches_id_provincia ON Coches(id_provincia);
CREATE INDEX idx_coches_id_modelo ON Coches(id_modelo);
CREATE INDEX idx_imagenes_id_coche ON Imagenes(id_coche);
CREATE INDEX idx_modelos_id_marca ON Modelos(id_marca);

-- Usuario Administrador

INSERT INTO Usuarios (id_usuario, nombre, apellidos, telefono, contrasena, correo, administrador) 
VALUES ('ADMIN001', 'Administrador', ' ', '684101844', 'Admin1234', 'admin@gmail.com', TRUE);

-- Marcas

INSERT INTO Marcas (id_marca, nombre) VALUES
('M01', 'Volkswagen'),
('M02', 'BMW'),
('M03', 'Mercedes-Benz'),
('M04', 'Audi'),
('M05', 'Porsche'),
('M06', 'Opel'),
('M07', 'Smart'),
('M08', 'Renault'),
('M09', 'Peugeot'),
('M10', 'Citroën'),
('M11', 'DS Automobiles'),
('M12', 'Alpine'),
('M13', 'Fiat'),
('M14', 'Alfa Romeo'),
('M15', 'Ferrari'),
('M16', 'Lamborghini'),
('M17', 'Maserati'),
('M18', 'Lancia'),
('M19', 'Abarth'),
('M20', 'Jaguar'),
('M21', 'Land Rover'),
('M22', 'MINI'),
('M23', 'Aston Martin'),
('M24', 'Bentley'),
('M25', 'Rolls-Royce'),
('M26', 'Lotus'),
('M27', 'McLaren'),
('M28', 'MG'),
('M29', 'SEAT'),
('M30', 'Cupra'),
('M31', 'Volvo'),
('M32', 'Polestar'),
('M33', 'Škoda'),
('M34', 'Dacia'),
('M35', 'Rimac'),
('M36', 'KTM'),
('M37', 'Bugatti'),
('M38', 'Koenigsegg'),
('M39', 'Pagani');

-- Modelos 

-- Volkswagen
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD001', 'Golf', 'M01'),
('MD002', 'Polo', 'M01'),
('MD003', 'Passat', 'M01'),
('MD004', 'Tiguan', 'M01'),
('MD005', 'T-Roc', 'M01'),
('MD006', 'Arteon', 'M01'),
('MD007', 'ID.3', 'M01'),
('MD008', 'ID.4', 'M01'),
('MD009', 'ID.5', 'M01'),
('MD010', 'ID.6', 'M01'),
('MD011', 'ID.7', 'M01'),
('MD012', 'ID. Buzz', 'M01'),
('MD013', 'Touareg', 'M01'),
('MD014', 'T-Cross', 'M01'),
('MD015', 'Taigo', 'M01'),
('MD016', 'Touran', 'M01'),
('MD017', 'Up', 'M01'),
('MD018', 'Jetta', 'M01'),
('MD019', 'Caddy', 'M01'),
('MD020', 'Transporter', 'M01'),
('MD021', 'Multivan', 'M01'),
('MD022', 'California', 'M01'),
('MD023', 'Caravelle', 'M01'),
('MD024', 'Crafter', 'M01'),
('MD025', 'Amarok', 'M01'),
('MD026', 'Sharan', 'M01');

-- BMW
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD027', 'Serie 1', 'M02'),
('MD028', 'Serie 2', 'M02'),
('MD029', 'Serie 3', 'M02'),
('MD030', 'Serie 4', 'M02'),
('MD031', 'Serie 5', 'M02'),
('MD032', 'Serie 6', 'M02'),
('MD033', 'Serie 7', 'M02'),
('MD034', 'Serie 8', 'M02'),
('MD035', 'X1', 'M02'),
('MD036', 'X2', 'M02'),
('MD037', 'X3', 'M02'),
('MD038', 'X4', 'M02'),
('MD039', 'X5', 'M02'),
('MD040', 'X6', 'M02'),
('MD041', 'X7', 'M02'),
('MD042', 'XM', 'M02'),
('MD043', 'Z4', 'M02'),
('MD044', 'i3', 'M02'),
('MD045', 'i4', 'M02'),
('MD046', 'i5', 'M02'),
('MD047', 'i7', 'M02'),
('MD048', 'iX', 'M02'),
('MD049', 'iX1', 'M02'),
('MD050', 'iX3', 'M02'),
('MD051', 'M2', 'M02'),
('MD052', 'M3', 'M02'),
('MD053', 'M4', 'M02'),
('MD054', 'M5', 'M02'),
('MD055', 'M8', 'M02');

-- Mercedes-Benz
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD056', 'Clase A', 'M03'),
('MD057', 'Clase B', 'M03'),
('MD058', 'Clase C', 'M03'),
('MD059', 'Clase E', 'M03'),
('MD060', 'Clase S', 'M03'),
('MD061', 'CLA', 'M03'),
('MD062', 'CLS', 'M03'),
('MD063', 'GLA', 'M03'),
('MD064', 'GLB', 'M03'),
('MD065', 'GLC', 'M03'),
('MD066', 'GLE', 'M03'),
('MD067', 'GLS', 'M03'),
('MD068', 'Clase G', 'M03'),
('MD069', 'EQA', 'M03'),
('MD070', 'EQB', 'M03'),
('MD071', 'EQC', 'M03'),
('MD072', 'EQE', 'M03'),
('MD073', 'EQS', 'M03'),
('MD074', 'EQV', 'M03'),
('MD075', 'Clase V', 'M03'),
('MD076', 'Vito', 'M03'),
('MD077', 'Sprinter', 'M03'),
('MD078', 'Citan', 'M03'),
('MD079', 'AMG GT', 'M03'),
('MD080', 'SL', 'M03');

-- Audi
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD081', 'A1', 'M04'),
('MD082', 'A3', 'M04'),
('MD083', 'A4', 'M04'),
('MD084', 'A5', 'M04'),
('MD085', 'A6', 'M04'),
('MD086', 'A7', 'M04'),
('MD087', 'A8', 'M04'),
('MD088', 'Q2', 'M04'),
('MD089', 'Q3', 'M04'),
('MD090', 'Q4', 'M04'),
('MD091', 'Q5', 'M04'),
('MD092', 'Q7', 'M04'),
('MD093', 'Q8', 'M04'),
('MD094', 'e-tron', 'M04'),
('MD095', 'e-tron GT', 'M04'),
('MD096', 'TT', 'M04'),
('MD097', 'R8', 'M04'),
('MD098', 'RS3', 'M04'),
('MD099', 'RS4', 'M04'),
('MD100', 'RS5', 'M04'),
('MD101', 'RS6', 'M04'),
('MD102', 'RS7', 'M04'),
('MD103', 'RS Q3', 'M04'),
('MD104', 'RS Q8', 'M04');

-- Porsche
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD105', '911', 'M05'),
('MD106', '718 Cayman', 'M05'),
('MD107', '718 Boxster', 'M05'),
('MD108', 'Taycan', 'M05'),
('MD109', 'Panamera', 'M05'),
('MD110', 'Macan', 'M05'),
('MD111', 'Cayenne', 'M05');

-- Opel
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD112', 'Corsa', 'M06'),
('MD113', 'Astra', 'M06'),
('MD114', 'Insignia', 'M06'),
('MD115', 'Crossland', 'M06'),
('MD116', 'Mokka', 'M06'),
('MD117', 'Grandland', 'M06'),
('MD118', 'Combo', 'M06'),
('MD119', 'Zafira', 'M06'),
('MD120', 'Vivaro', 'M06'),
('MD121', 'Movano', 'M06'),
('MD122', 'Rocks', 'M06');

-- Smart
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD123', 'fortwo', 'M07'),
('MD124', 'forfour', 'M07'),
('MD125', '#1', 'M07'),
('MD126', '#3', 'M07'),
('MD127', 'Roadster', 'M07');

-- Renault
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD128', 'Clio', 'M08'),
('MD129', 'Captur', 'M08'),
('MD130', 'Arkana', 'M08'),
('MD131', 'Mégane', 'M08'),
('MD132', 'Scénic', 'M08'),
('MD133', 'Austral', 'M08'),
('MD134', 'Espace', 'M08'),
('MD135', 'Rafale', 'M08'),
('MD136', 'Koleos', 'M08'),
('MD137', 'Talisman', 'M08'),
('MD138', 'Twingo', 'M08'),
('MD139', 'ZOE', 'M08'),
('MD140', '5', 'M08'),
('MD141', '4', 'M08'),
('MD142', 'Kangoo', 'M08'),
('MD143', 'Express', 'M08'),
('MD144', 'Trafic', 'M08'),
('MD145', 'Master', 'M08');

-- Peugeot
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD146', '108', 'M09'),
('MD147', '208', 'M09'),
('MD148', '308', 'M09'),
('MD149', '408', 'M09'),
('MD150', '508', 'M09'),
('MD151', '2008', 'M09'),
('MD152', '3008', 'M09'),
('MD153', '5008', 'M09'),
('MD154', 'Rifter', 'M09'),
('MD155', 'Traveller', 'M09'),
('MD156', 'Partner', 'M09'),
('MD157', 'Expert', 'M09'),
('MD158', 'Boxer', 'M09');

-- Citroën
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD159', 'C1', 'M10'),
('MD160', 'C3', 'M10'),
('MD161', 'C3 Aircross', 'M10'),
('MD162', 'C4', 'M10'),
('MD163', 'C4 X', 'M10'),
('MD164', 'C5 Aircross', 'M10'),
('MD165', 'C5 X', 'M10'),
('MD166', 'Berlingo', 'M10'),
('MD167', 'SpaceTourer', 'M10'),
('MD168', 'Ami', 'M10'),
('MD169', 'Jumpy', 'M10'),
('MD170', 'Jumper', 'M10');

-- DS Automobiles
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD171', 'DS 3', 'M11'),
('MD172', 'DS 4', 'M11'),
('MD173', 'DS 7', 'M11'),
('MD174', 'DS 9', 'M11');

-- Alpine
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD175', 'A110', 'M12'),
('MD176', 'A290', 'M12');

-- Fiat
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD177', '500', 'M13'),
('MD178', 'Panda', 'M13'),
('MD179', 'Tipo', 'M13'),
('MD180', '500X', 'M13'),
('MD181', 'Doblò', 'M13'),
('MD182', 'Scudo', 'M13'),
('MD183', 'Ducato', 'M13'),
('MD184', 'Multipla', 'M13'),
('MD185', 'Punto', 'M13');

-- Alfa Romeo
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD186', 'Giulia', 'M14'),
('MD187', 'Stelvio', 'M14'),
('MD188', 'Tonale', 'M14'),
('MD189', 'Junior', 'M14'),
('MD190', 'Giulietta', 'M14'),
('MD191', '4C', 'M14');

-- Ferrari
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD192', 'Roma', 'M15'),
('MD193', 'SF90', 'M15'),
('MD194', '296 GTB', 'M15'),
('MD195', 'F8 Tributo', 'M15'),
('MD196', '812', 'M15'),
('MD197', 'Purosangue', 'M15'),
('MD198', 'Daytona SP3', 'M15'),
('MD199', 'Portofino', 'M15'),
('MD200', 'LaFerrari', 'M15');

-- Lamborghini
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD201', 'Huracán', 'M16'),
('MD202', 'Aventador', 'M16'),
('MD203', 'Urus', 'M16'),
('MD204', 'Sián', 'M16'),
('MD205', 'Revuelto', 'M16');

-- Maserati
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD206', 'Ghibli', 'M17'),
('MD207', 'Levante', 'M17'),
('MD208', 'Quattroporte', 'M17'),
('MD209', 'MC20', 'M17'),
('MD210', 'Grecale', 'M17'),
('MD211', 'GranTurismo', 'M17'),
('MD212', 'GranCabrio', 'M17');

-- Lancia
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD213', 'Ypsilon', 'M18'),
('MD214', 'Delta', 'M18'),
('MD215', 'Thema', 'M18'),
('MD216', 'Voyager', 'M18');

-- Abarth
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD217', '595', 'M19'),
('MD218', '695', 'M19'),
('MD219', '500e', 'M19');

-- Jaguar
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD220', 'XE', 'M20'),
('MD221', 'XF', 'M20'),
('MD222', 'F-TYPE', 'M20'),
('MD223', 'F-PACE', 'M20'),
('MD224', 'E-PACE', 'M20'),
('MD225', 'I-PACE', 'M20');

-- Land Rover
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD226', 'Range Rover', 'M21'),
('MD227', 'Range Rover Sport', 'M21'),
('MD228', 'Range Rover Velar', 'M21'),
('MD229', 'Range Rover Evoque', 'M21'),
('MD230', 'Discovery', 'M21'),
('MD231', 'Discovery Sport', 'M21'),
('MD232', 'Defender', 'M21');

-- MINI
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD233', 'Cooper', 'M22'),
('MD234', 'Clubman', 'M22'),
('MD235', 'Countryman', 'M22'),
('MD236', 'Electric', 'M22'),
('MD237', 'Aceman', 'M22');

-- Aston Martin
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD238', 'DB11', 'M23'),
('MD239', 'DBS', 'M23'),
('MD240', 'Vantage', 'M23'),
('MD241', 'DBX', 'M23'),
('MD242', 'Valkyrie', 'M23'),
('MD243', 'DB12', 'M23');

-- Bentley
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD244', 'Continental GT', 'M24'),
('MD245', 'Flying Spur', 'M24'),
('MD246', 'Bentayga', 'M24'),
('MD247', 'Bacalar', 'M24'),
('MD248', 'Batur', 'M24');

-- Rolls-Royce
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD249', 'Phantom', 'M25'),
('MD250', 'Ghost', 'M25'),
('MD251', 'Wraith', 'M25'),
('MD252', 'Dawn', 'M25'),
('MD253', 'Cullinan', 'M25'),
('MD254', 'Spectre', 'M25');

-- Lotus
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD255', 'Emira', 'M26'),
('MD256', 'Evija', 'M26'),
('MD257', 'Eletre', 'M26'),
('MD258', 'Emeya', 'M26'),
('MD259', 'Exige', 'M26'),
('MD260', 'Evora', 'M26');

-- McLaren
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD261', '720S', 'M27'),
('MD262', 'Artura', 'M27'),
('MD263', 'GT', 'M27'),
('MD264', '765LT', 'M27'),
('MD265', 'Elva', 'M27'),
('MD266', 'Senna', 'M27'),
('MD267', 'Speedtail', 'M27');

-- MG
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD268', 'MG3', 'M28'),
('MD269', 'MG4', 'M28'),
('MD270', 'MG5', 'M28'),
('MD271', 'ZS', 'M28'),
('MD272', 'HS', 'M28'),
('MD273', 'Marvel R', 'M28'),
('MD274', 'Cyberster', 'M28');

-- SEAT
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD275', 'Ibiza', 'M29'),
('MD276', 'León', 'M29'),
('MD277', 'Arona', 'M29'),
('MD278', 'Ateca', 'M29'),
('MD279', 'Tarraco', 'M29'),
('MD280', 'Mii', 'M29');

-- Cupra
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD281', 'Formentor', 'M30'),
('MD282', 'Born', 'M30'),
('MD283', 'León', 'M30'),
('MD284', 'Ateca', 'M30'),
('MD285', 'Tavascan', 'M30'),
('MD286', 'Terramar', 'M30'),
('MD287', 'Urban Rebel', 'M30');

-- Volvo
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD288', 'XC40', 'M31'),
('MD289', 'XC60', 'M31'),
('MD290', 'XC90', 'M31'),
('MD291', 'S60', 'M31'),
('MD292', 'S90', 'M31'),
('MD293', 'V60', 'M31'),
('MD294', 'V90', 'M31'),
('MD295', 'C40', 'M31'),
('MD296', 'EX30', 'M31'),
('MD297', 'EX90', 'M31');

-- Polestar
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD298', 'Polestar 1', 'M32'),
('MD299', 'Polestar 2', 'M32'),
('MD300', 'Polestar 3', 'M32'),
('MD301', 'Polestar 4', 'M32'),
('MD302', 'Polestar 5', 'M32'),
('MD303', 'Polestar 6', 'M32');

-- Škoda
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD304', 'Fabia', 'M33'),
('MD305', 'Octavia', 'M33'),
('MD306', 'Superb', 'M33'),
('MD307', 'Kamiq', 'M33'),
('MD308', 'Karoq', 'M33'),
('MD309', 'Kodiaq', 'M33'),
('MD310', 'Enyaq', 'M33'),
('MD311', 'Scala', 'M33'),
('MD312', 'Elroq', 'M33');

-- Dacia
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD313', 'Sandero', 'M34'),
('MD314', 'Duster', 'M34'),
('MD315', 'Spring', 'M34'),
('MD316', 'Logan', 'M34'),
('MD317', 'Jogger', 'M34'),
('MD318', 'Bigster', 'M34');

-- Rimac
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD319', 'Nevera', 'M35'),
('MD320', 'Concept One', 'M35'),
('MD321', 'Concept Two', 'M35');

-- KTM
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD322', 'X-Bow', 'M36'),
('MD323', 'X-Bow GTX', 'M36'),
('MD324', 'X-Bow GT2', 'M36');

-- Bugatti
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD325', 'Chiron', 'M37'),
('MD326', 'Mistral', 'M37'),
('MD327', 'Bolide', 'M37'),
('MD328', 'Centodieci', 'M37'),
('MD329', 'La Voiture Noire', 'M37');

-- Koenigsegg
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD330', 'Jesko', 'M38'),
('MD331', 'Gemera', 'M38'),
('MD332', 'Regera', 'M38'),
('MD333', 'CC850', 'M38'),
('MD334', 'Jesko Absolut', 'M38');

-- Pagani
INSERT INTO Modelos (id_modelo, nombre, id_marca) VALUES
('MD335', 'Huayra', 'M39'),
('MD336', 'Utopia', 'M39'),
('MD337', 'Zonda', 'M39');

-- Provincias de España

INSERT INTO Provincias (id_provincia, nombre) VALUES
('P01', 'Álava'),
('P02', 'Albacete'),
('P03', 'Alicante'),
('P04', 'Almería'),
('P05', 'Ávila'),
('P06', 'Badajoz'),
('P07', 'Baleares'),
('P08', 'Barcelona'),
('P09', 'Burgos'),
('P10', 'Cáceres'),
('P11', 'Cádiz'),
('P12', 'Castellón'),
('P13', 'Ciudad Real'),
('P14', 'Córdoba'),
('P15', 'A Coruña'),
('P16', 'Cuenca'),
('P17', 'Girona'),
('P18', 'Granada'),
('P19', 'Guadalajara'),
('P20', 'Guipúzcoa'),
('P21', 'Huelva'),
('P22', 'Huesca'),
('P23', 'Jaén'),
('P24', 'León'),
('P25', 'Lleida'),
('P26', 'La Rioja'),
('P27', 'Lugo'),
('P28', 'Madrid'),
('P29', 'Málaga'),
('P30', 'Murcia'),
('P31', 'Navarra'),
('P32', 'Ourense'),
('P33', 'Asturias'),
('P34', 'Palencia'),
('P35', 'Las Palmas'),
('P36', 'Pontevedra'),
('P37', 'Salamanca'),
('P38', 'Santa Cruz de Tenerife'),
('P39', 'Cantabria'),
('P40', 'Segovia'),
('P41', 'Sevilla'),
('P42', 'Soria'),
('P43', 'Tarragona'),
('P44', 'Teruel'),
('P45', 'Toledo'),
('P46', 'Valencia'),
('P47', 'Valladolid'),
('P48', 'Vizcaya'),
('P49', 'Zamora'),
('P50', 'Zaragoza'),
('P51', 'Ceuta'),
('P52', 'Melilla');