<?php
namespace App\Http\Controllers;

use App\Models\MinhChung;
use App\Models\HoSoMinhChung;
use App\Models\SinhVien;
use App\Models\HoatDong;
use App\Models\TieuChiRenLuyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MinhChungController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === "sinh_vien" || $user->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", $user->id)->first();
            if (!$sinhVien) {
                return redirect()->route("dashboard")->with("warning", "Hồ sơ sinh viên chưa được khởi tạo.");
            }
            $hoSoMinhChungs = HoSoMinhChung::with(["minhChung", "hoatDong", "tieuChi"])
                ->where("sinh_vien_id", $sinhVien->id)
                ->latest()
                ->get();
            $hoatDongs = HoatDong::all();
            $tieuChis = TieuChiRenLuyen::all();

            return view("minh_chung.index", compact("hoSoMinhChungs", "hoatDongs", "tieuChis"));
        }

        // Advisors or CTSV approve proofs
        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if ($user->role === 'co_van') {
            if ($currentSemester) {
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
            $hoSoMinhChungs = HoSoMinhChung::with(["minhChung", "hoatDong", "sinhVien.lop", "tieuChi"])
                ->where("trang_thai_duyet", "cho_duyet")
                ->latest()
                ->get();
        }

        return view("minh_chung.approve", compact("hoSoMinhChungs"));
    }

    public function store(Request $request)
    {
        $request->validate([
            "file_minh_chung" => "required|file|mimes:jpg,jpeg,png,pdf|max:5120",
            "diem_de_xuat" => "required|integer",
        ]);

        $sinhVien = SinhVien::where("user_id", Auth::id())->first();
        if (!$sinhVien) {
            return back()->with("warning", "Tài khoản của bạn chưa liên kết với hồ sơ sinh viên.");
        }

        $file = $request->file("file_minh_chung");
        $fileName = time() . "_" . $file->getClientOriginalName();
        // Save to public directory for easy previewing
        $file->move(public_path("uploads/minh_chung"), $fileName);
        $filePath = "uploads/minh_chung/" . $fileName;

        $minhChung = MinhChung::create([
            "file_name" => $file->getClientOriginalName(),
            "file_path" => $filePath,
            "file_size" => $file->getSize(),
            "file_type" => $file->getClientOriginalExtension(),
        ]);

        HoSoMinhChung::create([
            "sinh_vien_id" => $sinhVien->id,
            "hoat_dong_id" => $request->hoat_dong_id ?? null,
            "ten_hoat_dong_ngoai" => $request->ten_hoat_dong_ngoai ?? null,
            "minh_chung_id" => $minhChung->id,
            "tieu_chi_id" => $request->tieu_chi_id ?? null,
            "diem_de_xuat" => $request->diem_de_xuat,
            "trang_thai_duyet" => "cho_duyet"
        ]);

        return back()->with("success", "Đã nộp hồ sơ minh chứng thành công! Đang chờ duyệt.");
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $hoSo = HoSoMinhChung::findOrFail($id);

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

        $hoSo->trang_thai_duyet = $request->trang_thai;
        $hoSo->ly_do_tu_choi = $request->ly_do;
        $hoSo->nguoi_duyet_id = Auth::id();
        $hoSo->save();

        // If approved, update DiemRenLuyen
        if ($request->trang_thai === "da_duyet") {
            $this->recalculatePoints($hoSo->sinh_vien_id);
        }

        return response()->json(["success" => true]);
    }

    public function recalculatePoints($sinhVienId, $semesterId = null)
    {
        $sinhVien = SinhVien::findOrFail($sinhVienId);
        
        $latestSemester = null;
        if ($semesterId) {
            $latestSemester = \App\Models\HocKy::find($semesterId);
        }
        if (!$latestSemester) {
            $latestSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        }
        
        if (!$latestSemester) return;

        $tiLeRenLuyen = floatval($latestSemester->ti_le_ren_luyen ?? 80.00);
        $tiLeHocTap = floatval($latestSemester->ti_le_hoc_tap ?? 20.00);

        // Points from activities attended
        $dangKyIds = $sinhVien->dangKyHoatDongs()->pluck("id");
        $attendedRegs = \App\Models\DiemDanh::whereIn("dang_ky_hoat_dong_id", $dangKyIds)
            ->where("trang_thai", "co_mat")
            ->pluck("dang_ky_hoat_dong_id");
            
        $activityPoints = HoatDong::whereIn("id", function($query) use ($attendedRegs) {
            $query->select("hoat_dong_id")->from("dang_ky_hoat_dongs")->whereIn("id", $attendedRegs);
        })->sum("diem_du_kien");

        // Points from approved outside evidence
        $evidencePoints = HoSoMinhChung::where("sinh_vien_id", $sinhVienId)
            ->where("trang_thai_duyet", "da_duyet")
            ->sum("diem_de_xuat");

        // Deductions from disciplines
        $deductions = \App\Models\KyLuat::where("sinh_vien_id", $sinhVienId)->sum("diem_tru");

        $totalCriteriaPoints = max(0, min($tiLeRenLuyen, $activityPoints + $evidencePoints - $deductions));

        // Academic points scaled dynamically
        $gpa = \App\Models\DiemHocTap::where("sinh_vien_id", $sinhVienId)
            ->where("hoc_ky_id", $latestSemester->id)
            ->first();
            
        if ($gpa) {
            $gpaPoints = floatval($gpa->diem_he_10) * ($tiLeHocTap / 10.0);
        } else {
            // Default fallback
            $gpaPoints = 7.5 * ($tiLeHocTap / 10.0);
        }

        $totalScore = $totalCriteriaPoints + $gpaPoints;

        $grading = "Khá";
        if ($totalScore >= 90) $grading = "Xuất sắc";
        elseif ($totalScore >= 80) $grading = "Tốt";
        elseif ($totalScore >= 65) $grading = "Khá";
        elseif ($totalScore >= 50) $grading = "Trung bình";
        else $grading = "Yếu";

        \App\Models\DiemRenLuyen::updateOrCreate([
            "sinh_vien_id" => $sinhVienId,
            "hoc_ky_id" => $latestSemester->id,
        ], [
            "tong_diem_tieu_chi" => $totalCriteriaPoints,
            "diem_hoc_tap_quy_doi" => $gpaPoints,
            "diem_tong_hop" => $totalScore,
            "xep_loai" => $grading,
            // Keep existing approval state if record already exists, otherwise default to tam_tinh
            "trang_thai_duyet" => \App\Models\DiemRenLuyen::where("sinh_vien_id", $sinhVienId)
                ->where("hoc_ky_id", $latestSemester->id)
                ->value("trang_thai_duyet") ?: "tam_tinh"
        ]);
    }
}