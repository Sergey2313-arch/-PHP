<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** Вход, регистрация и выход через стандартную сессионную авторизацию Laravel. */
class AuthController extends Controller
{
    /** GET /login: возвращает только страницу с формой входа. */
    public function login()
    {
        return view('auth.login');
    }

    /** GET /register: форма регистрации; сохранение вынесено в отдельный POST-метод. */
    public function register()
    {
        return view('auth.register');
    }

    /** POST /register: валидируем поля, хешируем пароль, создаём пользователя и сразу авторизуем. */
    public function store(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        // Роль берётся из значения user по умолчанию в БД и не принимается из формы.
        $user = User::create($data);
        Auth::login($user);
        // Новый идентификатор сессии защищает от использования заранее известной сессии.
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /** POST /login: Auth::attempt сравнивает пароль с хешем и создаёт авторизованную сессию. */
    public function authenticate(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Неверный email или пароль.']);
        }
        $request->session()->regenerate();

        // Возвращаем на ранее запрошенную закрытую страницу или, если её не было, на Dashboard.
        return redirect()->intended(route('dashboard'));
    }

    /** POST /logout: завершаем авторизацию, удаляем данные сессии и обновляем CSRF-токен. */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
