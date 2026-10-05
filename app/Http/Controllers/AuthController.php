<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if ($usuario = Auth::user()) {
            return $this->redirigir($usuario);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $password = $credentials['password'];
        $usuario = null;

        // El sistema reconoce el rol automáticamente: solo se ingresa la contraseña.
        $admin = Usuario::where('username', 'admin')->first();
        if ($admin && Hash::check($password, $admin->password)) {
            $usuario = $admin;
        } else {
            // El alumno usa su correo institucional como contraseña.
            $usuario = Usuario::where('username', mb_strtolower($password))
                ->whereNotNull('alumno_id')
                ->where('estado', 'ACTIVO')
                ->first();
        }

        if (!$usuario || $usuario->estado !== 'ACTIVO') {
            return back()
                ->withErrors(['password' => 'La contraseña no corresponde a ningún usuario registrado.'])
                ->onlyInput('password');
        }

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();

        $usuario->ultimo_login = now();
        $usuario->save();

        return $this->redirigir($usuario);
    }

    private function redirigir(Usuario $usuario)
    {
        if ($usuario->esAdmin()) {
            return redirect()->route('dashboard');
        }

        if ($usuario->alumno_id) {
            return redirect()->route('alumnos.carnet', $usuario->alumno_id);
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}