<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private readonly ActivityLogService $logService) {}

    public function index(): View
    {
        $admins = Admin::latest()->paginate(15);
        return view('admin.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('admin.admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $admin = Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $this->logService->log('admin.create', "Membuat akun admin baru: {$admin->name} ({$admin->email})", $admin);

        return redirect()->route('admin.admins.index')
            ->with('success', "Akun admin '{$admin->name}' berhasil dibuat.");
    }

    public function edit(Admin $admin): View
    {
        return view('admin.admins.edit', compact('admin'));
    }

    public function update(Request $request, Admin $admin): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => "required|email|max:150|unique:admins,email,{$admin->id}",
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        $this->logService->log('admin.update', "Memperbarui akun admin: {$admin->name} ({$admin->email})", $admin);

        return redirect()->route('admin.admins.index')
            ->with('success', "Akun admin '{$admin->name}' berhasil diperbarui.");
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        if (Admin::count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya akun admin yang tersisa.');
        }

        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $admin->name;
        $admin->delete();

        $this->logService->log('admin.delete', "Menghapus akun admin: {$name}");

        return redirect()->route('admin.admins.index')
            ->with('success', "Akun admin '{$name}' berhasil dihapus.");
    }
}
