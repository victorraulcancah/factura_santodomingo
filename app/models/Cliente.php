<?php

class Cliente
{
    private $id_cliente;
    private $documento;
    private $tipo_documento = '1'; // 1=DNI, 6=RUC, 4=Carnet de extranjeria
    private $id_usuario_nuevo = null; // reasignacion de dueno, solo la usa el ADMIN
    private $datos;
    private $direccion;
    private $direccion2;
    private $id_empresa;
    private $telefono;
    private $telefono2;
    private $email;
    private $departamento;
    private $provincia;
    private $distrito;
    private $fecha_nacimiento;
    private $total_venta;
    private $ultima_venta;
    private $conectar;

    /**
     * Cliente constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getDireccion2()
    {
        return $this->direccion2;
    }

    /**
     * @param mixed $direccion2
     */
    public function setDireccion2($direccion2): void
    {
        $this->direccion2 = $direccion2;
    }

    /**
     * @return mixed
     */
    public function getTelefono2()
    {
        return $this->telefono2;
    }

    /**
     * @param mixed $telefono2
     */
    public function setTelefono2($telefono2): void
    {
        $this->telefono2 = $telefono2;
    }



    /**
     * @return mixed
     */
    public function getIdCliente()
    {
        return $this->id_cliente;
    }

    /**
     * @param mixed $id_cliente
     */
    public function setIdCliente($id_cliente)
    {
        $this->id_cliente = $id_cliente;
    }

    /**
     * @return mixed
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * @param mixed $documento
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    }

    /**
     * @return mixed
     */
    public function getDatos()
    {
        return $this->datos;
    }

    /**
     * @param mixed $datos
     */
    /**
     * Reasigna el cliente a otro usuario. Cadena vacia = dejarlo sin asignar.
     * Solo tiene efecto si quien edita es ADMIN.
     */
    public function setIdUsuarioNuevo($id_usuario)
    {
        $this->id_usuario_nuevo = ($id_usuario === '' || $id_usuario === null) ? 'NULL' : (int) $id_usuario;
    }

    public function setTipoDocumento($tipo_documento)
    {
        $tipo_documento = trim($tipo_documento ?? '');
        $this->tipo_documento = in_array($tipo_documento, ['1', '4', '6', '7'], true) ? $tipo_documento : '1';
    }
    public function getTipoDocumento()
    {
        return $this->tipo_documento;
    }
    public function setTelefono($telefono)
    {
        $this->telefono = strtoupper($telefono);
    }
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param mixed $datos
     */
    public function setEmail($email)
    {
        $this->email = strtoupper($email);
    }
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $datos
     */
    public function setDatos($datos)
    {
        $this->datos = strtoupper($datos);
    }

    /**
     * @return mixed
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * @param mixed $direccion
     */
    public function setDireccion($direccion)
    {
        $this->direccion = strtoupper($direccion);
    }

    /**
     * @return mixed
     */
    public function getIdEmpresa()
    {
        return $this->id_empresa;
    }

    /**
     * @param mixed $id_empresa
     */
    public function setIdEmpresa($id_empresa)
    {
        $this->id_empresa = $id_empresa;
    }

    /**
     * @return mixed
     */
    public function getTotalVenta()
    {
        return $this->total_venta;
    }

    /**
     * @param mixed $total_venta
     */
    public function setTotalVenta($total_venta)
    {
        $this->total_venta = $total_venta;
    }

    /**
     * @return mixed
     */
    public function getUltimaVenta()
    {
        return $this->ultima_venta;
    }

    /**
     * @param mixed $ultima_venta
     */
    public function setUltimaVenta($ultima_venta)
    {
        $this->ultima_venta = $ultima_venta;
    }

    public function getDepartamento() { return $this->departamento; }
    public function setDepartamento($departamento) { $this->departamento = strtoupper($departamento); }
    public function getProvincia() { return $this->provincia; }
    public function setProvincia($provincia) { $this->provincia = strtoupper($provincia); }
    public function getDistrito() { return $this->distrito; }
    public function setDistrito($distrito) { $this->distrito = strtoupper($distrito); }
    public function getFechaNacimiento() { return $this->fecha_nacimiento; }
    public function setFechaNacimiento($fecha_nacimiento) { $this->fecha_nacimiento = $fecha_nacimiento; }

    public function insertar()
    {
        // Por defecto el dueno es quien lo crea; el ADMIN puede asignarlo a otro
        $idUsuario = (int) ($_SESSION['usuario_fac'] ?? 0);
        if ($this->id_usuario_nuevo !== null && (int) ($_SESSION['rol'] ?? 0) === 1) {
            $idUsuario = $this->id_usuario_nuevo === 'NULL' ? 'NULL' : (int) $this->id_usuario_nuevo;
        }
        $sqlIdUsuario = ($idUsuario === 'NULL') ? 'NULL' : "'$idUsuario'";
        $sql = "insert into clientes (documento, tipo_documento, datos, direccion, direccion2, telefono, telefono2, email, id_empresa, id_usuario, ultima_venta, total_venta, departamento, provincia, distrito, fecha_nacimiento)
                values ('$this->documento', '$this->tipo_documento', '$this->datos','$this->direccion','$this->direccion2','$this->telefono','$this->telefono2','$this->email', {$_SESSION['id_empresa']}, $sqlIdUsuario, '1000-01-01', '0', '$this->departamento', '$this->provincia', '$this->distrito', ".($this->fecha_nacimiento ? "'$this->fecha_nacimiento'" : "NULL").")";
        $result =  $this->conectar->query($sql);

        if ($result) {
            $this->id_cliente = $this->conectar->insert_id;
        }
        return $result;
    }

    public function modificar($documento, $datos, $id_cliente)
    {

        $sql = "update clientes 
        set documento = '$documento', datos = '$datos' 
        where id_cliente = '$id_cliente'";
        $result =  $this->conectar->query($sql);
        if ($result) {
            $this->id_cliente = $this->conectar->insert_id;
        }
        return $result;
    }

    public function obtenerId()
    {
        $sql = "select ifnull(max(id_cliente) + 1, 1) as codigo from clientes";
        $this->id_cliente = $this->conectar->get_valor_query($sql, 'codigo');
    }

    public function obtenerDatos()
    {
        $sql = "select * 
        from clientes 
        where id_cliente = '$this->id_cliente'";
        $fila = $this->conectar->query($sql)->fetch_assoc();
        $this->documento = $fila['documento'];
        $this->datos = $fila['datos'];
        $this->direccion = $fila['direccion'];
        $this->id_empresa = $fila['id_empresa'];
        $this->ultima_venta = $fila['ultima_venta'];
        $this->total_venta = $fila['total_venta'];
    }

    public function verificarDocumento()
    {
        $sql = "select *
        from clientes 
        where documento = '$this->documento' and id_empresa = '$this->id_empresa'";
        $result = $this->conectar->query($sql);
        if ($row = $result->fetch_assoc()) {
            $this->id_cliente = $row['id_cliente'];
            $this->datos = $row['datos'];
            $this->documento = $row['documento'];
            $this->email = $row['email'];
            $this->telefono = $row['telefono'];
            return true;
        }
        return false;
    }

    /**
     * Completa/actualiza los datos de un cliente que ya existe con lo que el
     * usuario haya escrito. Solo toca los campos que vengan con contenido:
     * lo que se deje en blanco no borra lo que ya estaba guardado.
     */
    public function completarDatos(array $valores)
    {
        if (!$this->id_cliente) {
            return false;
        }

        $permitidos = ['datos', 'tipo_documento', 'direccion', 'direccion2', 'telefono', 'telefono2', 'email'];
        $sets = [];
        foreach ($permitidos as $campo) {
            if (!isset($valores[$campo])) {
                continue;
            }
            $valor = trim((string) $valores[$campo]);
            if ($valor === '') {
                continue;
            }
            $sets[] = "$campo = '" . $this->conectar->real_escape_string($valor) . "'";
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "update clientes set " . implode(', ', $sets) . " where id_cliente = '" . (int) $this->id_cliente . "'";
        return $this->conectar->query($sql);
    }

    public function verFilas()
    {
        $sql = "select * from clientes where id_empresa = '$this->id_empresa'";
        return $this->conectar->query($sql);
    }

    public function buscarClientes($termino)
    {
        $sql = "select * from clientes 
        where id_empresa = '$this->id_empresa' and (datos like '%$termino%' or documento like '%$termino%') 
        order by datos asc";
        return $this->conectar->query($sql);
    }
    public function idLast()
    {

        try {
            $sql = "SELECT id_cliente,documento,datos,direccion,direccion2,telefono,telefono2,email,ultima_venta,total_venta,departamento,provincia,distrito,fecha_nacimiento FROM clientes  ORDER BY id_cliente DESC LIMIT 1";
            $fila = $this->conectar->query($sql)->fetch_object();
            return $fila;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function getAllData()
    {
        try {
            // Solo el ADMIN (rol 1) ve todos los clientes de la empresa,
            // los demas usuarios solo ven los que ellos registraron.
            $filtroUsuario = "";
            if ((int) ($_SESSION['rol'] ?? 0) !== 1) {
                $idUsuario = (int) ($_SESSION['usuario_fac'] ?? 0);
                $filtroUsuario = " and c.id_usuario = '$idUsuario'";
            }
            $sql = "SELECT c.id_cliente,c.documento,c.tipo_documento,
                    CASE c.tipo_documento
                        WHEN '6' THEN 'RUC'
                        WHEN '4' THEN 'C. EXTRANJERIA'
                        WHEN '7' THEN 'PASAPORTE'
                        ELSE 'DNI'
                    END as tipo_documento_desc,
                    c.datos,c.direccion,c.direccion2,c.telefono,c.telefono2,c.email,
                    c.ultima_venta,c.total_venta,c.departamento,c.provincia,c.distrito,c.fecha_nacimiento,
                    c.id_usuario,
                    COALESCE(
                        NULLIF(TRIM(CONCAT(COALESCE(u.nombres,''),' ',COALESCE(u.apellidos,''))),''),
                        NULLIF(TRIM(u.nombres_apellidos),''),
                        u.usuario
                    ) as vendedor
                    FROM clientes c
                    LEFT JOIN usuarios u ON u.usuario_id = c.id_usuario
                    where c.id_empresa='{$_SESSION['id_empresa']}'$filtroUsuario";
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function getOne($id)
    {
        try {
            $filtroUsuario = "";
            if ((int) ($_SESSION['rol'] ?? 0) !== 1) {
                $idUsuario = (int) ($_SESSION['usuario_fac'] ?? 0);
                $filtroUsuario = " and id_usuario = '$idUsuario'";
            }
            $sql = "SELECT * FROM clientes WHERE id_cliente = '$id'$filtroUsuario";
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function cuentasCobrar()
    {
        try {
            $sql = "SELECT ventas.id_venta,ventas.fecha_emision,ventas.fecha_vencimiento,c.datos,dv.estado,dv.dias_venta_id FROM ventas LEFT JOIN dias_ventas AS dv ON
            ventas.id_venta=dv.id_venta 
            LEFT JOIN clientes AS c ON 
            ventas.id_cliente = c.id_cliente 
            WHERE ventas.id_tipo_pago = 2";
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function cuentasCobrarEstado($id)
    {
        try {
            $sql = "UPDATE dias_ventas set estado = 0 WHERE dias_venta_id = $id";
            $result =  $this->conectar->query($sql);
            return $result;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function editar($id)
    {
        $sql = "UPDATE clientes SET datos ='$this->datos',documento ='$this->documento',tipo_documento ='$this->tipo_documento',direccion ='$this->direccion',direccion2 ='$this->direccion2',telefono ='$this->telefono',telefono2 ='$this->telefono2',email='$this->email', departamento='$this->departamento', provincia='$this->provincia', distrito='$this->distrito', fecha_nacimiento=".($this->fecha_nacimiento ? "'$this->fecha_nacimiento'" : "NULL")." WHERE id_cliente = $id";

        // Solo el ADMIN puede cambiar a que usuario pertenece el cliente
        if ($this->id_usuario_nuevo !== null && (int) ($_SESSION['rol'] ?? 0) === 1) {
            $sql = str_replace('SET datos =', "SET id_usuario = {$this->id_usuario_nuevo}, datos =", $sql);
        }

        // Un usuario que no es ADMIN solo puede editar los clientes que el registro
        if ((int) ($_SESSION['rol'] ?? 0) !== 1) {
            $sql .= " AND id_usuario = '" . (int) ($_SESSION['usuario_fac'] ?? 0) . "'";
        }

        $result =  $this->conectar->query($sql);
        return $result;
    }
    public function delete($id)
    {
        try {
            $filtroUsuario = "";
            if ((int) ($_SESSION['rol'] ?? 0) !== 1) {
                $idUsuario = (int) ($_SESSION['usuario_fac'] ?? 0);
                $filtroUsuario = " and id_usuario = '$idUsuario'";
            }
            $sql = "DELETE FROM clientes WHERE  id_cliente = '$id'$filtroUsuario";
            $fila = mysqli_query($this->conectar, $sql);
            return $fila;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
}
