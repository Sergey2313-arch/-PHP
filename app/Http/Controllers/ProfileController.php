<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

/** Управляет профилем текущего пользователя; чужой user_id в маршрутах не принимается. */
class ProfileController extends Controller
{
    /** Показывает данные пользователя, определённого по сессии. */
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /** Сохраняет имя, email и необязательный аватар после проверки UpdateProfileRequest. */
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $oldAvatar = $user->avatar;
        $newAvatar = null;
        try {
            // Явный список полей не позволяет передать role или password через эту форму.
            $user->fill($request->safe()->only(['name', 'email']));
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            // В БД хранится путь к изображению, а само изображение — на public-диске.
            if ($request->hasFile('avatar')) {
                $newAvatar = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $newAvatar;
            }
            $user->save();
        } catch (Throwable $exception) {
            // Если запись пользователя не сохранилась, убираем новый файл без записи в БД.
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }
            throw $exception;
        }
        // Старый файл удаляем только после успешного сохранения нового пути в базе.
        if ($newAvatar && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return back()->with('success', 'Профиль обновлён.');
    }

    /** Form Request проверяет текущий пароль и подтверждение; в базу записываем новый хеш. */
    public function password(UpdatePasswordRequest $request)
    {
        $request->user()->update(['password' => Hash::make($request->validated('password'))]);
        $request->session()->regenerate();

        return back()->with('success', 'Пароль изменён.');
    }
}
