-- Agrega tipo de documento, y el usuario que registro al cliente.
-- tipo_documento: 1 = DNI, 6 = RUC, 4 = CARNET DE EXTRANJERIA (codigos SUNAT)

ALTER TABLE `clientes` ADD COLUMN `tipo_documento` VARCHAR(2) DEFAULT '1';

ALTER TABLE `clientes` ADD COLUMN `id_usuario` INT(11) DEFAULT NULL;

ALTER TABLE `clientes` ADD INDEX `idx_clientes_id_usuario` (`id_usuario`);

-- El documento pasa a ser opcional (solo el nombre es obligatorio)
ALTER TABLE `clientes` MODIFY `documento` VARCHAR(20) COLLATE utf8_spanish_ci DEFAULT NULL;

-- Vendedor que registro la venta (cotizaciones ya tenia id_usuario)
ALTER TABLE `ventas` ADD COLUMN `id_usuario` INT(11) DEFAULT NULL;

ALTER TABLE `ventas` ADD INDEX `idx_ventas_id_usuario` (`id_usuario`);

-- Productos entregados como obsequio (precio 0 pero con costo)
ALTER TABLE `productos_ventas` ADD COLUMN `es_regalo` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `productos_cotis` ADD COLUMN `es_regalo` TINYINT(1) NOT NULL DEFAULT 0;

-- productos_cotis tenia PRIMARY KEY (id_producto, id_coti), lo que impedia repetir
-- un producto en la misma cotizacion (p.ej. vender y regalar el mismo producto).
-- Se reemplaza por una clave propia. El KEY sobre id_producto se agrega ANTES de
-- soltar la PK porque la foreign key necesita un indice.
ALTER TABLE `productos_cotis` ADD KEY `idx_pc_id_producto` (`id_producto`);

ALTER TABLE `productos_cotis` DROP PRIMARY KEY;

ALTER TABLE `productos_cotis` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
