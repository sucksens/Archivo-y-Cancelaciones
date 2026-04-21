<?php
/**
 * PadronMapper - Mapea datos de BBj al formulario PDF V1J AUTO
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\BBj;

class PadronMapper
{
    

    private static function formatRfc(?string $rfc1, ?string $rfc2, ?string $rfc3): ?string
    {
        $rfc = trim(($rfc1 ?? '') . ($rfc2 ?? '') . ($rfc3 ?? ''));
        return !empty($rfc) ? $rfc : null;
    }

    public static function mapToPdfForm(array $facturaBbj, array $inventarioBbj, array $clienteBbj, array $operacionBbj): array
    {
        $formData = [];

        //Datos de l inventario
        //serie,anomod,id_marca,desccolext,pedimento
        //DATOS VEHICULO
        if (!empty($inventarioBbj['SERIE'])) {
            $formData['serie'] = $inventarioBbj['SERIE'];
        }

        if (!empty($inventarioBbj['ANOMOD'])) {
            $formData['anio'] = $inventarioBbj['ANOMOD'];
        }

        if (!empty($inventarioBbj['ID_MARCA'])) {
            $formData['marca'] = $inventarioBbj['ID_MARCA'];
        }

        if (!empty($inventarioBbj['DESCCOLEXT'])) {
            $formData['color'] = $inventarioBbj['DESCCOLEXT'];
        }

        if (!empty($inventarioBbj['PEDIMENTO'])) {
            $formData['pedimento'] = $inventarioBbj['PEDIMENTO'];
        }

        if (!empty($facturaBbj['FOLIOFISCAL'])) {
            $formData['folio_fiscal'] = $facturaBbj['FOLIOFISCAL'];
        }

        //DATOS IDENTIFICACION
        //rfc,fisica o moral
        //fisica:paterno,materno,nombre
        //moral:razonsoc,tipo_razon
        $rfc = self::formatRfc($clienteBbj['RFC1'] ?? null, $clienteBbj['RFC2'] ?? null, $clienteBbj['RFC3'] ?? null);
        if (!empty($rfc)) {
            $formData['rfc'] = $rfc;
        }

        //revision de si es fisica o moral
        if ($clienteBbj['FISMOR'] == 'F') {
            //llenado de datos de persona fisica
            if (!empty($clienteBbj['PATERNO'])) {
                $formData['apellido_paterno'] = $clienteBbj['PATERNO'];
            }

            if (!empty($clienteBbj['MATERNO'])) {
                $formData['apellido_materno'] = $clienteBbj['MATERNO'];
            }

            if (!empty($clienteBbj['NOMBRE'])) {
                $formData['nombre'] = $clienteBbj['NOMBRE'];
            }
        }else {
            //llenado de datos de persona moral
            if (!empty($clienteBbj['RAZONSOC'])) {
                $formData['denominacion_razon_social_1'] = $clienteBbj['RAZONSOC'];
            }

            if (!empty($clienteBbj['TIPO_RAZON'])) {
                $formData['regimen_sociedad'] = $clienteBbj['TIPO_RAZON'];
            }
        }
    
        //DATOS DOMICILIO
        if (!empty($clienteBbj['CALLE'])) {
            $formData['calle'] = $clienteBbj['CALLE'];
        }
            
        if (!empty($clienteBbj['NOEXT'])) {
            $formData['ext'] = $clienteBbj['NOEXT'];
        }

        if (!empty($clienteBbj['NOINT'])) {
            $formData['int'] = $clienteBbj['NOINT'];
        }

        if (!empty($clienteBbj['COLONIA'])) {
            $formData['col'] = $clienteBbj['COLONIA'];
        }

        if (!empty($clienteBbj['CIUDAD'])) {
            $formData['localidad'] = $clienteBbj['CIUDAD'];
            $formData['mpo'] = $clienteBbj['CIUDAD'];
        }

        if (!empty($clienteBbj['ESTADO'])) {
            $formData['entidad'] = $clienteBbj['ESTADO'];
        }

        if (!empty($clienteBbj['CP'])) {
            $formData['cp'] = $clienteBbj['CP'];
        }

        if (!empty($clienteBbj['CEL'])) {
            $formData['telefono'] = $clienteBbj['CEL'];
        } elseif (!empty($clienteBbj['TEL1'])) {
            $lada1 = $clienteBbj['LADA1'] ?? '';
            $tel1 = $clienteBbj['TEL1'] ?? '';
            $formData['telefono'] = ($lada1 ? $lada1 . ' ' : '') . $tel1;
        }

        if (!empty($clienteBbj['EMAIL'])) {
            $formData['correo_1'] = $clienteBbj['EMAIL'];
        }

        return $formData;
    }
}
