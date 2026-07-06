<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Controller xử lý xác thực người dùng (Authentication).
 * Đăng nhập, đăng ký và đăng xuất cho các vai trò sinh viên, cán sự, cố vấn và admin.
 */
class AuthController extends Controller
{
    /**
     * Hiển thị giao diện đăng nhập.
     * Nếu người dùng đã đăng nhập trước đó, chuyển hướng về trang Dashboard.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Xử lý gửi yêu cầu đăng nhập.
     * Kiểm tra thông tin email, password và lưu thông tin đăng nhập.
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
     * Hiển thị giao diện đăng ký tài khoản mới.
     */
    public function showRegister()
    {
        $lops = \App\Models\Lop::orderBy('ten_lop', 'asc')->get();
        $heDaoTaos = \App\Models\HeDaoTao::all();
        return view('auth.register', compact('lops', 'heDaoTaos'));
    }

    /**
     * Xử lý đăng ký tài khoản người dùng mới.
     * Tự động liên kết vai trò của người dùng với hệ thống phân quyền của Laravel.
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:ctsv,sinh_vien,ban_can_su,co_van',
        ];

        if ($request->role === 'sinh_vien' || $request->role === 'ban_can_su') {
            $rules['ma_sv'] = 'required|string|max:50|unique:sinh_viens,ma_sv';
            $rules['lop_id'] = 'required|exists:lops,id';
            $rules['he_dao_tao_id'] = 'required|exists:he_dao_taos,id';
        }

        $request->validate($rules, [
            'name.required' => 'Họ tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải từ 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò không hợp lệ.',
            'ma_sv.required' => 'Mã sinh viên không được để trống.',
            'ma_sv.unique' => 'Mã sinh viên này đã tồn tại trong hệ thống.',
            'lop_id.required' => 'Vui lòng chọn lớp học.',
            'lop_id.exists' => 'Lớp học không hợp lệ.',
            'he_dao_tao_id.required' => 'Vui lòng chọn hệ đào tạo.',
            'he_dao_tao_id.exists' => 'Hệ đào tạo không hợp lệ.',
        ]);

        $user = \Illuminate\Support\Facades\DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Gán vai trò thông qua quan hệ nhiều-nhiều
            $roleModel = \App\Models\Role::where('name', $request->role)->first();
            if ($roleModel) {
                $user->roles()->attach($roleModel->id);
            }

            if ($request->role === 'sinh_vien' || $request->role === 'ban_can_su') {
                \App\Models\SinhVien::create([
                    'user_id' => $user->id,
                    'ma_sv' => $request->ma_sv,
                    'ho_ten' => $request->name,
                    'lop_id' => $request->lop_id,
                    'he_dao_tao_id' => $request->he_dao_tao_id,
                ]);
            }

            return $user;
        });

        // Tự động đăng nhập sau khi đăng ký thành công
        Auth::login($user);

        $roleName = $this->getRoleNameVi($user->role);
        return redirect()->route('dashboard')
            ->with('success', 'Đăng ký tài khoản thành công! Bạn đã đăng nhập với vai trò ' . $roleName . '.');
    }

    /**
     * Đăng xuất tài khoản người dùng hiện tại.
     * Hủy phiên (session) hoạt động và làm mới mã token bảo mật.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công.');
    }

    /**
     * Chuyển đổi tên vai trò từ cơ sở dữ liệu sang tên Tiếng Việt thân thiện với người dùng.
     */
    private function getRoleNameVi($role)
    {
        switch ($role) {
            case 'ctsv':
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

