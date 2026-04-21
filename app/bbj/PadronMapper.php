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
    
    private static function parseBbjDate(?string $bbjDate): ?string
    {
        if (empty($bbjDate) || strlen($bbjDate) !== 8) {
            return null;
        }
        
        $anio = substr($bbjDate, 0, 4);
        $mes = substr($bbjDate, 4, 2);
        $dia = substr($bbjDate, 6, 2);
        
        return "{$anio}-{$mes}-{$dia}";
    }

    private static function formatRfc(?string $rfc1, ?string $rfc2, ?string $rfc3): ?string
    {
        $rfc = trim(($rfc1 ?? '') . ($rfc2 ?? '') . ($rfc3 ?? ''));
        return !empty($rfc) ? $rfc : null;
    }

    public static function mapToPdfForm(array $facturaBbj, array $inventarioBbj, array $clienteBbj, array $operacionBbj): array
    {
        $formData = [];

        if (!empty($facturaBbj['FOLIOFISCAL'])) {
            $formData['folio_fiscal'] = $facturaBbj['FOLIOFISCAL'];
        }

        if (!empty($inventarioBbj['SERIE'])) {
            $formData['serie'] = $inventarioBbj['SERIE'];
        }

        if (!empty($inventarioBbj['ID_MODELO'])) {
            $formData['modelo'] = $inventarioBbj['ID_MODELO'];
        }

        if (!empty($inventarioBbj['ANOMOD'])) {
            $formData['anio'] = $inventarioBbj['ANOMOD'];
        }

        if (!empty($inventarioBbj['DESCRIPCION'])) {
            $formData['marca'] = $inventarioBbj['DESCRIPCION'];
        }

        if (!empty($inventarioBbj['DESCCOLEXT'])) {
            $formData['color'] = $inventarioBbj['DESCCOLEXT'];
        }

        if (!empty($inventarioBbj['CVEVEHICULAR'])) {
            $formData['n_orf'] = $inventarioBbj['CVEVEHICULAR'];
        }

        if (!empty($inventarioBbj['CPOS'])) {
            $formData['ndp'] = $inventarioBbj['CPOS'];
        }

        $rfc = self::formatRfc($clienteBbj['RFC1'] ?? null, $clienteBbj['RFC2'] ?? null, $clienteBbj['RFC3'] ?? null);
        if (!empty($rfc)) {
            $formData['rfc'] = $rfc;
        }

        if (!empty($clienteBbj['PATERNO'])) {
            $formData['apellido_paterno'] = $clienteBbj['PATERNO'];
        }

        if (!empty($clienteBbj['MATERNO'])) {
            $formData['apellido_materno'] = $clienteBbj['MATERNO'];
        }

        if (!empty($clienteBbj['NOMBRE'])) {
            $formData['nombre'] = $clienteBbj['NOMBRE'];
        }

        if (!empty($clienteBbj['RAZONSOC'])) {
            $formData['denominacion_razon_social_1'] = $clienteBbj['RAZONSOC'];
        }

        if (!empty($clienteBbj['TIPO_RAZON'])) {
            $formData['regimen_sociedad'] = $clienteBbj['TIPO_RAZON'];
        }

        if (!empty($clienteBbj['CALLE'])) {
            $formData['calle'] = $clienteBbj['CALLE'];
            
            $numero = '';
            if (!empty($clienteBbj['NOEXT'])) {
                $numero = $clienteBbj['NOEXT'];
            }
            if (!empty($clienteBbj['NOINT'])) {
                $numero .= ($numero ? ' ' : '') . $clienteBbj['NOINT'];
            }
            if (!empty($numero)) {
                $formData['no'] = $numero;
            }
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
            $formData['entidad_federativa'] = $clienteBbj['ESTADO'];
        }

        if (!empty($clienteBbj['CP'])) {
            $formData['postal'] = $clienteBbj['CP'];
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

        if (!empty($facturaBbj['FECFAC'])) {
            $fecfac = self::parseBbjDate($facturaBbj['FECFAC']);
            if ($fecfac) {
                $fechaObj = \DateTime::createFromFormat('Y-m-d', $fecfac);
                if ($fechaObj) {
                    $formData['dia'] = $fechaObj->format('d');
                    $formData['mes'] = $fechaObj->format('m');
                    $formData['anio'] = $fechaObj->format('Y');
                    $formData['fecha_final'] = $fecfac;
                }
            }
        }

        if (!empty($facturaBbj['INVENTARIO'])) {
            $formData['no_placa'] = $facturaBbj['INVENTARIO'];
        }

        return $formData;
    }
}
