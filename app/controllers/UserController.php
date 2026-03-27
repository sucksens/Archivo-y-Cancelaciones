<?php
/**
 * UserController - Controlador de usuarios
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\User;
use App\Models\Role;
use App\Helpers\ValidationHelper;

class UserController extends BaseController
{
    private User $userModel;
    private Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    /**
     * Listar usuarios
     */
    public function index(): void
    {
        $this->requirePermission('admin.users');

        $page = (int) ($this->input('page') ?? 1);
        $filters = [
            'empresa' => $this->input('empresa'),
            'activo' => $this->input('activo'),
            'search' => $this->input('search')
        ];

        $result = $this->userModel->getAll($filters, $page);

        $this->view('users/index', [
            'title' => 'Gestión de Usuarios',
            'users' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'empresas' => EMPRESAS
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): void
    {
        $this->requirePermission('admin.users');

        $roles = $this->roleModel->getAll();

        $this->view('users/create', [
            'title' => 'Nuevo Usuario',
            'roles' => $roles,
            'empresas' => EMPRESAS
        ]);
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(): void
    {
        $this->requirePermission('admin.users');

        try {
            $this->validateCsrf();

            $validator = new ValidationHelper($_POST);
            $validator
                ->required('username', 'El usuario es requerido')
                ->minLength('username', 3)
                ->maxLength('username', 50)
                ->required('email', 'El email es requerido')
                ->email('email')
                ->required('nombre_completo', 'El nombre completo es requerido')
                ->required('password', 'La contraseña es requerida')
                ->minLength('password', 8)
                ->required('empresa', 'La empresa es requerida')
                ->in('empresa', array_keys(EMPRESAS));

            if ($validator->hasErrors()) {
                $this->session->flash('error', $validator->getFirstError());
                $this->session->set('old_input', $_POST);
                $this->redirect('/usuarios/crear');
            }

            // Verificar unicidad
            if ($this->userModel->usernameExists($_POST['username'])) {
                $this->session->flash('error', 'El nombre de usuario ya existe');
                $this->redirect('/usuarios/crear');
            }

            if ($this->userModel->emailExists($_POST['email'])) {
                $this->session->flash('error', 'El email ya está registrado');
                $this->redirect('/usuarios/crear');
            }

            // Crear usuario
            $userId = $this->userModel->create([
                'username' => ValidationHelper::sanitize($_POST['username']),
                'email' => ValidationHelper::sanitize($_POST['email']),
                'password' => $_POST['password'],
                'nombre_completo' => ValidationHelper::sanitize($_POST['nombre_completo']),
                'empresa' => $_POST['empresa'],
                'departamento' => ValidationHelper::sanitize($_POST['departamento'] ?? ''),
                'activo' => isset($_POST['activo']) ? 1 : 0
            ]);

            // Asignar roles
            if (!empty($_POST['roles']) && is_array($_POST['roles'])) {
                $this->userModel->syncRoles($userId, $_POST['roles'], $this->userId());
            }

            $this->log('Usuario creado', 'usuarios', "ID: {$userId}");
            $this->session->remove('old_input');
            $this->session->flash('success', 'Usuario creado correctamente');
            $this->redirect('/usuarios');

        } catch (\Exception $e) {
            $this->session->flash('error', 'Error al crear usuario');
            $this->redirect('/usuarios/crear');
        }
    }

    /**
     * Mostrar formulario de edición
     * 
     * @param int $id ID del usuario
     */
    public function edit(int $id): void
    {
        $this->requirePermission('admin.users');

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->session->flash('error', 'Usuario no encontrado');
            $this->redirect('/usuarios');
        }

        $roles = $this->roleModel->getAll();
        $userRoles = $this->userModel->getRoles($id);
        $userRoleIds = array_column($userRoles, 'id');

        $this->view('users/edit', [
            'title' => 'Editar Usuario',
            'userData' => $user,
            'roles' => $roles,
            'userRoleIds' => $userRoleIds,
            'empresas' => EMPRESAS
        ]);
    }

    /**
     * Actualizar usuario
     * 
     * @param int $id ID del usuario
     */
    public function update(int $id): void
    {
        $this->requirePermission('admin.users');

        try {
            $this->validateCsrf();

            $user = $this->userModel->find($id);
            if (!$user) {
                $this->session->flash('error', 'Usuario no encontrado');
                $this->redirect('/usuarios');
            }

            $validator = new ValidationHelper($_POST);
            $validator
                ->required('username', 'El usuario es requerido')
                ->required('email', 'El email es requerido')
                ->email('email')
                ->required('nombre_completo', 'El nombre completo es requerido')
                ->required('empresa', 'La empresa es requerida')
                ->in('empresa', array_keys(EMPRESAS));

            if ($validator->hasErrors()) {
                $this->session->flash('error', $validator->getFirstError());
                $this->redirect('/usuarios/' . $id . '/editar');
            }

            // Verificar unicidad
            if ($this->userModel->usernameExists($_POST['username'], $id)) {
                $this->session->flash('error', 'El nombre de usuario ya existe');
                $this->redirect('/usuarios/' . $id . '/editar');
            }

            if ($this->userModel->emailExists($_POST['email'], $id)) {
                $this->session->flash('error', 'El email ya está registrado');
                $this->redirect('/usuarios/' . $id . '/editar');
            }

            // Preparar datos
            $updateData = [
                'username' => ValidationHelper::sanitize($_POST['username']),
                'email' => ValidationHelper::sanitize($_POST['email']),
                'nombre_completo' => ValidationHelper::sanitize($_POST['nombre_completo']),
                'empresa' => $_POST['empresa'],
                'departamento' => ValidationHelper::sanitize($_POST['departamento'] ?? ''),
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];

            // Cambiar contraseña si se proporciona
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 8) {
                    $this->session->flash('error', 'La contraseña debe tener al menos 8 caracteres');
                    $this->redirect('/usuarios/' . $id . '/editar');
                }
                $updateData['password'] = $_POST['password'];
            }

            $this->userModel->update($id, $updateData);

            // Sincronizar roles
            $roles = !empty($_POST['roles']) && is_array($_POST['roles']) ? $_POST['roles'] : [];
            $this->userModel->syncRoles($id, $roles, $this->userId());

            $this->log('Usuario actualizado', 'usuarios', "ID: {$id}");
            $this->session->flash('success', 'Usuario actualizado correctamente');
            $this->redirect('/usuarios');

        } catch (\Exception $e) {
            $this->session->flash('error', 'Error al actualizar usuario');
            $this->redirect('/usuarios/' . $id . '/editar');
        }
    }

    /**
     * Eliminar usuario (desactivar)
     * 
     * @param int $id ID del usuario
     */
    public function destroy(int $id): void
    {
        $this->requirePermission('admin.users');

        try {
            $this->validateCsrf();

            // No permitir eliminar al propio usuario
            if ($id === $this->userId()) {
                if ($this->isAjax()) {
                    $this->json(['error' => 'No puedes eliminar tu propio usuario'], 400);
                }
                $this->session->flash('error', 'No puedes eliminar tu propio usuario');
                $this->redirect('/usuarios');
            }

            $this->userModel->delete($id);
            $this->log('Usuario desactivado', 'usuarios', "ID: {$id}");

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Usuario desactivado']);
            }

            $this->session->flash('success', 'Usuario desactivado correctamente');
            $this->redirect('/usuarios');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Error al desactivar usuario'], 500);
            }
            $this->session->flash('error', 'Error al desactivar usuario');
            $this->redirect('/usuarios');
        }
    }

    /**
     * Activar/Desactivar usuario (toggle)
     * 
     * @param int $id ID del usuario
     */
    public function toggleStatus(int $id): void
    {
        $this->requirePermission('admin.users');

        try {
            $this->validateCsrf();

            $user = $this->userModel->find($id);
            if (!$user) {
                $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $newStatus = $user['activo'] ? 0 : 1;
            $this->userModel->update($id, ['activo' => $newStatus]);

            $statusText = $newStatus ? 'activado' : 'desactivado';
            $this->log("Usuario {$statusText}", 'usuarios', "ID: {$id}");

            $this->json([
                'success' => true,
                'message' => "Usuario {$statusText}",
                'activo' => $newStatus
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Error al cambiar estado'], 500);
        }
    }
}
