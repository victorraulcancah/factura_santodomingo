-- Agrega departamento, provincia y distrito a usuarios (igual que en clientes).
-- Se guarda el nombre (texto) tal como se hace en el modal de clientes.

ALTER TABLE `usuarios` ADD COLUMN `departamento` VARCHAR(100) DEFAULT NULL;

ALTER TABLE `usuarios` ADD COLUMN `provincia` VARCHAR(100) DEFAULT NULL;

ALTER TABLE `usuarios` ADD COLUMN `distrito` VARCHAR(100) DEFAULT NULL;
