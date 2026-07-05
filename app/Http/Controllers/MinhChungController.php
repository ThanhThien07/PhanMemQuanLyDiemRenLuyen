<?php
namespace App\Http\Controllers;

use App\Models\MinhChung;
use App\Models\HoSoMinhChung;
use App\Models\SinhVien;
use App\Models\HoatDong;
use App\Models\TieuChiRenLuyen;
use App\Services\DiemRenLuyenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller quản lý Hồ sơ Minh chứng của sinh viên.
 * Cho phép sinh viên nộp minh chứng và Cố vấn/Admin phê duyệt.
 */
class MinhChungController extends Controller
{
    /**
     * Hiển thị danh sách hồ sơ minh chứng.
     * - Sinh viên/Ban cán sự: Xem các minh chứng đã nộp của cá nhân.
     * - Cố vấn học tập: Xem các minh chứng chờ duyệt của lớp được phân công.
     * - Admin/CTSV: Xem toàn bộ các minh chứng chờ duyệt.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Phân quyền cho Sinh viên và Ban cán sự lớp
        if ($user->role === "sinh_vien" || $user->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", $user->id)->first();
            if (!$sinhVien) {
                return redirect()->route("dashboard")->with("warning", "Hồ sơ sinh viên chưa được khởi tạo.");
            }
            
            // Lấy toàn bộ minh chứng của sinh viên này kèm quan hệ
            $hoSoMinhChungs = HoSoMinhChung::with(["minhChung", "hoatDong", "tieuChi"])
                ->where("sinh_vien_id", $sinhVien->id)
                ->latest()
                ->get();
                
            $hoatDongs = HoatDong::all();
            $tieuChis = TieuChiRenLuyen::all();

            return view("minh_chung.index", compact("hoSoMinhChungs", "hoatDongs", "tieuChis"));
        }

        // 2. Phân quyền cho Cố vấn học tập (CVHT) và CTSV (Admin)
        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if ($user->role === 'co_van') {
            if ($currentSemester) {
                // Chỉ lấy danh sách lớp được phân công trong học kỳ hiện tại
                $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                    ->where('hoc_ky_id', $currentSemester->id)
                    ->pluck('lop_id');
                
                $hoSoMinhChungs = HoSoMinhChung::with(["minhChung", "hoatDong", "sinhVien.lop", "tieuChi"])
                    ->where("trang_thai_duyet", "cho_duyet")
                    ->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                        $q->whereIn('lop_id', $assignedLopIds);
                    })
                    ->latest()
                    ->get();
            } else {
                $hoSoMinhChungs = collect();
            }
        } else {
            // Admin thấy toàn bộ minh chứng chờ duyệt của hệ thống
            $hoSoMinhChungs = HoSoMinhChung::with(["minhChung", "hoatDong", "sinhVien.lop", "tieuChi"])
                ->where("trang_thai_duyet", "cho_duyet")
                ->latest()
                ->get();
        }

        return view("minh_chung.approve", compact("hoSoMinhChungs"));
    }

    /**
     * Xử lý nộp hồ sơ minh chứng mới (Dành cho Sinh viên).
     * Sử dụng Transaction để đảm bảo tính toàn vẹn dữ liệu khi ghi vào 2 bảng.
     */
    public function store(Request $request)
    {
        // Kiểm tra dữ liệu đầu vào
        $request->validate([
            "file_minh_chung" => "required|file|mimes:jpg,jpeg,png,pdf|max:5120",
            "diem_de_xuat" => "required|integer|min:0|max:100",
            "tieu_chi_id" => "nullable|exists:tieu_chi_ren_luyens,id",
            "hoat_dong_id" => "nullable|exists:hoat_dongs,id",
            "ten_hoat_dong_ngoai" => "nullable|string|max:255",
        ], [
            "file_minh_chung.required" => "Vui lòng chọn file minh chứng.",
            "file_minh_chung.mimes" => "Định dạng file không hỗ trợ (chỉ nhận JPG, JPEG, PNG, PDF).",
            "file_minh_chung.max" => "Kích thước file tối đa là 5MB.",
            "diem_de_xuat.required" => "Vui lòng nhập điểm đề xuất.",
            "diem_de_xuat.integer" => "Điểm đề xuất phải là số nguyên.",
            "diem_de_xuat.min" => "Điểm đề xuất tối thiểu là 0.",
            "diem_de_xuat.max" => "Điểm đề xuất tối đa là 100.",
            "tieu_chi_id.exists" => "Tiêu chí rèn luyện không hợp lệ.",
            "hoat_dong_id.exists" => "Hoạt động không hợp lệ.",
        ]);

        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) {
            return back()->with("warning", "Tài khoản của bạn chưa liên kết với hồ sơ sinh viên.");
        }

        // Xử lý upload file
        $file = $request->file("file_minh_chung");
        $fileName = time() . "_" . $file->getClientOriginalName();
        $file->move(public_path("uploads/minh_chung"), $fileName);
        $filePath = "uploads/minh_chung/" . $fileName;

        // Lưu thông tin vào DB sử dụng Transaction
        DB::transaction(function () use ($request, $sinhVien, $file, $filePath) {
            $minhChung = MinhChung::create([
                "file_name" => $file->getClientOriginalName(),
                "file_path" => $filePath,
                "file_size" => $file->getSize(),
                "file_type" => $file->getClientOriginalExtension(),
            ]);

            HoSoMinhChung::create([
                "sinh_vien_id" => $sinhVien->id,
                "hoat_dong_id" => $request->hoat_dong_id ?: null,
                "ten_hoat_dong_ngoai" => $request->ten_hoat_dong_ngoai ?: null,
                "minh_chung_id" => $minhChung->id,
                "tieu_chi_id" => $request->tieu_chi_id ?: null,
                "diem_de_xuat" => $request->diem_de_xuat,
                "trang_thai_duyet" => "cho_duyet"
            ]);
        });

        return back()->with("success", "Đã nộp hồ sơ minh chứng thành công! Đang chờ duyệt.");
    }

    /**
     * Cập nhật trạng thái duyệt minh chứng (Dành cho Cố vấn/Admin).
     * Tự động tính toán lại điểm rèn luyện nếu minh chứng được duyệt thành công.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $hoSo = HoSoMinhChung::findOrFail($id);

        // Kiểm tra quyền hạn của Cố vấn học tập đối với sinh viên lớp khác
        if ($user->role === 'co_van') {
            $student = SinhVien::findOrFail($hoSo->sinh_vien_id);
            $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
            if ($currentSemester) {
                $isAssigned = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                    ->where('lop_id', $student->lop_id)
                    ->where('hoc_ky_id', $currentSemester->id)
                    ->exists();
                if (!$isAssigned) {
                    return response()->json(["success" => false, "message" => "Bạn không có quyền duyệt minh chứng của sinh viên lớp khác."], 403);
                }
            }
        }

        // Cập nhật trạng thái minh chứng
        $hoSo->trang_thai_duyet = $request->trang_thai;
        $hoSo->ly_do_tu_choi = $request->ly_do;
        $hoSo->nguoi_duyet_id = Auth::id();
        $hoSo->save();

        // Nếu minh chứng được phê duyệt, tự động chạy hàm tính lại điểm
        if ($request->trang_thai === "da_duyet") {
            DiemRenLuyenService::recalculatePoints($hoSo->sinh_vien_id);
        }

        return response()->json(["success" => true]);
    }
}