<?php
namespace App\Http\Controllers;

use App\Models\SinhVien;
use App\Models\HoatDong;
use App\Models\HoSoMinhChung;
use App\Models\DiemRenLuyen;
use App\Models\ThongBao;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Controller quản lý giao diện Dashboard chính.
 * Hiển thị số liệu thống kê nhanh tùy thuộc vào vai trò của người dùng.
 */
class DashboardController extends Controller
{
    /**
     * Hiển thị trang Dashboard.
     * - Sinh viên/Ban cán sự: Hiển thị điểm tích lũy tạm tính, hoạt động tham gia, minh chứng chờ duyệt.
     * - Cố vấn học tập: Thống kê số lượng sinh viên phụ trách, minh chứng và khiếu nại chờ xử lý của các lớp được phân công.
     * - Admin/CTSV: Thống kê tổng quan toàn trường.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Lấy 5 thông báo mới nhất để hiển thị
        $thongBaos = ThongBao::latest()->take(5)->get();

        // 1. Dành cho sinh viên hoặc ban cán sự lớp
        if ($user->role === "sinh_vien" || $user->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", $user->id)->first();
            if (!$sinhVien) {
                return view("dashboard", ["thongBaos" => $thongBaos, "no_profile" => true]);
            }

            // Thống kê số hoạt động trong trường sinh viên đã tham gia và được điểm danh có mặt
            $dangKyIds = $sinhVien->dangKyHoatDongs()->pluck("id");
            $hoatDongDaThamGiaCount = \App\Models\DiemDanh::whereIn("dang_ky_hoat_dong_id", $dangKyIds)
                ->where("trang_thai", "co_mat")
                ->count();
            
            // Đếm số minh chứng ngoài trường đang chờ duyệt
            $minhChungChoDuyetCount = HoSoMinhChung::where("sinh_vien_id", $sinhVien->id)
                ->where("trang_thai_duyet", "cho_duyet")
                ->count();
            
            // Lấy kết quả điểm rèn luyện mới nhất
            $diemRenLuyen = DiemRenLuyen::where("sinh_vien_id", $sinhVien->id)->latest()->first();

            return view("dashboard", [
                "thongBaos" => $thongBaos,
                "sinhVien" => $sinhVien,
                "hoatDongDaThamGiaCount" => $hoatDongDaThamGiaCount,
                "minhChungChoDuyetCount" => $minhChungChoDuyetCount,
                "diemRenLuyen" => $diemRenLuyen
            ]);
        }

        // 2. Dành cho Cố vấn học tập, đơn vị tổ chức, hoặc CTSV/Admin
        $currentSemester = \App\Models\HocKy::where("trang_thai", "dang_dien_ra")->first() ?: \App\Models\HocKy::first();
        if ($user->role === 'co_van' && $currentSemester) {
            // Lấy danh sách ID lớp học được phân công cho Cố vấn trong học kỳ hiện tại
            $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('hoc_ky_id', $currentSemester->id)
                ->pluck('lop_id');
            
            $stats = [
                "total_students" => SinhVien::whereIn('lop_id', $assignedLopIds)->count(),
                "total_activities" => HoatDong::count(),
                // Đếm hồ sơ minh chứng chờ duyệt của sinh viên các lớp phụ trách
                "pending_evidences" => HoSoMinhChung::where("trang_thai_duyet", "cho_duyet")
                    ->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                        $q->whereIn('lop_id', $assignedLopIds);
                    })->count(),
                // Đếm yêu cầu khiếu nại chờ xử lý của các lớp phụ trách
                "pending_complaints" => \App\Models\KhieuNai::where("trang_thai_xu_ly", "cho_tiep_nhan")
                    ->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                        $q->whereIn('lop_id', $assignedLopIds);
                    })->count(),
            ];
        } else {
            // Thống kê toàn cục dành cho quản trị viên (Admin/CTSV)
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