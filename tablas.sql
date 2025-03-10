-- Crear la tabla "bodegas"
CREATE TABLE bodegas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
);

-- Crear la tabla "sucursales"
CREATE TABLE sucursales (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
);

-- Crear la tabla "monedas"
CREATE TABLE monedas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
);

-- Crear la tabla "materiales"
CREATE TABLE materiales (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL
);

-- Crear la tabla "productos"
CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    bodega_id INT NOT NULL,
    sucursal_id INT NOT NULL,
    moneda_id INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    descripcion TEXT,
    FOREIGN KEY (bodega_id) REFERENCES bodegas(id),
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id),
    FOREIGN KEY (moneda_id) REFERENCES monedas(id)
);

-- Crear la tabla "producto_material" (relación entre productos y materiales)
CREATE TABLE producto_material (
    producto_id INT NOT NULL,
    material_id INT NOT NULL,
    PRIMARY KEY (producto_id, material_id),
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materiales(id) ON DELETE CASCADE
);

--JOIN para ver los materiales asociados al Producto
SELECT 
    p.codigo, 
    p.nombre, 
    m.nombre AS material
FROM productos p
LEFT JOIN producto_material pm ON p.id = pm.producto_id
LEFT JOIN materiales m ON pm.material_id = m.id  
WHERE p.id = 'id'
