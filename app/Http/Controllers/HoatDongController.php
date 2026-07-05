<?php
namespace App\Http\Controllers;

use App\Models\HoatDong;
use App\Models\DangKyHoatDong;
use App\Models\DiemDanh;
use App\Models\SinhVien;
use App\Models\TieuChiRenLuyen;
use App\Models\DonViToChuc;
use App\Services\DiemRenLuyenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller quản lý hoạt động rèn luyện.
 * Cho phép đăng ký tham gia, điểm danh bằng QR code và quản lý điểm danh.
 */
class HoatDongController extends Controller
{
    /**
     * Danh sách hoạt động rèn luyện.
     * Hỗ trợ tìm kiếm theo tên và lọc theo tiêu chí rèn luyện.
     */
    public function index(Request $request)
    {
        $query = HoatDong::with(["donViToChuc", "tieuChi"]);

        // Tìm kiếm theo từ khóa tên hoạt động
        if ($request->has("search") && !empty($request->search)) {
            $query->where("ten_hoat_dong", "like", "%" . $request->search . "%");
        }

        // Lọc theo tiêu chí rèn luyện
        if ($request->has("tieu_chi") && !empty($request->tieu_chi)) {
            $query->where("tieu_chi_id", $request->tieu_chi);
        }

        $hoatDongs = $query->latest()->paginate(10);
        $tieuChis = TieuChiRenLuyen::all();

        return view("hoat_dong.index", compact("hoatDongs", "tieuChis"));
    }

    /**
     * Chi tiết hoạt động rèn luyện.
     * Hiển thị trạng thái đăng ký và trạng thái điểm danh đối với sinh viên.
     */
    public function show($id)
    {
        $hoatDong = HoatDong::with(["donViToChuc", "tieuChi"])->findOrFail($id);
        $isRegistered = false;
        $diemDanhStatus = null;

        // Nếu là sinh viên/ban cán sự, kiểm tra xem đã đăng ký và điểm danh chưa
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

    /**
     * Đăng ký tham gia hoạt động rèn luyện (Dành cho Sinh viên).
     */
    public function register($id)
    {
        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) {
            return back()->with("warning", "Tài khoản của bạn chưa liên kết với hồ sơ sinh viên.");
        }

        $hoatDong = HoatDong::findOrFail($id);
        
        // Chỉ cho phép đăng ký hoạt động có trạng thái 'da_cong_bo'
        if ($hoatDong->trang_thai !== "da_cong_bo") {
            return back()->with("warning", "Hoạt động này không mở đăng ký.");
        }

        // Kiểm tra xem số lượng đăng ký đã đạt giới hạn tối đa chưa
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

    /**
     * Hủy đăng ký tham gia hoạt động (Dành cho Sinh viên).
     */
    public function cancel($id)
    {
        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) return back();

        DangKyHoatDong::where("sinh_vien_id", $sinhVien->id)
            ->where("hoat_dong_id", $id)
            ->delete();

        return back()->with("success", "Đã hủy đăng ký hoạt động.");
    }

    /**
     * Hiển thị màn hình tạo hoạt động mới (Dành cho Admin/CTSV).
     */
    public function create()
    {
        $tieuChis = TieuChiRenLuyen::all();
        $donVis = DonViToChuc::all();
        return view("hoat_dong.create", compact("tieuChis", "donVis"));
    }

    /**
     * Xử lý lưu hoạt động mới (Dành cho Admin/CTSV).
     */
    public function store(Request $request)
    {
        // Kiểm tra và xác thực dữ liệu đầu vào nghiêm ngặt
        $request->validate([
            "ma_hoat_dong" => "required|string|max:50|unique:hoat_dongs,ma_hoat_dong",
            "ten_hoat_dong" => "required|string|max:255",
            "tieu_chi_id" => "required|exists:tieu_chi_ren_luyens,id",
            "diem_du_kien" => "required|integer|min:0|max:100",
            "slot_toi_da" => "nullable|integer|min:1",
            "thoi_gian_bat_dau" => "required|date",
            "thoi_gian_ket_thuc" => "required|date|after_or_equal:thoi_gian_bat_dau",
        ], [
            "ma_hoat_dong.required" => "Mã hoạt động không được bỏ trống.",
            "ma_hoat_dong.unique" => "Mã hoạt động đã tồn tại trên hệ thống.",
            "ten_hoat_dong.required" => "Tên hoạt động không được bỏ trống.",
            "tieu_chi_id.required" => "Vui lòng chọn tiêu chí rèn luyện.",
            "tieu_chi_id.exists" => "Tiêu chí rèn luyện đã chọn không tồn tại.",
            "diem_du_kien.required" => "Điểm dự kiến không được bỏ trống.",
            "diem_du_kien.min" => "Điểm dự kiến tối thiểu là 0.",
            "diem_du_kien.max" => "Điểm dự kiến tối đa là 100.",
            "slot_toi_da.min" => "Số lượt đăng ký tối đa phải lớn hơn hoặc bằng 1.",
            "thoi_gian_bat_dau.required" => "Thời gian bắt đầu không được bỏ trống.",
            "thoi_gian_ket_thuc.required" => "Thời gian kết thúc không được bỏ trống.",
            "thoi_gian_ket_thuc.after_or_equal" => "Thời gian kết thúc phải diễn ra sau hoặc bằng thời gian bắt đầu.",
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

    /**
     * Hiển thị danh sách điểm danh sinh viên trong hoạt động.
     */
    public function attendanceList($id)
    {
        $hoatDong = HoatDong::findOrFail($id);
        $registrations = DangKyHoatDong::with(["sinhVien.lop", "diemDanh"])->where("hoat_dong_id", $id)->get();
        return view("hoat_dong.attendance", compact("hoatDong", "registrations"));
    }

    /**
     * Cập nhật trạng thái điểm danh thủ công (Dành cho Admin/CTSV).
     * Tự động cập nhật lại điểm rèn luyện của sinh viên sau khi điểm danh thay đổi.
     */
    public function updateAttendance(Request $request, $id)
    {
        $reg = DangKyHoatDong::findOrFail($id);
        $diemDanh = DiemDanh::firstOrNew(["dang_ky_hoat_dong_id" => $reg->id]);
        $diemDanh->trang_thai = $request->status;
        $diemDanh->check_in_time = $request->status === "co_mat" ? now() : null;
        $diemDanh->save();

        // Tính lại điểm rèn luyện sử dụng Service
        DiemRenLuyenService::recalculatePoints($reg->sinh_vien_id);

        return response()->json(["success" => true]);
    }

    /**
     * Sinh viên thực hiện điểm danh bằng việc quét mã QR.
     * Tự động đăng ký tham gia nếu chưa đăng ký trước đó.
     */
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

        // Kiểm tra xem thời gian quét QR có nằm trong khoảng thời gian diễn ra hoạt động không
        if ($now->lt($hoatDong->thoi_gian_bat_dau) || $now->gt($hoatDong->thoi_gian_ket_thuc)) {
            return redirect()->route('hoat_dong.show', $id)
                ->with('warning', 'Mã QR này chỉ khả dụng trong khoảng thời gian diễn ra hoạt động.');
        }

        // Tự động đăng ký nếu sinh viên chưa đăng ký tham gia hoạt động
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

        // Tính toán lại điểm rèn luyện của sinh viên
        DiemRenLuyenService::recalculatePoints($sinhVien->id);

        return redirect()->route('hoat_dong.show', $id)
            ->with('success', 'Bạn đã điểm danh thành công!');
    }

    /**
     * API kiểm tra trạng thái điểm danh hiện tại của sinh viên đối với hoạt động.
     */
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