
CREATE TABLE productos (
    ID_Producto INT NOT NULL AUTO_INCREMENT,
    Nombre VARCHAR(255) NOT NULL,
    Descripcion TEXT,
    Fotos TEXT,
    Precio DECIMAL(10, 2) NOT NULL,
    Cantidad_en_almacen INT NOT NULL,
    Fabricante VARCHAR(255),
    Origen VARCHAR(255),
    Categoria VARCHAR(50) NOT NULL,
    PRIMARY KEY (ID_Producto)
);


CREATE TABLE usuarios (
    ID_Usuario INT NOT NULL AUTO_INCREMENT,
    Nombre_usuario VARCHAR(255) NOT NULL,
    Correo_electronico VARCHAR(255) NOT NULL UNIQUE,
    Contraseña VARCHAR(255) NOT NULL,
    Fecha_nacimiento DATE,
    Numero_tarjeta_bancaria VARCHAR(19),
    Direccion_Postal TEXT,
    administrador TINYINT(1) DEFAULT 0,
    PRIMARY KEY (ID_Usuario)
);


CREATE TABLE carrito_compras (
    ID_Producto_Carrito INT NOT NULL AUTO_INCREMENT,
    ID_Usuario INT NOT NULL,
    ID_Producto INT NOT NULL,
    Cantidad INT DEFAULT 1,
    Fecha_agregado DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ID_Producto_Carrito),
    FOREIGN KEY (ID_Usuario) REFERENCES usuarios(ID_Usuario),
    FOREIGN KEY (ID_Producto) REFERENCES productos(ID_Producto)
);

CREATE TABLE historial_compras (
    ID_Compra INT NOT NULL AUTO_INCREMENT,
    ID_Usuario INT,
    ID_Producto INT,
    Fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (ID_Compra),
    FOREIGN KEY (ID_Usuario) REFERENCES usuarios(ID_Usuario),
    FOREIGN KEY (ID_Producto) REFERENCES productos(ID_Producto)
);

INSERT INTO Productos (Nombre, Descripcion, Fotos, Precio, Cantidad_en_almacen, Fabricante, Origen)
VALUES
('12000 FC Points', 'EA SPORTS FC 25 - FC Points 12000 (Xbox One/Xbox Series)', 'images/FC12000.jpg', 1618.42, 95, 'EA SPORTS', 'Canadá'),
('18500 FC Points', 'EA SPORTS FC 25 - FC Points 18500 (Xbox One/Xbox Series)', 'images/FC18500.jpg', 2208.10, 71, 'EA SPORTS', 'Canadá'),
('1050 FC Points', 'EA SPORTS FC 25 - FC Points 1050 (Xbox One/Xbox Series)', 'images/FC1050.jpg', 165.46, 150, 'EA SPORTS', 'Canadá'),
('2800 FC Points', 'EA SPORTS FC 25 - FC Points 2800 (Xbox One/Xbox Series)', 'images/FC2800.jpg', 418.77, 131, 'EA SPORTS', 'Canadá');


UPDATE Productos
SET Categoria = 'Deportes'
WHERE Categoria IS NULL;


INSERT INTO Productos (Nombre, Descripcion, Fotos, Precio, Cantidad_en_almacen, Fabricante, Origen, Categoria)
VALUES
('13000 Points', 'Call of Duty: Modern Warfare III - 13000 Points XBOX LIVE Key', 'images/cod.png', 1657.88, 92, 'Activision', 'Canadá', 'Deportes'),
('2400 Points', 'Call of Duty: Modern Warfare III - 2400 Points XBOX LIVE Key', 'images/cod.png', 327.22, 1280, 'Activision', 'Canadá', 'Deportes'),
('1100 Points', 'Call of Duty: Modern Warfare III - 1100 Points XBOX LIVE Key', 'images/cod.png', 168.73, 1167, 'Activision', 'Canadá', 'Deportes'),
('500 Points', 'Call of Duty: Modern Warfare III - 500 Points XBOX LIVE Key', 'images/cod.png', 91.78, 663, 'Activision', 'Canadá', 'Deportes');


-- Agregar productos a la tabla `Productos`
INSERT INTO Productos (Nombre, Descripcion, Precio, Cantidad_en_almacen, Fabricante, Origen, Categoria, Fotos)
VALUES
('Comodín de élite', 'Comodín de élite para el juego Clash', 179.00, 8, 'Supercell', 'Finlandia', 'Estrategia', 'images/comodineliteClash.png'),
('Evolución de esqueletos', 'Evolución de esqueletos para el juego Clash', 99.00, 2, 'Supercell', 'Finlandia', 'Estrategia', 'images/esqueletosClash.png'),
('Evolución del mago', 'Evolución del mago para el juego Clash', 99.00, 2, 'Supercell', 'Finlandia', 'Estrategia', 'images/magoClash.png'),
('Evolución de la descarga', 'Evolución de la descarga para el juego Clash', 99.00, 2, 'Supercell', 'Finlandia', 'Estrategia', 'images/zapClash.png'),
('Evolución del espíritu de hielo', 'Evolución del espíritu de hielo para el juego Clash', 99.00, 2, 'Supercell', 'Finlandia', 'Estrategia', 'images/espirituClash.png'),
('Evolución de la excavadora de duendes', 'Evolución de la excavadora de duendes para el juego Clash', 99.00, 2, 'Supercell', 'Finlandia', 'Estrategia', 'images/excavadoraduendesClash.png'),
('Evolución de la jaula del forzudo', 'Evolución de la jaula del forzudo para el juego Clash', 99.00, 2, 'Supercell', 'Finlandia', 'Estrategia', 'images/jaulaClash.png'),
('Montón de gemas', '14,000 gemas para el juego Clash', 2499.00, 5, 'Supercell', 'Finlandia', 'Estrategia', 'images/14000gemasClash.png'),
('Vagón de gemas', '6,500 gemas para el juego Clash', 1299.00, 5, 'Supercell', 'Finlandia', 'Estrategia', 'images/6500gemasClash.png'),
('Barril de gemas', '2,500 gemas para el juego Clash', 499.00, 5, 'Supercell', 'Finlandia', 'Estrategia', 'images/2500gemasClash.png'),
('Bolsa de gemas', '500 gemas para el juego Clash', 129.00, 5, 'Supercell', 'Finlandia', 'Estrategia', 'images/500gemasClash.png'),
('Puñado de gemas', '80 gemas para el juego Clash', 25.00, 5, 'Supercell', 'Finlandia', 'Estrategia', 'images/80gemasClash.png');


INSERT INTO Productos (Nombre, Descripcion, Precio, Cantidad_en_almacen, Fabricante, Origen, Categoria, Fotos)
VALUES
('1,000 V-Bucks', '1,000 monedas V-Bucks para el juego Fortnite', 99.00, 10, 'Epic Games', 'Estados Unidos', 'Battle Royale', 'images/1000Fortnite.png'),
('2,800 V-Bucks', '2,800 monedas V-Bucks para el juego Fortnite', 249.00, 10, 'Epic Games', 'Estados Unidos', 'Battle Royale', 'images/2800Fortnite.png'),
('5,000 V-Bucks', '5,000 monedas V-Bucks para el juego Fortnite', 399.00, 10, 'Epic Games', 'Estados Unidos', 'Battle Royale', 'images/5000Fortnite.png'),
('13,500 V-Bucks', '13,500 monedas V-Bucks para el juego Fortnite', 999.00, 10, 'Epic Games', 'Estados Unidos', 'Battle Royale', 'images/13500Fortnite.png'),
('Harley Quinn Skin', 'Aspecto exclusivo de Harley Quinn para el juego Fortnite', 199.00, 5, 'Epic Games', 'Estados Unidos', 'Battle Royale', 'images/HarleyFortnite.png');


INSERT INTO Productos (Nombre, Descripcion, Precio, Cantidad_en_almacen, Fabricante, Origen, Categoria, Fotos)
VALUES
('400 Robux', '400 Robux para usar en Roblox', 89.00, 15, 'Roblox Corporation', 'Estados Unidos', 'Moneda Virtual', 'images/Robux.jpg'),
('800 Robux', '800 Robux para usar en Roblox', 159.00, 15, 'Roblox Corporation', 'Estados Unidos', 'Moneda Virtual', 'images/Robux.jpg'),
('1,700 Robux', '1,700 Robux para usar en Roblox', 309.00, 10, 'Roblox Corporation', 'Estados Unidos', 'Moneda Virtual', 'images/Robux.jpg'),
('4,500 Robux', '4,500 Robux para usar en Roblox', 749.00, 8, 'Roblox Corporation', 'Estados Unidos', 'Moneda Virtual', 'images/Robux.jpg'),
('10,000 Robux', '10,000 Robux para usar en Roblox', 1599.00, 5, 'Roblox Corporation', 'Estados Unidos', 'Moneda Virtual', 'images/Robux.jpg');


INSERT INTO productos (Nombre, Descripcion, Precio, Cantidad_en_almacen, Fabricante, Origen, Categoria, Fotos)
value
('Fortnite - Marvel: Royalty & Warriors Pack', '¡Epic Games te da la oportunidad de encarnar a algunos de los mejores y más brillantes héroes de Marvel! Este paquete incluye una generosa cantidad de objetos de Marvel Comics: desde skins hasta Back Bling, Pickaxes y más. Una vez que compres Fortnite Marvel: Royalty & Warriors Pack Código de XBOX LIVE, ampliarás tu Casillero del juego con cosméticos muy codiciados, como el traje de Pantera Negra de Fortnite, herramientas de recolección temáticas, ¡e incluso dos Gliders!', 4259.00, 5, 'Epic Games', 'Estados Unidos', 'Battle Royale', 'images/skin2Fortnite.png');




ALTER TABLE Historial_Compras
DROP FOREIGN KEY historial_compras_ibfk_2;

ALTER TABLE Historial_Compras
ADD CONSTRAINT historial_compras_ibfk_2
FOREIGN KEY (ID_Producto) REFERENCES Productos(ID_Producto)
ON DELETE SET NULL;


-- usuario admin, se deduce el hash para que la contraseña sea admin123 con la funcion hash de php
INSERT INTO Usuarios (
    Nombre_usuario, 
    Correo_electronico, 
    Contraseña, 
    Fecha_nacimiento, 
    Numero_tarjeta_bancaria, 
    Direccion_Postal, 
    administrador
)
VALUES (
    'AdminMaster', 
    'admin@ejemplo.com', 
    '$2y$10$lc3ygqJAOq2XR02s/Y2gz.6mnN7HKUAxMZfJpQtuSkK8HYMZ5sBt6', 
    '1990-01-01', 
    '1234567890123456', 
    'Av. Principal 123, Ciudad Ejemplo, CP 12345', 
    1 -- Administrador
);

-- usuario admin, se deduce el hash para que la contraseña sea admin123 con la funcion hash de php
INSERT INTO Usuarios (
    Nombre_usuario, 
    Correo_electronico, 
    Contraseña, 
    Fecha_nacimiento, 
    Numero_tarjeta_bancaria, 
    Direccion_Postal, 
    administrador
)
VALUES (
    'AdminMaster2', 
    'admin2@ejemplo.com', 
    '$2y$10$lc3ygqJAOq2XR02s/Y2gz.6mnN7HKUAxMZfJpQtuSkK8HYMZ5sBt6', 
    '1990-01-01', 
    '1234567890123456', 
    'Av. Principal 123, Ciudad Ejemplo, CP 12345', 
    1 -- Administrador
);



