<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Obtiene el usuario logueado
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoginUser()
    {
        $user = auth()->user();

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Obtiene la lista de usuarios con filtros y paginación
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'nullable|integer',
            'sort' => 'nullable|string|in:id,name,email,phone,role',
            'order' => 'nullable|string|in:asc,desc',
            'search' => 'nullable|string',
            'page' => 'nullable|integer',
        ]);
        $user = auth()->user();
        $users = User::select('rol', 'name', 'last_name', 'email', 'phone', 'id', 'created_at', 'updated_at')
            ->where('id', '!=', $user->id)
            ->when($request->search, function ($query) use ($request) {
                return $query->where(function ($query) use ($request) {
                    return $query->whereRaw('name like ?', '%' . $request->search . '%')
                        ->orWhereRaw('last_name like ?', '%' . $request->search . '%')
                        ->orWhereRaw('email like ?', '%' . $request->search . '%')
                        ->orWhereRaw('phone like ?', '%' . $request->search . '%');
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                return $query->orderBy($request->sort, $request->order ?? 'asc');
            })
            ->paginate($request->per_page ?? 10, ['*'], 'page', $request->page ?? 1);

        if (!$users->isEmpty()) {
            $users->getCollection()->transform(function ($value) {
                $value->originalRol = $value->rol;
                $value->rol = $value->rol == 'admin' ? 'Administrador' : 'Usuario';
                $value->creado = Carbon::parse($value->created_at)->diffForHumans();
                $value->actualizado = Carbon::parse($value->updated_at)->diffForHumans();
                $value->edit = false;
                return $value;
            });
        }

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Obtiene un usuario por id
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $request = request();
        $request->merge(['id' => $id]);

        $request->validate([
            'id' => 'required|integer',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Registra un usuario
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::create($request->all());

        if (!$user) {
            return response()->json([
                'message' => 'Se produjo un error al registrar el usuario.',
            ], 500);
        }

        return response()->json([
            'user' => $user,
        ], 201);
    }

    /**
     * Actualiza un usuario
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->validate([
            'name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string',
            'password' => 'required|string',
            'new_password' => 'nullable|string'
        ]);

        $user = User::find($user->id);

        // Check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            // Log::debug('Password incorrect, user: ' . $user->id);
            return response()->json([
                'message' => 'Contraseña incorrecta.',
            ], 422);
        }


        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        if ($request->has('new_password') && $request->new_password != '') {
            $user->password = Hash::make($request->new_password);
        }

        if (!$user->save()) {
            // Log::debug('User not updated, user: ' . $user->id);
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el usuario.',
            ], 500);
        }

        // Log::debug('User updated, user: ' . $user->id);

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Actualiza el rol de un usuario
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'rol' => 'required|string|in:admin,user',
        ]);

        $user = User::find($id);

        if (!$user) {
            // Log::debug('User not found, user: ' . $id);
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $user->update($request->only('rol'));

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Elimina suavemente un usuario
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            // Log::debug('User not found, user: ' . $id);
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        // Log::debug('User deleted, user: ' . $id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted',
        ]);
    }

    /**
     * Restaura un usuario eliminado
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $user->restore();

        return response()->json([
            'message' => 'User restored',
        ]);
    }
}
