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

    public function __construct(string $DatabaseName){
        $this->DatabaseName = $DatabaseName;
        $this->db = DatabaseBBj::getInstance($this->DatabaseName);
   }

   public function getFactura(string $uuid){
        $sql = "SELECT * FROM FACTURAS WHERE FOLIOFISCAL = ?";
        return $this->db->fetchOne($sql, [$uuid]);
   }

   public function getDoctosRelacionados(string $vendedor, string $pedido){
        $sql = "SELECT * FROM PEDCOMPAGOS WHERE ID_VENDEDOR = ? AND ID_PEDIDO = ?";
        return $this->db->fetchAll($sql, [$vendedor, $pedido]);
   }
}
