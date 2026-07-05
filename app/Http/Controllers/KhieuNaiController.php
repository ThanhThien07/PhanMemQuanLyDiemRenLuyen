<?php
namespace App\Http\Controllers;

use App\Models\KhieuNai;
use App\Models\SinhVien;
use App\Models\HoatDong;
use App\Models\MinhChung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KhieuNaiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if (!$currentSemester) {
            return redirect()->route("dashboard")->with("warning", "Hệ thống chưa có học kỳ nào hoạt động.");
        }

        if ($user->role === "sinh_vien" || $user->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", $user->id)->first();
            if (!$sinhVien) return redirect()->route("dashboard");

            $khieuNais = KhieuNai::with(["hoatDong", "minhChung"])
                ->where("sinh_vien_id", $sinhVien->id)
                ->latest()
                ->get();
            $hoatDongs = HoatDong::all();

            // Check if student has submitted self-evaluation
            $diemRenLuyen = \App\Models\DiemRenLuyen::where('sinh_vien_id', $sinhVien->id)
                ->where('hoc_ky_id', $currentSemester->id)
                ->first();
            $canSubmit = $diemRenLuyen && $diemRenLuyen->trang_thai_duyet !== 'tam_tinh';

            // BCS class reviews
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

        // CVHT or Admin
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
            // Admin sees all
            $khieuNais = KhieuNai::with(['hoatDong', 'sinhVien.lop', 'minhChung'])->latest()->get();
        }

        return view("khieu_nai.admin", compact("khieuNais"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "ly_do" => "required",
        ]);

        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) {
            return back()->with("warning", "Chưa có hồ sơ sinh viên.");
        }

        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if (!$currentSemester) {
            return back()->with("warning", "Chưa có học kỳ nào hoạt động.");
        }

        $diemRenLuyen = \App\Models\DiemRenLuyen::where('sinh_vien_id', $sinhVien->id)
            ->where('hoc_ky_id', $currentSemester->id)
            ->first();

        if (!$diemRenLuyen || $diemRenLuyen->trang_thai_duyet === 'tam_tinh') {
            return back()->with("warning", "Vui lòng hoàn thành tự đánh giá và nộp phiếu điểm trước khi gửi minh chứng bổ sung.");
        }

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
            "hoat_dong_id" => $request->hoat_dong_id ?? null,
            "ly_do" => $request->ly_do,
            "minh_chung_id" => $minhChungId,
            "trang_thai_xu_ly" => "cho_tiep_nhan"
        ]);

        return back()->with("success", "Đã gửi yêu cầu bổ sung minh chứng thành công.");
    }

    public function reply(Request $request, $id)
    {
        $kn = KhieuNai::findOrFail($id);
        $kn->phan_hoi = $request->phan_hoi;
        $kn->trang_thai_xu_ly = $request->trang_thai;
        $kn->nguoi_xu_ly_id = Auth::id();
        $kn->save();

        return response()->json(["success" => true]);
    }
}