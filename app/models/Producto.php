<?php

    class Producto
    {
        private $id_producto;
        private $descripcion;
        private $precio;
        private $costo;
        private $iscbp;
        private $id_empresa;
        private $ultima_salida;
        private $codsunat;
        private $conectar;

        /**
         * Producto constructor.
         */
        public function __construct() {
            $this->conectar = (new Conexion())->getConexion();
        }

        /**
         * @return mixed
         */
        public function getIdProducto() {
            return $this->id_producto;
        }

        /**
         * @param mixed $id_producto
         */
        public function setIdProducto($id_producto) {
            $this->id_producto = $id_producto;
        }

        /**
         * @return mixed
         */
        public function getDescripcion() {
            return $this->descripcion;
        }

        /**
         * @param mixed $descripcion
         */
        public function setDescripcion($descripcion) {
            $this->descripcion = $descripcion;
        }

        /**
         * @return mixed
         */
        public function getPrecio() {
            return $this->precio;
        }

        /**
         * @param mixed $precio
         */
        public function setPrecio($precio) {
            $this->precio = $precio;
        }

        /**
         * @return mixed
         */
        public function getCosto() {
            return $this->costo;
        }

        /**
         * @param mixed $costo
         */
        public function setCosto($costo) {
            $this->costo = $costo;
        }

        /**
         * @return mixed
         */
        public function getIscbp() {
            return $this->iscbp;
        }

        /**
         * @param mixed $iscbp
         */
        public function setIscbp($iscbp) {
            $this->iscbp = $iscbp;
        }

        /**
         * @return mixed
         */
        public function getIdEmpresa() {
            return $this->id_empresa;
        }

        /**
         * @param mixed $id_empresa
         */
        public function setIdEmpresa($id_empresa) {
            $this->id_empresa = $id_empresa;
        }

        /**
         * @return mixed
         */
        public function getUltimaSalida() {
            return $this->ultima_salida;
        }

        /**
         * @param mixed $ultima_salida
         */
        public function setUltimaSalida($ultima_salida) {
            $this->ultima_salida = $ultima_salida;
        }

        /**
         * @return mixed
         */
        public function getCodsunat() {
            return $this->codsunat;
        }

        /**
         * @param mixed $codsunat
         */
        public function setCodsunat($codsunat) {
            $this->codsunat = $codsunat;
        }

        public function insertar() {
            $sql = "insert into productos 
        values ('$this->id_producto', '$this->descripcion', '$this->precio', '$this->costo', '$this->iscbp', '$this->id_empresa','{$_SESSION['sucursal']}' ,'$this->ultima_salida', '$this->codsunat')";

            echo $sql;
            die();

            return $this->conectar->ejecutar_idu($sql);
        }

        public function modificar() {
            $sql = "update productos 
        set descripcion = '$this->descripcion', precio = '$this->precio', costo = '$this->costo', iscbp = '$this->iscbp', codsunat = '$this->codsunat'  
        where id_producto = '$this->id_producto'";
            echo $sql;
            return $this->conectar->ejecutar_idu($sql);
        }

        public function obtenerId() {
            $sql = "select ifnull(max(id_producto) + 1, 1) as codigo 
            from productos";
            $this->id_producto = $this->conectar->get_valor_query($sql, 'codigo');
        }

        public function obtenerDatos() {
            $sql = "select * 
        from productos 
        where id_producto = '$this->id_producto'";
            $fila = $this->conectar->get_Row($sql);
            $this->descripcion = $fila['descripcion'];
            $this->precio = $fila['precio'];
            $this->costo = $fila['costo'];
            $this->iscbp = $fila['iscbp'];
            $this->id_empresa = $fila['id_empresa'];
            $this->ultima_salida = $fila['ultima_salida'];
            $this->codsunat = $fila['codsunat'];
        }

        public function verFilas($almacen) {
            $sql = "SELECT * from productos where id_empresa = '$this->id_empresa' and sucursal = '{$_SESSION['sucursal']}' and estado = '1' AND almacen=$almacen order by id_producto DESC";

            return $this->conectar->query($sql);
        }

        public function verFilas_kardex($almacen) {
            $sql = "SELECT * from view_producto_balance where id_empresa = '$this->id_empresa' and sucursal = '{$_SESSION['sucursal']}' and estado = '1' AND almacen=$almacen order by id_producto DESC";

            return $this->conectar->query($sql);
        }

        public function ver_compras_balance($almacen, $desde, $hasta) {
            $sql = "SELECT * from view_compras_balance where
                                        id_empresa = '$this->id_empresa' and
                                        sucursal = '{$_SESSION['sucursal']}' and
                                        estado = '1' AND
                                        almacen=$almacen AND  
                                       comprobante_fecha BETWEEN '$desde' AND '$hasta' 
                                    order by comprobante_fecha DESC";
            #var_dump($sql);
            return $this->conectar->query($sql);
        }

        public function ver_ventas_balance($almacen, $desde, $hasta) {
            $sql = "SELECT * from view_ventas_balance where
                                        id_empresa = '$this->id_empresa' and
                                        sucursal = '{$_SESSION['sucursal']}' and
                                        estado = '1' AND
                                        almacen=$almacen AND  
                                       comprobante_fecha BETWEEN '$desde' AND '$hasta' 
                                    order by comprobante_fecha DESC";
            # var_dump($sql);
            return $this->conectar->query($sql);
        }

        public function ver_balance($almacen, $desde, $hasta) {
            $sql = "SELECT * from view_balance where
                                        id_empresa = '$this->id_empresa' and
                                        sucursal = '{$_SESSION['sucursal']}' and
                                        estado = '1' AND
                                        almacen=$almacen AND  
                                       comprobante_fecha BETWEEN '$desde' AND '$hasta' 
                                    order by comprobante_fecha DESC";
            # var_dump($sql);
            return $this->conectar->query($sql);
        }

        public function ver_kardex_productos($almacen, $desde, $hasta) {
            $sql = "SELECT 
	    B.producto,
	    B.kardex_operacion,
	    B.kardex_empresa_proveedor,
	    '----' AS '----',
	    B.entradas_cantidad,
	    B.entradas_costo_unitario,
	    B.entradas_costo_total,
	    '-----' AS '-----',
	    B.salidas_cantidad,
	    B.salidas_costo_unitario,
	    B.salidas_costo_total
	    
	     FROM (
                SELECT
                    a.descripcion AS producto,
                    'compra' AS kardex_operacion,
                    e.razon_social AS kardex_empresa_proveedor,
                    '----' AS '----',
                    a.cantidad AS entradas_cantidad,
                    a.costo AS entradas_costo_unitario,
                    (SUM(a.cantidad) * a.costo) AS entradas_costo_total,
                    '-----' AS '-----',
                    
                    
                    (SELECT SUM(x.cantidad) FROM productos_ventas x LEFT JOIN ventas y ON x.id_venta = y.id_venta  
                                     WHERE x.id_producto = a.id_producto AND y.estado =1 AND y.fecha_emision BETWEEN '$desde' AND '$hasta' ) AS salidas_cantidad,
                    
                    
                    IFNULL((SELECT ROUND(SUM(x.costo*x.cantidad),2) FROM productos_ventas x LEFT JOIN ventas y ON x.id_venta = y.id_venta  
                                   WHERE x.id_producto = a.id_producto AND y.estado =1 AND y.fecha_emision BETWEEN '$desde' AND '$hasta'),0) AS salidas_costo_unitario,
                    
                    
                    
                    IFNULL((SELECT ROUND(SUM(x.precio*x.cantidad),2) FROM productos_ventas x LEFT JOIN ventas y ON x.id_venta = y.id_venta 
                                                        WHERE x.id_producto = a.id_producto AND y.estado =1  AND y.fecha_emision BETWEEN '$desde' AND '$hasta'),0) AS salidas_costo_total,
                    
                    
                    
                    '---------' AS '---------',
                    a.id_empresa AS id_empresa,
                    a.sucursal AS sucursal,
                    a.estado AS estado,
                    a.almacen AS almacen,
                    a.id_producto AS id_producto
                FROM
                    productos a
                    LEFT JOIN productos_compras b
                    ON a.id_producto = b.id_producto
                    LEFT JOIN compras c
                    ON c.id_compra = b.id_compra
                    LEFT JOIN documentos_sunat d
                    ON d.id_tido = c.id_tido
                    LEFT JOIN proveedores e
                    ON e.proveedor_id = c.id_proveedor
                GROUP BY a.descripcion,c.id_proveedor
            ) AS B
	     WHERE B.producto !=''
	    ";
            #echo "<pre>";
            #var_dump($sql);
            #echo "</pre>";
            #die();
            $resultado = $this->conectar->query($sql);
            if (!$resultado) {
                die("Error en la consulta: " . $this->conectar->error);
            }
            return $resultado;
        }

        public function verFilasId($id) {
            $sql = "SELECT * from productos where id_empresa = '$this->id_empresa' and sucursal = '{$_SESSION['sucursal']}' and estado = '1' AND id_producto=$id order by id_producto DESC";

            return $this->conectar->query($sql)->fetch_assoc();
        }

        public function BuscarProductos($term) {
            $sql = "select * from productos 
        where id_empresa = '$this->id_empresa' and descripcion like '%$term%' 
        order by descripcion asc";
            return $this->conectar->get_Cursor($sql);
        }
    }
