<?php

class VentaAnulada
{
    private $id_venta;
    private $fecha;
    private $motivo;
    private $conectar;

    /**
     * VentaAnulada constructor.
     */
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    /**
     * @return mixed
     */
    public function getIdVenta()
    {
        return $this->id_venta;
    }

    /**
     * @param mixed $id_venta
     */
    public function setIdVenta($id_venta)
    {
        $this->id_venta = $id_venta;
    }

    /**
     * @return mixed
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param mixed $fecha
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    }

    /**
     * @return mixed
     */
    public function getMotivo()
    {
        return $this->motivo;
    }

    /**
     * @param mixed $motivo
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;
    }

    public function insertar()
    {
        $sql = "insert into ventas_anuladas 
        values ('$this->id_venta', '$this->fecha', '$this->motivo')";
        $resulta=  $this->conectar->query($sql);

        $sql="select * from productos_ventas where id_venta='$this->id_venta'";

        $listaVP = $this->conectar->query($sql);

        foreach ($listaVP as $item){
            $sql="update productos set  cantidad= cantidad+'{$item['cantidad']}' where id_producto='{$item['id_producto']}' ";
            //echo $sql;
            $this->conectar->query($sql);
        }

        return $resulta;
    }

    public function verFacturasAnuladas($id_empresa)
    {
        $sql = "select v.id_venta, v.fecha, va.fecha as fecha_anulado, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas_anuladas as va 
            inner join ventas as v on v.id_venta = va.id_venta 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '$id_empresa' and v.fecha = '$this->fecha' and v.id_tido = 2 ";
        return $this->conectar->get_Cursor($sql);
    }
}