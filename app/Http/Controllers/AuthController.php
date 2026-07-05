<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Hiển thị giao diện đăng nhập
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:100',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải từ 6 ký tự.',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $roleName = $this->getRoleNameVi($user->role);
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Chào mừng quay trở lại, ' . $user->name . ' (' . $roleName . ')!');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    /**
     * Hiển thị giao diện đăng ký
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký tài khoản mới
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,sinh_vien,ban_can_su,co_van',
        ], [
            'name.required' => 'Họ tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải từ 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò không hợp lệ.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        $roleModel = \App\Models\Role::where('name', $request->role)->first();
        if ($roleModel) {
            $user->roles()->attach($roleModel->id);
        }

        Auth::login($user);

        $roleName = $this->getRoleNameVi($user->role);
        return redirect()->route('dashboard')
            ->with('success', 'Đăng ký tài khoản thành công! Bạn đã đăng nhập với vai trò ' . $roleName . '.');
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công.');
    }

    /**
     * Helper dịch vai trò sang Tiếng Việt
     */
    private function getRoleNameVi($role)
    {
        switch ($role) {
            case 'admin':
                return 'Phòng Công tác sinh viên (CTSV)';
            case 'sinh_vien':
                return 'Sinh viên';
            case 'ban_can_su':
                return 'Ban cán sự lớp';
            case 'co_van':
                return 'Cố vấn học tập';
            default:
                return $role;
        }
    }
}

