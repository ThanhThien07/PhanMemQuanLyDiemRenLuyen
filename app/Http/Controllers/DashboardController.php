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

            // Tính dữ liệu biểu đồ cho lớp của sinh viên
            $lopId = $sinhVien->lop_id;
            $xepLoaiCounts = DiemRenLuyen::where('lop_id', $lopId)
                ->select('xep_loai', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('xep_loai')
                ->pluck('count', 'xep_loai')
                ->toArray();

            $categories = ['Xuất sắc', 'Tốt', 'Khá', 'Trung bình', 'Yếu'];
            $donutData = [];
            foreach ($categories as $cat) {
                $donutData[$cat] = $xepLoaiCounts[$cat] ?? 0;
            }

            $lineData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dayLabel = now()->subDays($i)->format('d/m');
                $count = \App\Models\DangKyHoatDong::whereDate('created_at', $date)
                    ->whereHas('sinhVien', function($q) use ($lopId) {
                        $q->where('lop_id', $lopId);
                    })->count();
                $lineData[$dayLabel] = $count;
            }

            return view("dashboard", [
                "thongBaos" => $thongBaos,
                "sinhVien" => $sinhVien,
                "hoatDongDaThamGiaCount" => $hoatDongDaThamGiaCount,
                "minhChungChoDuyetCount" => $minhChungChoDuyetCount,
                "diemRenLuyen" => $diemRenLuyen,
                "donutData" => $donutData,
                "lineData" => $lineData
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

            // Tính dữ liệu biểu đồ cho các lớp phụ trách
            $xepLoaiCounts = DiemRenLuyen::whereIn('lop_id', $assignedLopIds)
                ->select('xep_loai', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('xep_loai')
                ->pluck('count', 'xep_loai')
                ->toArray();

            $categories = ['Xuất sắc', 'Tốt', 'Khá', 'Trung bình', 'Yếu'];
            $donutData = [];
            foreach ($categories as $cat) {
                $donutData[$cat] = $xepLoaiCounts[$cat] ?? 0;
            }

            $lineData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dayLabel = now()->subDays($i)->format('d/m');
                $count = \App\Models\DangKyHoatDong::whereDate('created_at', $date)
                    ->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                        $q->whereIn('lop_id', $assignedLopIds);
                    })->count();
                $lineData[$dayLabel] = $count;
            }
        } else {
            // Thống kê toàn cục dành cho quản trị viên (Admin/CTSV)
            $stats = [
                "total_students" => SinhVien::count(),
                "total_activities" => HoatDong::count(),
                "pending_evidences" => HoSoMinhChung::where("trang_thai_duyet", "cho_duyet")->count(),
                "pending_complaints" => \App\Models\KhieuNai::where("trang_thai_xu_ly", "cho_tiep_nhan")->count(),
            ];

            // Tính dữ liệu biểu đồ toàn trường
            $xepLoaiCounts = DiemRenLuyen::select('xep_loai', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('xep_loai')
                ->pluck('count', 'xep_loai')
                ->toArray();

            $categories = ['Xuất sắc', 'Tốt', 'Khá', 'Trung bình', 'Yếu'];
            $donutData = [];
            foreach ($categories as $cat) {
                $donutData[$cat] = $xepLoaiCounts[$cat] ?? 0;
            }

            $lineData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dayLabel = now()->subDays($i)->format('d/m');
                $count = \App\Models\DangKyHoatDong::whereDate('created_at', $date)->count();
                $lineData[$dayLabel] = $count;
            }
        }

        return view("dashboard", [
            "thongBaos" => $thongBaos,
            "stats" => $stats,
            "donutData" => $donutData,
            "lineData" => $lineData
        ]);
    }
}