<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Daftar user dengan pencarian.
     */
    public function index(Request $request): View
    {
        $kataKunci = $request->string('q')->trim()->value();

        $daftarUser = User::query()
            ->when($kataKunci !== '', fn ($query) => $query->where(function ($query) use ($kataKunci): void {
                $query->where('name', 'like', "%{$kataKunci}%")->orWhere('email', 'like', "%{$kataKunci}%");
            }))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', [
            'daftarUser' => $daftarUser,
            'kataKunci' => $kataKunci,
        ]);
    }

    /**
     * Form tambah user.
     */
    public function create(): View
    {
        return view('users.create', ['user' => new User(['role' => UserRole::Admin])]);
    }

    /**
     * Simpan user baru (password di-hash lewat cast model).
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        return to_route('users.index')->with('success', "Akun {$user->name} dibuat.");
    }

    /**
     * Form edit user.
     */
    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user]);
    }

    /**
     * Perbarui user. Password kosong = tidak diubah.
     * Super Admin tidak boleh menurunkan perannya sendiri agar tidak terkunci dari menu ini.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if ($user->is($request->user()) && $data['role'] !== UserRole::SuperAdmin->value) {
            return back()->withInput()->withErrors(['role' => 'Anda tidak dapat menurunkan peran akun Anda sendiri.']);
        }

        $user->update($data);

        return to_route('users.index')->with('success', "Perubahan akun {$user->name} disimpan.");
    }

    /**
     * Hapus user. Super Admin tidak boleh menghapus akunnya sendiri.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return to_route('users.index')->with('success', "Akun {$user->name} dihapus.");
    }
}
