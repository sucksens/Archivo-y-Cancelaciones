<?php
/**
 * FacturasBridge - Este Puente es el puente con informacion de facturas desde bbj segun la empresa solicitante
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\BBj;

use App\Core\DatabaseBBj;

class FacturasBridge{

      private string $DatabaseName;
      private DatabaseBBj $db;

      /**
       * Constructor de la clase
       * 
       * @param string $DatabaseName
       * se pasa el nombre de la base de datos de bbj en la cual se buscaran operaciones de la factura
       */
      public function __construct(string $DatabaseName){
          $this->DatabaseName = $DatabaseName;
          $this->db = DatabaseBBj::getInstance($this->DatabaseName);
      }

       /**
        * Obtiene la factura de bbj
        * 
        * @param string $uuid
        * @return array|null
        */
      public function getFactura(string $uuid): ?array
      {
            $sql = "SELECT * FROM FACTURAS WHERE FOLIOFISCAL = ? AND STATUS = '0' ";
            return $this->db->fetchOne($sql, [$uuid]);
      }

      /**
      * Obtiene los recibos de caja de la factura
      * 
      * @param string $vendedor
      * @param string $pedido
      * @return array
      */
      public function getRecibosCaja(string $vendedor, string $pedido): array
      {
            $sql = "SELECT * FROM PEDCOMPAGOS WHERE ID_VENDEDOR = ? AND ID_PEDIDO = ?";
            return $this->db->fetchAll($sql, [$vendedor, $pedido]);
      }

      /**
      * Obtiene los documentos relacionados de la factura
      * 
      * @param string $vendedor
      * @param string $pedido
      * @return array
      */
      public function getDoctosRelacionados(string $vendedor, string $pedido): array
      {
            $sql = "SELECT * FROM NCANTICIPOS WHERE ID_VENDEDOR = ? AND ID_PEDIDO = ? AND STATUS = '0' ";
            return $this->db->fetchAll($sql, [$vendedor, $pedido]);
      }

      /**
      * Obtiene datos del inventario del vehículo
      * 
      * @param string $inventario
      * @return array|null
      */
      public function getDatosInventario(string $inventario): ?array
      {
            $sql = "SELECT ID_MARCA,ANOMOD,DESCCOLEXT,SERIE,PEDIMENTO FROM INV_VENDIDOS WHERE INVENTARIO = ?";
            return $this->db->fetchOne($sql, [$inventario]);
      }

      /**
      * Obtiene datos del cliente del pedido
      * 
      * @param string $idPedido
      * @param string $idVendedor
      * @return array|null
      */
      public function getDatosCliente(string $idPedido, string $idVendedor): ?array
      {
            $sql = "SELECT * FROM PEDFACTURA WHERE ID_PEDIDO = ? AND ID_VENDEDOR = ?";
            return $this->db->fetchOne($sql, [$idPedido, $idVendedor]);
      }

      /**
      * Obtiene datos de la operación (prioriza FACOPERACION, fallback a PEDOPERACION)
      * 
      * @param string $idPedido
      * @param string $idVendedor
      * @return array|null
      */
      public function getDatosOperacion(string $idPedido, string $idVendedor): ?array
      {
            $sql = "SELECT * FROM FACOPERACION WHERE ID_PEDIDO = ? AND ID_VENDEDOR = ?";
            $result = $this->db->fetchOne($sql, [$idPedido, $idVendedor]);
            
            if (!$result) {
                  $sql = "SELECT * FROM PEDOPERACION WHERE ID_PEDIDO = ? AND ID_VENDEDOR = ?";
                  $result = $this->db->fetchOne($sql, [$idPedido, $idVendedor]);
            }
            
            return $result;
      }
}
