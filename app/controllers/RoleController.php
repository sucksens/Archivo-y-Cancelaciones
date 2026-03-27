<?php
/**
 * RoleController - Controlador de roles y permisos
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Role;
use App\Models\Permission;
use App\Helpers\ValidationHelper;

class RoleController extends BaseController
{
    private Role $roleModel;
    private Permission $permissionModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    /**
     * Listar roles
     */
    public function index(): void
    {
        $this->requirePermission('admin.roles');

        $roles = $this->roleModel->getAll();

        // Agregar conteo de usuarios por rol
        foreach ($roles as &$role) {
            $role['user_count'] = $this->roleModel->countUsers($role['id']);
        }

        $this->view('admin/roles', [
            'title' => 'Gestión de Roles',
            'roles' => $roles
        ]);
    }

    /**
     * Obtener detalle de rol (AJAX)
     * 
     * @param int $id ID del rol
     */
    public function show(int $id): void
    {
        $this->requirePermission('admin.roles');

        $role = $this->roleModel->find($id);
        if (!$role) {
            $this->json(['error' => 'Rol no encontrado'], 404);
        }

        $role['permissions'] = $this->roleModel->getPermissions($id);
        $role['user_count'] = $this->roleModel->countUsers($id);

        $this->json($role);
    }

    /**
     * Crear nuevo rol
     */
    public function store(): void
    {
        $this->requirePermission('admin.roles');

        try {
            $this->validateCsrf();

            $validator = new ValidationHelper($_POST);
            $validator
                ->required('nombre', 'El nombre del rol es requerido')
                ->maxLength('nombre', 50);

            if ($validator->hasErrors()) {
                if ($this->isAjax()) {
                    $this->json(['error' => $validator->getFirstError()], 400);
                }
                $this->session->flash('error', $validator->getFirstError());
                $this->redirect('/admin/roles');
            }

            // Verificar nombre único
            if ($this->roleModel->findByName($_POST['nombre'])) {
                if ($this->isAjax()) {
                    $this->json(['error' => 'Ya existe un rol con ese nombre'], 400);
                }
                $this->session->flash('error', 'Ya existe un rol con ese nombre');
                $this->redirect('/admin/roles');
            }

            $roleId = $this->roleModel->create([
                'nombre' => ValidationHelper::sanitize($_POST['nombre']),
                'descripcion' => ValidationHelper::sanitize($_POST['descripcion'] ?? ''),
                'nivel' => intval($_POST['nivel'] ?? 0)
            ]);

            // Asignar permisos
            if (!empty($_POST['permissions']) && is_array($_POST['permissions'])) {
                $this->roleModel->syncPermissions($roleId, $_POST['permissions'], $this->userId());
            }

            $this->log('Rol creado', 'roles', "ID: {$roleId}");

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Rol creado correctamente', 'id' => $roleId]);
            }

            $this->session->flash('success', 'Rol creado correctamente');
            $this->redirect('/admin/roles');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Error al crear rol'], 500);
            }
            $this->session->flash('error', 'Error al crear rol');
            $this->redirect('/admin/roles');
        }
    }

    /**
     * Actualizar rol
     * 
     * @param int $id ID del rol
     */
    public function update(int $id): void
    {
        $this->requirePermission('admin.roles');

        try {
            $this->validateCsrf();

            $role = $this->roleModel->find($id);
            if (!$role) {
                if ($this->isAjax()) {
                    $this->json(['error' => 'Rol no encontrado'], 404);
                }
                $this->session->flash('error', 'Rol no encontrado');
                $this->redirect('/admin/roles');
            }

            // No permitir editar rol de sistema
            if ($role['nivel'] >= 100) {
                if ($this->isAjax()) {
                    $this->json(['error' => 'No se puede editar este rol del sistema'], 403);
                }
                $this->session->flash('error', 'No se puede editar este rol del sistema');
                $this->redirect('/admin/roles');
            }

            $validator = new ValidationHelper($_POST);
            $validator
                ->required('nombre', 'El nombre del rol es requerido')
                ->maxLength('nombre', 50);

            if ($validator->hasErrors()) {
                if ($this->isAjax()) {
                    $this->json(['error' => $validator->getFirstError()], 400);
                }
                $this->session->flash('error', $validator->getFirstError());
                $this->redirect('/admin/roles');
            }

            $this->roleModel->update($id, [
                'nombre' => ValidationHelper::sanitize($_POST['nombre']),
                'descripcion' => ValidationHelper::sanitize($_POST['descripcion'] ?? ''),
                'nivel' => intval($_POST['nivel'] ?? $role['nivel'])
            ]);

            // Sincronizar permisos
            $permissions = !empty($_POST['permissions']) && is_array($_POST['permissions']) 
                ? $_POST['permissions'] : [];
            $this->roleModel->syncPermissions($id, $permissions, $this->userId());

            $this->log('Rol actualizado', 'roles', "ID: {$id}");

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Rol actualizado correctamente']);
            }

            $this->session->flash('success', 'Rol actualizado correctamente');
            $this->redirect('/admin/roles');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Error al actualizar rol'], 500);
            }
            $this->session->flash('error', 'Error al actualizar rol');
            $this->redirect('/admin/roles');
        }
    }

    /**
     * Eliminar rol
     * 
     * @param int $id ID del rol
     */
    public function destroy(int $id): void
    {
        $this->requirePermission('admin.roles');

        try {
            $this->validateCsrf();

            $role = $this->roleModel->find($id);
            if (!$role) {
                $this->json(['error' => 'Rol no encontrado'], 404);
            }

            // No permitir eliminar roles del sistema
            if ($role['nivel'] >= 100) {
                $this->json(['error' => 'No se puede eliminar este rol del sistema'], 403);
            }

            // Verificar que no tenga usuarios asignados
            if ($this->roleModel->countUsers($id) > 0) {
                $this->json(['error' => 'No se puede eliminar un rol con usuarios asignados'], 400);
            }

            $this->roleModel->delete($id);
            $this->log('Rol eliminado', 'roles', "ID: {$id}");

            $this->json(['success' => true, 'message' => 'Rol eliminado correctamente']);

        } catch (\Exception $e) {
            $this->json(['error' => 'Error al eliminar rol'], 500);
        }
    }

    /**
     * Listar permisos disponibles
     */
    public function permissions(): void
    {
        $this->requirePermission('admin.permissions');

        $permissions = $this->permissionModel->getAllGrouped();

        $this->view('admin/permissions', [
            'title' => 'Permisos del Sistema',
            'permissionsGrouped' => $permissions
        ]);
    }

    /**
     * Obtener permisos de un rol (AJAX)
     * 
     * @param int $id ID del rol
     */
    public function getRolePermissions(int $id): void
    {
        $this->requirePermission('admin.roles');

        $permissions = $this->roleModel->getPermissionIds($id);
        $this->json($permissions);
    }

    /**
     * Obtener todos los permisos agrupados (AJAX)
     */
    public function getAllPermissions(): void
    {
        $this->requirePermission('admin.roles');

        $permissions = $this->permissionModel->getAllGrouped();
        $this->json($permissions);
    }
}
