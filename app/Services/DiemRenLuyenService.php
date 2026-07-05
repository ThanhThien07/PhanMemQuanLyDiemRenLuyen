<?php

namespace App\Services;

use App\Models\SinhVien;
use App\Models\HocKy;
use App\Models\HoatDong;
use App\Models\DiemDanh;
use App\Models\HoSoMinhChung;
use App\Models\KyLuat;
use App\Models\DiemHocTap;
use App\Models\DiemRenLuyen;

/**
 * Service xử lý tính toán điểm rèn luyện của Sinh Viên.
 * Cô lập logic nghiệp vụ (business logic) khỏi các controller.
 */
class DiemRenLuyenService
{
    /**
     * Tính toán lại điểm rèn luyện của sinh viên dựa trên:
     * - Hoạt động trong trường đã tham gia & điểm danh có mặt.
     * - Minh chứng ngoài trường đã được phê duyệt.
     * - Điểm trừ kỷ luật.
     * - Điểm học tập quy đổi (từ điểm GPA hệ 10).
     *
     * @param int $sinhVienId ID của sinh viên cần tính
     * @param int|null $semesterId ID của học kỳ (nếu null sẽ lấy học kỳ đang diễn ra)
     * @return void
     */
    public static function recalculatePoints($sinhVienId, $semesterId = null)
    {
        // 1. Tìm thông tin sinh viên
        $sinhVien = SinhVien::findOrFail($sinhVienId);
        
        // 2. Xác định học kỳ tính điểm
        $latestSemester = null;
        if ($semesterId) {
            $latestSemester = HocKy::find($semesterId);
        }
        if (!$latestSemester) {
            $latestSemester = HocKy::where("trang_thai", "dang_dien_ra")->first() ?: HocKy::first();
        }
        
        // Nếu hệ thống không có học kỳ nào, dừng việc tính toán
        if (!$latestSemester) {
            return;
        }

        // 3. Lấy cấu hình tỷ lệ điểm (mặc định 80% rèn luyện và 20% học tập)
        $tiLeRenLuyen = floatval($latestSemester->ti_le_ren_luyen ?? 80.00);
        $tiLeHocTap = floatval($latestSemester->ti_le_hoc_tap ?? 20.00);

        // 4. Tính điểm từ các hoạt động trong trường đã tham gia (điểm danh có mặt)
        $dangKyIds = $sinhVien->dangKyHoatDongs()->pluck("id");
        $attendedRegs = DiemDanh::whereIn("dang_ky_hoat_dong_id", $dangKyIds)
            ->where("trang_thai", "co_mat")
            ->pluck("dang_ky_hoat_dong_id");
            
        $activityPoints = HoatDong::whereIn("id", function($query) use ($attendedRegs) {
            $query->select("hoat_dong_id")
                  ->from("dang_ky_hoat_dongs")
                  ->whereIn("id", $attendedRegs);
        })->sum("diem_du_kien");

        // 5. Tính điểm từ các hồ sơ minh chứng ngoài trường đã được duyệt
        $evidencePoints = HoSoMinhChung::where("sinh_vien_id", $sinhVienId)
            ->where("trang_thai_duyet", "da_duyet")
            ->sum("diem_de_xuat");

        // 6. Tính tổng điểm trừ kỷ luật
        $deductions = KyLuat::where("sinh_vien_id", $sinhVienId)->sum("diem_tru");

        // 7. Tổng điểm các tiêu chí rèn luyện (giới hạn tối thiểu là 0, tối đa là tỷ lệ điểm rèn luyện)
        $totalCriteriaPoints = max(0, min($tiLeRenLuyen, $activityPoints + $evidencePoints - $deductions));

        // 8. Tính điểm học tập quy đổi từ điểm GPA hệ 10
        $gpa = DiemHocTap::where("sinh_vien_id", $sinhVienId)
            ->where("hoc_ky_id", $latestSemester->id)
            ->first();
            
        if ($gpa) {
            // Công thức quy đổi: GPA hệ 10 * (tỉ lệ học tập / 10)
            $gpaPoints = floatval($gpa->diem_he_10) * ($tiLeHocTap / 10.0);
        } else {
            // Giá trị mặc định nếu chưa cập nhật điểm GPA (mặc định 7.5)
            $gpaPoints = 7.5 * ($tiLeHocTap / 10.0);
        }

        // 9. Tính tổng điểm tổng hợp (rèn luyện + học tập)
        $totalScore = $totalCriteriaPoints + $gpaPoints;

        // 10. Xếp loại rèn luyện
        $grading = "Khá";
        if ($totalScore >= 90) {
            $grading = "Xuất sắc";
        } elseif ($totalScore >= 80) {
            $grading = "Tốt";
        } elseif ($totalScore >= 65) {
            $grading = "Khá";
        } elseif ($totalScore >= 50) {
            $grading = "Trung bình";
        } else {
            $grading = "Yếu";
        }

        // 11. Lưu hoặc cập nhật kết quả vào bảng điểm rèn luyện
        DiemRenLuyen::updateOrCreate([
            "sinh_vien_id" => $sinhVienId,
            "hoc_ky_id" => $latestSemester->id,
        ], [
            "tong_diem_tieu_chi" => $totalCriteriaPoints,
            "diem_hoc_tap_quy_doi" => $gpaPoints,
            "diem_tong_hop" => $totalScore,
            "xep_loai" => $grading,
            // Giữ lại trạng thái duyệt cũ nếu đã có, ngược lại mặc định là 'tam_tinh'
            "trang_thai_duyet" => DiemRenLuyen::where("sinh_vien_id", $sinhVienId)
                ->where("hoc_ky_id", $latestSemester->id)
                ->value("trang_thai_duyet") ?: "tam_tinh"
        ]);
    }
}
