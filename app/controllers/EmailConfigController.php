<?php
/**
 * EmailConfigController - Gestión de whitelist de correos y blacklist de dominios
 * Sistema de Tickets de Cancelación
 *
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\EmailConfig;
use App\Helpers\PermissionHelper;

class EmailConfigController extends BaseController
{
    private EmailConfig $emailConfig;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->emailConfig = new EmailConfig();
    }

    /**
     * Mostrar whitelist y blacklist
     */
    public function index(): void
    {
        $this->requirePermission('facturas.email.manage_whitelist');

        $this->log('Ver config email', 'email_config', 'Acceso a gestión de whitelist/blacklist');

        $this->view('email_config/index', [
            'title'           => 'Configuración de Email',
            'whitelist'       => $this->emailConfig->getWhitelist(),
            'domainBlacklist' => $this->emailConfig->getDomainBlacklist(),
        ]);
    }

    // =========================================================
    // WHITELIST
    // =========================================================

    /**
     * Agregar correo a whitelist
     */
    public function storeWhitelist(): void
    {
        $this->requirePermission('facturas.email.manage_whitelist');

        try {
            $this->validateCsrf();

            $email = trim($this->input('email') ?? '');
            $descripcion = trim($this->input('descripcion') ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Correo electrónico inválido.');
            }

            $this->emailConfig->addToWhitelist($email, $descripcion, $this->userId());

            $this->log('Whitelist: agregar correo', 'email_config', "Email: {$email}");
            $this->session->flash('success', "Correo {$email} agregado a la whitelist.");

        } catch (\Exception $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate')
                ? 'Ese correo ya existe en la whitelist.'
                : $e->getMessage();
            $this->session->flash('error', $msg);
        }

        $this->redirect('/admin/email-config');
    }

    /**
     * Activar/desactivar correo en whitelist
     */
    public function toggleWhitelist(int $id): void
    {
        $this->requirePermission('facturas.email.manage_whitelist');

        try {
            $this->validateCsrf();

            $entry = $this->emailConfig->findWhitelist($id);
            if (!$entry) {
                throw new \Exception('Registro no encontrado.');
            }

            $this->emailConfig->toggleWhitelist($id);
            $nuevoEstado = $entry['activo'] ? 'desactivado' : 'activado';

            $this->log('Whitelist: toggle correo', 'email_config', "ID: {$id}, Email: {$entry['email']}, Estado: {$nuevoEstado}");
            $this->session->flash('success', "Correo {$entry['email']} {$nuevoEstado}.");

        } catch (\Exception $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/email-config');
    }

    /**
     * Eliminar correo de whitelist
     */
    public function destroyWhitelist(int $id): void
    {
        $this->requirePermission('facturas.email.manage_whitelist');

        try {
            $this->validateCsrf();

            $entry = $this->emailConfig->findWhitelist($id);
            if (!$entry) {
                throw new \Exception('Registro no encontrado.');
            }

            $this->emailConfig->removeFromWhitelist($id);
            $this->log('Whitelist: eliminar correo', 'email_config', "ID: {$id}, Email: {$entry['email']}");
            $this->session->flash('success', "Correo {$entry['email']} eliminado de la whitelist.");

        } catch (\Exception $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/email-config');
    }

    // =========================================================
    // BLACKLIST DE DOMINIOS
    // =========================================================

    /**
     * Agregar dominio a blacklist
     */
    public function storeBlacklist(): void
    {
        $this->requirePermission('facturas.email.manage_whitelist');

        try {
            $this->validateCsrf();

            $dominio = strtolower(trim($this->input('dominio') ?? ''));
            $motivo  = trim($this->input('motivo') ?? '');

            // Validar formato básico de dominio (sin @, sin espacios)
            if (empty($dominio) || !preg_match('/^[a-z0-9\-\.]+\.[a-z]{2,}$/', $dominio)) {
                throw new \Exception('Dominio inválido. Ejemplo: tempmail.com');
            }

            $this->emailConfig->addDomainBlacklist($dominio, $motivo, $this->userId());
            $this->log('Blacklist: agregar dominio', 'email_config', "Dominio: {$dominio}");
            $this->session->flash('success', "Dominio @{$dominio} agregado a la blacklist.");

        } catch (\Exception $e) {
            $msg = str_contains($e->getMessage(), 'Duplicate')
                ? 'Ese dominio ya está en la blacklist.'
                : $e->getMessage();
            $this->session->flash('error', $msg);
        }

        $this->redirect('/admin/email-config');
    }

    /**
     * Eliminar dominio de blacklist
     */
    public function destroyBlacklist(int $id): void
    {
        $this->requirePermission('facturas.email.manage_whitelist');

        try {
            $this->validateCsrf();

            $entry = $this->emailConfig->findBlacklist($id);
            if (!$entry) {
                throw new \Exception('Registro no encontrado.');
            }

            $this->emailConfig->removeDomainBlacklist($id);
            $this->log('Blacklist: eliminar dominio', 'email_config', "ID: {$id}, Dominio: {$entry['dominio']}");
            $this->session->flash('success', "Dominio @{$entry['dominio']} eliminado de la blacklist.");

        } catch (\Exception $e) {
            $this->session->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/email-config');
    }
}
