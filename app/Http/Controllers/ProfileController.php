<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $oldAvatar = $user->avatar;
        $newAvatar = null;
        try {
            $user->fill($request->safe()->only(['name', 'email']));
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            if ($request->hasFile('avatar')) {
                $newAvatar = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $newAvatar;
            }
            $user->save();
        } catch (Throwable $exception) {
            if ($newAvatar) {
                Storage::disk('public')->delete($newAvatar);
            }
            throw $exception;
        }
        // Delete the previous file only after the new path has been saved.
        if ($newAvatar && $oldAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return back()->with('success', 'Профиль обновлён.');
    }

    public function password(UpdatePasswordRequest $request)
    {
        $request->user()->update(['password' => Hash::make($request->validated('password'))]);
        $request->session()->regenerate();

        return back()->with('success', 'Пароль изменён.');
    }
}
