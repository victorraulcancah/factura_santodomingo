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
