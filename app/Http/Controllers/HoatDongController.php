<?php
namespace App\Http\Controllers;

use App\Models\HoatDong;
use App\Models\DangKyHoatDong;
use App\Models\DiemDanh;
use App\Models\SinhVien;
use App\Models\TieuChiRenLuyen;
use App\Models\DonViToChuc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HoatDongController extends Controller
{
    public function index(Request $request)
    {
        $query = HoatDong::with(["donViToChuc", "tieuChi"]);

        if ($request->has("search") && !empty($request->search)) {
            $query->where("ten_hoat_dong", "like", "%" . $request->search . "%");
        }

        if ($request->has("tieu_chi") && !empty($request->tieu_chi)) {
            $query->where("tieu_chi_id", $request->tieu_chi);
        }

        $hoatDongs = $query->latest()->paginate(10);
        $tieuChis = TieuChiRenLuyen::all();

        return view("hoat_dong.index", compact("hoatDongs", "tieuChis"));
    }

    public function show($id)
    {
        $hoatDong = HoatDong::with(["donViToChuc", "tieuChi"])->findOrFail($id);
        $isRegistered = false;
        $diemDanhStatus = null;

        if (Auth::user()->role === "sinh_vien" || Auth::user()->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", Auth::id())->first();
            if ($sinhVien) {
                $registration = DangKyHoatDong::where("sinh_vien_id", $sinhVien->id)
                    ->where("hoat_dong_id", $id)
                    ->first();
                if ($registration) {
                    $isRegistered = true;
                    $diemDanh = DiemDanh::where("dang_ky_hoat_dong_id", $registration->id)->first();
                    $diemDanhStatus = $diemDanh ? $diemDanh->trang_thai : "Chưa điểm danh";
                }
            }
        }

        return view("hoat_dong.show", compact("hoatDong", "isRegistered", "diemDanhStatus"));
    }

    public function register($id)
    {
        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) {
            return back()->with("warning", "Tài khoản của bạn chưa liên kết với hồ sơ sinh viên.");
        }

        $hoatDong = HoatDong::findOrFail($id);
        if ($hoatDong->trang_thai !== "da_cong_bo") {
            return back()->with("warning", "Hoạt động này không mở đăng ký.");
        }

        $registeredCount = DangKyHoatDong::where("hoat_dong_id", $id)->count();
        if ($registeredCount >= $hoatDong->slot_toi_da) {
            return back()->with("warning", "Hoạt động đã hết lượt đăng ký.");
        }

        DangKyHoatDong::firstOrCreate([
            "sinh_vien_id" => $sinhVien->id,
            "hoat_dong_id" => $id,
        ], [
            "trang_thai_dang_ky" => "da_dang_ky"
        ]);

        return back()->with("success", "Đăng ký tham gia hoạt động thành công!");
    }

    public function cancel($id)
    {
        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) return back();

        DangKyHoatDong::where("sinh_vien_id", $sinhVien->id)
            ->where("hoat_dong_id", $id)
            ->delete();

        return back()->with("success", "Đã hủy đăng ký hoạt động.");
    }

    public function create()
    {
        $tieuChis = TieuChiRenLuyen::all();
        $donVis = DonViToChuc::all();
        return view("hoat_dong.create", compact("tieuChis", "donVis"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "ma_hoat_dong" => "required|unique:hoat_dongs",
            "ten_hoat_dong" => "required",
            "tieu_chi_id" => "required",
            "diem_du_kien" => "required|integer",
            "thoi_gian_bat_dau" => "required",
            "thoi_gian_ket_thuc" => "required",
        ]);

        $donVi = DonViToChuc::where("user_id", Auth::id())->first();
        if (!$donVi) {
            $firstDonVi = DonViToChuc::first();
            if (!$firstDonVi) {
                $firstDonVi = DonViToChuc::create([
                    'ma_don_vi' => 'CTSV',
                    'ten_don_vi' => 'Phòng Công tác sinh viên (CTSV)',
                    'user_id' => Auth::id()
                ]);
            }
            $donViId = $firstDonVi->id;
        } else {
            $donViId = $donVi->id;
        }

        HoatDong::create([
            "ma_hoat_dong" => $request->ma_hoat_dong,
            "ten_hoat_dong" => $request->ten_hoat_dong,
            "don_vi_to_chuc_id" => $donViId,
            "tieu_chi_id" => $request->tieu_chi_id,
            "diem_du_kien" => $request->diem_du_kien,
            "slot_toi_da" => $request->slot_toi_da ?? 100,
            "thoi_gian_bat_dau" => $request->thoi_gian_bat_dau,
            "thoi_gian_ket_thuc" => $request->thoi_gian_ket_thuc,
            "yeu_cau_minh_chung" => $request->has("yeu_cau_minh_chung") ? 1 : 0,
            "trang_thai" => "da_cong_bo"
        ]);

        return redirect()->route("hoat_dong.index")->with("success", "Đã thêm hoạt động mới!");
    }

    public function attendanceList($id)
    {
        $hoatDong = HoatDong::findOrFail($id);
        $registrations = DangKyHoatDong::with(["sinhVien.lop", "diemDanh"])->where("hoat_dong_id", $id)->get();
        return view("hoat_dong.attendance", compact("hoatDong", "registrations"));
    }

    public function updateAttendance(Request $request, $id)
    {
        $reg = DangKyHoatDong::findOrFail($id);
        $diemDanh = DiemDanh::firstOrNew(["dang_ky_hoat_dong_id" => $reg->id]);
        $diemDanh->trang_thai = $request->status;
        $diemDanh->check_in_time = $request->status === "co_mat" ? now() : null;
        $diemDanh->save();

        // Recalculate training points
        $mcController = new MinhChungController();
        $mcController->recalculatePoints($reg->sinh_vien_id);

        return response()->json(["success" => true]);
    }

    public function diemDanhQr($id)
    {
        $user = Auth::user();
        if ($user->role !== 'sinh_vien' && $user->role !== 'ban_can_su') {
            return redirect()->route('hoat_dong.show', $id)
                ->with('warning', 'Chỉ sinh viên mới có thể thực hiện quét mã điểm danh.');
        }

        $sinhVien = SinhVien::where('user_id', $user->id)->first();
        if (!$sinhVien) {
            return redirect()->route('hoat_dong.show', $id)
                ->with('warning', 'Không tìm thấy hồ sơ sinh viên của bạn.');
        }

        $hoatDong = HoatDong::findOrFail($id);
        $now = now();

        // Check if the activity is currently ongoing
        if ($now->lt($hoatDong->thoi_gian_bat_dau) || $now->gt($hoatDong->thoi_gian_ket_thuc)) {
            return redirect()->route('hoat_dong.show', $id)
                ->with('warning', 'Mã QR này chỉ khả dụng trong khoảng thời gian diễn ra hoạt động.');
        }

        // Register automatically if not registered
        $reg = DangKyHoatDong::firstOrCreate([
            'sinh_vien_id' => $sinhVien->id,
            'hoat_dong_id' => $id,
        ], [
            'trang_thai_dang_ky' => 'da_dang_ky'
        ]);

        $diemDanh = DiemDanh::firstOrNew(['dang_ky_hoat_dong_id' => $reg->id]);
        $diemDanh->trang_thai = 'co_mat';
        $diemDanh->check_in_time = $now;
        $diemDanh->save();

        // Recalculate training points
        $mcController = new MinhChungController();
        $mcController->recalculatePoints($sinhVien->id);

        return redirect()->route('hoat_dong.show', $id)
            ->with('success', 'Bạn đã điểm danh thành công!');
    }

    public function checkAttendance($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'status' => 'guest']);
        }

        $sinhVien = SinhVien::where('user_id', $user->id)->first();
        if (!$sinhVien) {
            return response()->json(['success' => false, 'status' => 'no_profile']);
        }

        $reg = DangKyHoatDong::where('sinh_vien_id', $sinhVien->id)
            ->where('hoat_dong_id', $id)
            ->first();

        if (!$reg) {
            return response()->json(['success' => true, 'status' => 'chua_dang_ky']);
        }

        $diemDanh = DiemDanh::where('dang_ky_hoat_dong_id', $reg->id)->first();
        $status = $diemDanh ? $diemDanh->trang_thai : 'vang_mat';

        return response()->json(['success' => true, 'status' => $status]);
    }
}