<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetUserPasswordRequest;
use App\Models\User;
use App\Services\Users\UserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserPasswordController extends Controller
{
    public function __construct(private UserManagementService $svc) {}

    public function edit(User $user): View
    {
        $this->authorize('resetPassword', $user);

        return view('admin.users.reset-password', compact('user'));
    }

    public function update(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->svc->resetPassword($user, $request->validated()['password'], Auth::user());

        return redirect()->route('admin.users.show', $user)->with('success', 'Senha redefinida com sucesso.');
    }
}
