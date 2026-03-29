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
}
