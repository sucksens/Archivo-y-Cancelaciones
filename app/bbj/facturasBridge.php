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
            $sql = "SELECT * FROM INV_VENDIDOS WHERE INVENTARIO = ?";
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

      /**
       * Mapeo de códigos de procedencia a texto descriptivo
       */
      private const PROCEDENCIAS = [
            'ARG' => 'HECHO EN ARGENTINA',
            'BRA' => 'HECHO EN BRASIL',
            'CAN' => 'HECHO EN CANADA',
            'CHN' => 'HECHO EN CHINA',
            'CRA' => 'HECHO EN COREA',
            'ESP' => 'HECHO EN ESPAÑA',
            'EUM' => 'HECHO EN MEXICO',
            'FRA' => 'HECHO EN FRANCIA',
            'GMA' => 'HECHO EN ALEMANIA',
            'HLD' => 'HECHO EN HOLANDA',
            'IDA' => 'HECHO EN INDIA',
            'IND' => 'HECHO EN INDONESIA',
            'ITA' => 'HECHO EN ITALIA',
            'JPN' => 'HECHO EN JAPON',
            'MAL' => 'HECHO EN MALASIA',
            'NID' => 'HECHO EN NO IDENTIFICADO',
            'TAI' => 'HECHO EN TAILANDIA',
            'TUR' => 'HECHO EN TURQUIA',
            'USA' => 'HECHO EN U.S.A.',
      ];

      /**
       * Obtiene el modelo del vehículo
       *
       * @param string $idmodelo
       * @return string
       */
      public function getModelo(string $idmodelo): string
      {
            $sql = "SELECT * FROM MODELOGRAL WHERE ID_MODELO = ?";
            $datos = $this->db->fetchOne($sql, [$idmodelo]);
            return $datos['DESCRIPCION'] ?? '';
      }


      /**
       * Obtiene datos del motor del vehículo (texto de procedencia)
       *
       * @param string $idmodelo
       * @return string
       */
      public function getMotorGrupo(string $idmodelo): string
      {
            $sql = "SELECT ID_PROCEDENCIA FROM MODELOGRAL WHERE ID_MODELO = ?";
            $datos = $this->db->fetchOne($sql, [$idmodelo]);
            
            if (empty($datos) || empty($datos['ID_PROCEDENCIA'])) {
                  return '';
            }
            
            return self::PROCEDENCIAS[$datos['ID_PROCEDENCIA']] ?? '';
      }
}
