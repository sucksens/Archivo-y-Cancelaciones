<?php
/**
 * PadronController - Controlador para generar PDF de padrón V1J AUTO
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\FacturaArchivo;
use App\Helpers\PermissionHelper;
use App\Helpers\ValidationHelper;
use App\BBj\FacturasBridge;
use App\BBj\PadronMapper;

class PadronController extends BaseController
{
    private FacturaArchivo $facturaModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->facturaModel = new FacturaArchivo();
    }

    /**
     * Generar PDF de padrón V1J AUTO
     * 
     * @param int $id ID de la factura
     */
    public function generar(int $id): void
    {
        $this->requirePermission('facturas.padron.generar');

        $factura = $this->facturaModel->find($id);

        if (!$factura) {
            $this->log('Factura no encontrada al intentar generar padrón', 'padron', "ID Factura: {$id}");
            $this->session->flash('error', 'Factura no encontrada');
            $this->redirect('/facturas');
        }

        $userCompany = PermissionHelper::getUserCompany();
        $canView = PermissionHelper::hasPermission('facturas.view.all')
                || (PermissionHelper::hasPermission('facturas.view.own') && $factura['usuario_id'] === $this->userId())
                || (PermissionHelper::hasPermission('facturas.view.empresa') && 
                    ($factura['empresa'] === $userCompany || $userCompany === 'ambas'));

        if (!$canView) {
            $this->log('Intento de acceso no autorizado a generar padrón', 'padron', 
                "ID Factura: {$id}, Usuario ID: {$this->userId()}");
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }

        try {
            $dbMapping = [
                'grupo_motormexa' => [
                    'autos_nuevos' => '01AN_AUTOSNUEVOS',
                    'seminuevos' => '01AU_SEMINUEVOS'
                ],
                'automotriz_motormexa' => [
                    'autos_nuevos' => '02AN_AUTOSNUEVOS',
                    'seminuevos' => '02AU_SEMINUEVOS'
                ]
            ];

            $dbName = $dbMapping[$factura['empresa']][$factura['tipo_factura']] ?? '01AN_AUTOSNUEVOS';
            
            $facturaBridge = new FacturasBridge($dbName);
            
            $facturaBbj = $facturaBridge->getFactura($factura['uuid_factura']);

            if (!$facturaBbj) {
                $this->log('Factura no encontrada en sistema administrativo BBj', 'padron', 
                    "ID Factura: {$id}, Empresa: {$factura['empresa']}");
                throw new \Exception("La factura no fue encontrada en el sistema administrativo ({$factura['empresa']})");
            }

            $inventarioBbj = [];
            if (!empty($facturaBbj['INVENTARIO'])) {
                $inventarioBbj = $facturaBridge->getDatosInventario($facturaBbj['INVENTARIO']) ?? [];
            }

            $clienteBbj = [];
            if (!empty($facturaBbj['ID_PEDIDO']) && !empty($facturaBbj['ID_VENDEDOR'])) {
                $clienteBbj = $facturaBridge->getDatosCliente($facturaBbj['ID_PEDIDO'], $facturaBbj['ID_VENDEDOR']) ?? [];
            }

            $operacionBbj = [];
            if (!empty($facturaBbj['ID_PEDIDO']) && !empty($facturaBbj['ID_VENDEDOR'])) {
                $operacionBbj = $facturaBridge->getDatosOperacion($facturaBbj['ID_PEDIDO'], $facturaBbj['ID_VENDEDOR']) ?? [];
            }

            // Obtener el número de motor según empresa y tipo de factura
            $motor = "";
            if ($factura['empresa'] == 'automotriz_motormexa') {
                // Mitsubishi
                if ($factura['tipo_factura'] == 'seminuevos') {
                    $motor = $inventarioBbj['MOTOR'] ?? '';
                } else {
                    // nuevos
                    $motor = $inventarioBbj['NOMOTOR'] ?? '';
                }
            } else {
                // Grupo
                if ($factura['tipo_factura'] == 'seminuevos') {
                    $motor = $inventarioBbj['MOTOR'] ?? '';
                } else {
                    // nuevos grupo - consultar MODELOGRAL para obtener texto de procedencia
                    if (!empty($inventarioBbj['ID_MODELO'])) {
                        $motor = $facturaBridge->getMotorGrupo($inventarioBbj['ID_MODELO']);
                    }
                }
            }

            $marca = "";
            if (!empty($inventarioBbj['ID_MODELO'])) {
                $marca = $facturaBridge->getMarca($inventarioBbj['ID_MODELO']);
            }


            $formData = PadronMapper::mapToPdfForm($facturaBbj, $inventarioBbj, $clienteBbj, $operacionBbj, $factura['empresa'], $factura['tipo_factura'], $motor, $marca);

            $apiUrl = 'http://200.1.1.245:5000/llenar_padron/';
            
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($formData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/pdf'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \Exception('Error al conectar con el servidor de generación de PDF: ' . $curlError);
            }

            if ($httpCode !== 200) {
                $errorMsg = "El servidor respondió con código HTTP {$httpCode}";
                if ($response) {
                    $decoded = json_decode($response, true);
                    if ($decoded && isset($decoded['detail'])) {
                        // Manejar errores de validación422 donde detail puede ser array
                        if (is_array($decoded['detail'])) {
                            // Formato FastAPI: array de objetos con "msg", "loc", "type"
                            $messages = [];
                            foreach ($decoded['detail'] as $error) {
                                if (isset($error['msg'])) {
                                    $field = isset($error['loc']) && is_array($error['loc'])
                                        ? implode('.', array_slice($error['loc'], 1))
                                        : '';
                                    $messages[] = $field ? "{$field}: {$error['msg']}" : $error['msg'];
                                } else {
                                    $messages[] = json_encode($error, JSON_UNESCAPED_UNICODE);
                                }
                            }
                            $errorMsg .= ': ' . implode('; ', $messages);
                        } else {
                            $errorMsg .= ': ' . $decoded['detail'];
                        }
                    } else {
                        $errorMsg .= ': ' . substr($response, 0, 200);
                    }
                }
                throw new \Exception($errorMsg);
            }

            if (!$response) {
                throw new \Exception('No se recibió respuesta del servidor de generación de PDF');
            }

            $filename = 'padron_v1j_' . ($factura['uuid_factura'] ?? 'factura') . '.pdf';

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($response));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            echo $response;
            exit;

        } catch (\Exception $e) {
            $this->log('Error al generar PDF de padrón', 'padron', 
                "ID Factura: {$id}, Error: " . $e->getMessage());
            
            $this->session->flash('error', 'Error al generar el PDF. Por favor, intenta de nuevo o contacta a soporte.');
            $this->redirect('/facturas/' . $id);
        }
    }
}
