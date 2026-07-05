<?php
namespace App\Http\Controllers;

use App\Models\SinhVien;
use App\Models\HoatDong;
use App\Models\HoSoMinhChung;
use App\Models\DiemRenLuyen;
use App\Models\ThongBao;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $thongBaos = ThongBao::latest()->take(5)->get();

        if ($user->role === "sinh_vien" || $user->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", $user->id)->first();
            if (!$sinhVien) {
                return view("dashboard", ["thongBaos" => $thongBaos, "no_profile" => true]);
            }

            // Stats for student
            $dangKyIds = $sinhVien->dangKyHoatDongs()->pluck("id");
            $hoatDongDaThamGiaCount = \App\Models\DiemDanh::whereIn("dang_ky_hoat_dong_id", $dangKyIds)->where("trang_thai", "co_mat")->count();
            
            $minhChungChoDuyetCount = HoSoMinhChung::where("sinh_vien_id", $sinhVien->id)->where("trang_thai_duyet", "cho_duyet")->count();
            
            $diemRenLuyen = DiemRenLuyen::where("sinh_vien_id", $sinhVien->id)->latest()->first();

            return view("dashboard", [
                "thongBaos" => $thongBaos,
                "sinhVien" => $sinhVien,
                "hoatDongDaThamGiaCount" => $hoatDongDaThamGiaCount,
                "minhChungChoDuyetCount" => $minhChungChoDuyetCount,
                "diemRenLuyen" => $diemRenLuyen
            ]);
        }

        // Stats for Advisor, Organizer, CTSV
        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if ($user->role === 'co_van' && $currentSemester) {
            $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('hoc_ky_id', $currentSemester->id)
                ->pluck('lop_id');
            
            $stats = [
                "total_students" => SinhVien::whereIn('lop_id', $assignedLopIds)->count(),
                "total_activities" => HoatDong::count(),
                "pending_evidences" => HoSoMinhChung::where("trang_thai_duyet", "cho_duyet")
                    ->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                        $q->whereIn('lop_id', $assignedLopIds);
                    })->count(),
                "pending_complaints" => \App\Models\KhieuNai::where("trang_thai_xu_ly", "cho_tiep_nhan")
                    ->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                        $q->whereIn('lop_id', $assignedLopIds);
                    })->count(),
            ];
        } else {
            $stats = [
                "total_students" => SinhVien::count(),
                "total_activities" => HoatDong::count(),
                "pending_evidences" => HoSoMinhChung::where("trang_thai_duyet", "cho_duyet")->count(),
                "pending_complaints" => \App\Models\KhieuNai::where("trang_thai_xu_ly", "cho_tiep_nhan")->count(),
            ];
        }

        return view("dashboard", [
            "thongBaos" => $thongBaos,
            "stats" => $stats
        ]);
    }
}