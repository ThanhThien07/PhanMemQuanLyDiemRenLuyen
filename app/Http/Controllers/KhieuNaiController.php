<?php
namespace App\Http\Controllers;

use App\Models\KhieuNai;
use App\Models\SinhVien;
use App\Models\HoatDong;
use App\Models\MinhChung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller quản lý Khiếu nại và phúc khảo điểm rèn luyện.
 * Sinh viên gửi khiếu nại (bổ sung minh chứng), Cán sự lớp/Cố vấn/Admin tiếp nhận và xử lý.
 */
class KhieuNaiController extends Controller
{
    /**
     * Hiển thị danh sách khiếu nại.
     * - Sinh viên/Ban cán sự: Xem các khiếu nại cá nhân đã nộp.
     * - Ban cán sự (đối với lớp của mình): Xem các khiếu nại của lớp mình đang rà soát.
     * - Cố vấn học tập: Xem khiếu nại của sinh viên thuộc lớp được phân công.
     * - Admin: Xem toàn bộ các khiếu nại trên hệ thống.
     */
    public function index()
    {
        $user = Auth::user();
        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if (!$currentSemester) {
            return redirect()->route("dashboard")->with("warning", "Hệ thống chưa có học kỳ nào hoạt động.");
        }

        // 1. Phân quyền cho Sinh viên và Ban cán sự lớp
        if ($user->role === "sinh_vien" || $user->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", $user->id)->first();
            if (!$sinhVien) return redirect()->route("dashboard");

            $khieuNais = KhieuNai::with(["hoatDong", "minhChung"])
                ->where("sinh_vien_id", $sinhVien->id)
                ->latest()
                ->get();
            $hoatDongs = HoatDong::all();

            // Kiểm tra xem sinh viên đã nộp phiếu tự đánh giá rèn luyện chưa
            $diemRenLuyen = \App\Models\DiemRenLuyen::where('sinh_vien_id', $sinhVien->id)
                ->where('hoc_ky_id', $currentSemester->id)
                ->first();
            $canSubmit = $diemRenLuyen && $diemRenLuyen->trang_thai_duyet !== 'tam_tinh';

            // Đối với Ban cán sự lớp: hiển thị thêm các khiếu nại cần duyệt của lớp
            $classReviews = collect();
            if ($user->role === 'ban_can_su') {
                $classReviews = KhieuNai::whereHas('sinhVien', function($q) use ($sinhVien) {
                    $q->where('lop_id', $sinhVien->lop_id)
                      ->where('id', '!=', $sinhVien->id);
                })->whereHas('sinhVien.diemRenLuyens', function($q) use ($currentSemester) {
                    $q->where('hoc_ky_id', $currentSemester->id)
                      ->where('trang_thai_duyet', 'cho_bcs_duyet');
                })->with(['sinhVien.lop', 'hoatDong', 'minhChung'])
                  ->latest()
                  ->get();
            }

            return view("khieu_nai.index", compact("khieuNais", "hoatDongs", "canSubmit", "classReviews"));
        }

        // 2. Phân quyền cho Cố vấn học tập (CVHT) và CTSV (Admin)
        $classReviews = collect();
        if ($user->role === 'co_van') {
            $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('hoc_ky_id', $currentSemester->id)
                ->pluck('lop_id');

            $khieuNais = KhieuNai::whereHas('sinhVien', function($q) use ($assignedLopIds) {
                $q->whereIn('lop_id', $assignedLopIds);
            })->whereHas('sinhVien.diemRenLuyens', function($q) use ($currentSemester) {
                $q->where('hoc_ky_id', $currentSemester->id)
                  ->where('trang_thai_duyet', 'cho_cvht_duyet');
            })->with(['hoatDong', 'sinhVien.lop', 'minhChung'])
              ->latest()
              ->get();
        } else {
            // Admin thấy toàn bộ khiếu nại
            $khieuNais = KhieuNai::with(['hoatDong', 'sinhVien.lop', 'minhChung'])->latest()->get();
        }

        return view("khieu_nai.admin", compact("khieuNais"));
    }

    /**
     * Sinh viên gửi khiếu nại (bổ sung hồ sơ minh chứng ngoài/trong trường).
     * Sử dụng Transaction để upload và ghi dữ liệu đồng thời vào 2 bảng.
     */
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào nghiêm ngặt
        $request->validate([
            "ly_do" => "required|string|max:1000",
            "hoat_dong_id" => "nullable|exists:hoat_dongs,id",
            "file_khieu_nai" => "nullable|file|mimes:jpg,jpeg,png,pdf|max:5120",
        ], [
            "ly_do.required" => "Lý do khiếu nại không được để trống.",
            "ly_do.max" => "Lý do khiếu nại quá dài (tối đa 1000 ký tự).",
            "hoat_dong_id.exists" => "Hoạt động rèn luyện không hợp lệ.",
            "file_khieu_nai.mimes" => "Định dạng file không hỗ trợ (chỉ nhận JPG, JPEG, PNG, PDF).",
            "file_khieu_nai.max" => "Kích thước file tối đa là 5MB.",
        ]);

        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) {
            return back()->with("warning", "Chưa có hồ sơ sinh viên.");
        }

        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if (!$currentSemester) {
            return back()->with("warning", "Chưa có học kỳ nào hoạt động.");
        }

        // Bắt buộc phải có phiếu điểm rèn luyện và trạng thái khác 'tam_tinh' mới được gửi khiếu nại
        $diemRenLuyen = \App\Models\DiemRenLuyen::where('sinh_vien_id', $sinhVien->id)
            ->where('hoc_ky_id', $currentSemester->id)
            ->first();

        if (!$diemRenLuyen || $diemRenLuyen->trang_thai_duyet === 'tam_tinh') {
            return back()->with("warning", "Vui lòng hoàn thành tự đánh giá và nộp phiếu điểm trước khi gửi minh chứng bổ sung.");
        }

        // Sử dụng Database Transaction để lưu thông tin
        DB::transaction(function () use ($request, $sinhVien) {
            $minhChungId = null;
            if ($request->hasFile("file_khieu_nai")) {
                $file = $request->file("file_khieu_nai");
                $fileName = time() . "_" . $file->getClientOriginalName();
                $file->move(public_path("uploads/khieu_nai"), $fileName);
                $filePath = "uploads/khieu_nai/" . $fileName;

                $mc = MinhChung::create([
                    "file_name" => $file->getClientOriginalName(),
                    "file_path" => $filePath,
                    "file_size" => $file->getSize(),
                    "file_type" => $file->getClientOriginalExtension(),
                ]);
                $minhChungId = $mc->id;
            }

            KhieuNai::create([
                "sinh_vien_id" => $sinhVien->id,
                "hoat_dong_id" => $request->hoat_dong_id ?: null,
                "ly_do" => $request->ly_do,
                "minh_chung_id" => $minhChungId,
                "trang_thai_xu_ly" => "cho_tiep_nhan"
            ]);
        });

        return back()->with("success", "Đã gửi yêu cầu bổ sung minh chứng thành công.");
    }

    /**
     * Phản hồi khiếu nại của sinh viên (Dành cho cán bộ/Cố vấn/Admin).
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'phan_hoi' => 'nullable|string|max:1000',
            'trang_thai' => 'required|in:cho_tiep_nhan,dang_xu_ly,da_giai_quyet,tu_choi',
        ]);

        $kn = KhieuNai::findOrFail($id);
        $kn->phan_hoi = $request->phan_hoi;
        $kn->trang_thai_xu_ly = $request->trang_thai;
        $kn->nguoi_xu_ly_id = Auth::id();
        $kn->save();

        return response()->json(["success" => true]);
    }
}