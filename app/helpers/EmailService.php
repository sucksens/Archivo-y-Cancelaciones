<?php
/**
 * Helper EmailService - Envío de facturas por correo vía API interna
 * Sistema de Tickets de Cancelación
 *
 * Llama a: POST http://200.1.1.245:5000/enviar_archivos_por_correo/
 * Multipart/form-data: xml (file), pdf (file), email_destino, asunto, mensaje_cuerpo
 *
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Helpers;

class EmailService
{
    private const API_BASE_URL = 'http://200.1.1.245:5000';
    private const API_ENDPOINT = '/enviar_archivos_por_correo/';
    private const TIMEOUT = 60;
    private const CONNECT_TIMEOUT = 15;

    /**
     * Enviar factura por correo adjuntando XML y PDF.
     *
     * @param string $xmlPath    Ruta absoluta del archivo XML
     * @param string $pdfPath    Ruta absoluta del archivo PDF (puede estar vacío)
     * @param string $emailDest  Correo destinatario
     * @param string $asunto     Asunto del correo
     * @param string $cuerpo     Cuerpo del mensaje (opcional)
     * @return array { exito: bool, mensaje: string, error: string|null, id_operacion: string|null }
     * @throws \Exception Si falla la conexión con la API
     */
    public function send(
        string $xmlPath,
        string $pdfPath,
        string $emailDest,
        string $asunto,
        string $cuerpo = 'Se adjuntan los archivos de la factura.'
    ): array {
        if (!file_exists($xmlPath)) {
            throw new \Exception("Archivo XML no encontrado: {$xmlPath}");
        }

        $postFields = [
            'xml'           => curl_file_create($xmlPath, 'text/xml', basename($xmlPath)),
            'email_destino' => $emailDest,
            'asunto'        => $asunto,
            'mensaje_cuerpo'=> $cuerpo,
        ];

        // El PDF es requerido por la API, usamos el XML como fallback si no existe PDF
        if (!empty($pdfPath) && file_exists($pdfPath)) {
            $postFields['pdf'] = curl_file_create($pdfPath, 'application/pdf', basename($pdfPath));
        } else {
            // Si no hay PDF adjuntamos el XML también en el campo pdf como fallback
            $postFields['pdf'] = curl_file_create($xmlPath, 'text/xml', basename($xmlPath));
        }

        $apiUrl = self::API_BASE_URL . self::API_ENDPOINT;

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $postFields,
            CURLOPT_TIMEOUT         => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT  => self::CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_MAXREDIRS       => 3,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        curl_close($ch);

        error_log("=== EmailService::send DEBUG ===");
        error_log("Destino: {$emailDest} | Asunto: {$asunto}");
        error_log("HTTP Code: {$httpCode}");
        error_log("cURL Error: " . ($curlError ?: 'None'));
        error_log("Response (500): " . substr($response ?? '', 0, 500));

        if ($curlError || $curlErrno) {
            throw new \Exception("Error de conexión con la API de email: {$curlError} (errno {$curlErrno})");
        }

        $decoded = json_decode($response, true);

        if ($httpCode === 200 && $decoded) {
            return [
                'exito'        => (bool) ($decoded['exito'] ?? false),
                'mensaje'      => $decoded['mensaje'] ?? '',
                'error'        => $decoded['error'] ?? null,
                'id_operacion' => $decoded['id_operacion'] ?? null,
            ];
        }

        // Intentar extraer mensaje de error del cuerpo
        $errorMsg = 'Error HTTP ' . $httpCode;
        if ($decoded && isset($decoded['detail'])) {
            // Formato 422 Validation Error
            $errorMsg .= ': ' . (is_array($decoded['detail'])
                ? implode(', ', array_column($decoded['detail'], 'msg'))
                : $decoded['detail']);
        } elseif ($response) {
            $errorMsg .= ' – ' . substr($response, 0, 200);
        }

        throw new \Exception($errorMsg);
    }
}
