<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\HeDaoTao;
use App\Models\Khoa;
use App\Models\Nganh;
use App\Models\KhoaHoc;
use App\Models\Lop;
use App\Models\SinhVien;
use App\Models\CauLacBo;
use App\Models\DonViToChuc;
use App\Models\HocKy;
use App\Models\CauHinhDotDuyet;
use App\Models\TieuChiRenLuyen;
use App\Models\HoatDong;
use App\Models\DangKyHoatDong;
use App\Models\DiemDanh;
use App\Models\DiemHocTap;
use App\Models\DiemRenLuyen;
use App\Models\ThongBao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roles = [
            ['name' => 'admin', 'display_name' => 'Phòng Công tác sinh viên (CTSV)'],
            ['name' => 'sinh_vien', 'display_name' => 'Sinh viên'],
            ['name' => 'ban_can_su', 'display_name' => 'Ban cán sự lớp'],
            ['name' => 'co_van', 'display_name' => 'Cố vấn học tập'],
        ];
        
        $roleIds = [];
        foreach ($roles as $r) {
            $roleModel = Role::create($r);
            $roleIds[$r['name']] = $roleModel->id;
        }

        // 2. Create Users
        $usersData = [
            [
                'name' => 'Phòng Công tác sinh viên (Admin)',
                'email' => 'ctsv@sv.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'name' => 'Nguyễn Văn A (Sinh viên)',
                'email' => 'sinhvien@sv.com',
                'password' => Hash::make('password'),
                'role' => 'sinh_vien'
            ],
            [
                'name' => 'Trần Thị B (Lớp trưởng)',
                'email' => 'bcs@sv.com',
                'password' => Hash::make('password'),
                'role' => 'ban_can_su'
            ],
            [
                'name' => 'Thầy Nguyễn Văn Cố Vấn',
                'email' => 'covan@sv.com',
                'password' => Hash::make('password'),
                'role' => 'co_van'
            ],
        ];

        $users = [];
        foreach ($usersData as $ud) {
            $user = User::create($ud);
            $user->roles()->attach($roleIds[$ud['role']]);
            $users[$ud['role']] = $user;
        }

        // 3. Create HeDaoTao
        $hdt = HeDaoTao::create(['ten_he' => 'Đại học chính quy']);

        // 4. Create Khoa
        $khoa = Khoa::create([
            'ma_khoa' => 'CNTT',
            'ten_khoa' => 'Khoa Công nghệ thông tin'
        ]);

        // 5. Create Nganh
        $nganh = Nganh::create([
            'ma_nganh' => 'KTPM',
            'ten_nganh' => 'Kỹ thuật phần mềm',
            'khoa_id' => $khoa->id
        ]);

        // 6. Create KhoaHoc
        $khoaHoc = KhoaHoc::create([
            'ma_khoa_hoc' => 'K18',
            'nam_bat_dau' => 2022,
            'nam_ket_thuc' => 2026
        ]);

        // 7. Create Lop
        $lop = Lop::create([
            'ma_lop' => 'PM1801',
            'ten_lop' => 'Kỹ thuật phần mềm 1801',
            'nganh_id' => $nganh->id,
            'khoa_hoc_id' => $khoaHoc->id
        ]);

        // 8. Create SinhVien profile for sinh_vien and ban_can_su
        $sv1 = SinhVien::create([
            'user_id' => $users['sinh_vien']->id,
            'ma_sv' => 'SV220101',
            'ho_ten' => 'Nguyễn Văn A',
            'lop_id' => $lop->id,
            'he_dao_tao_id' => $hdt->id
        ]);

        $sv2 = SinhVien::create([
            'user_id' => $users['ban_can_su']->id,
            'ma_sv' => 'SV220102',
            'ho_ten' => 'Trần Thị B',
            'lop_id' => $lop->id,
            'he_dao_tao_id' => $hdt->id
        ]);

        // 9. CLBs
        $clb = CauLacBo::create([
            'ma_clb' => 'CLB_IT',
            'ten_clb' => 'Câu lạc bộ Tin học',
            'mo_ta' => 'Nơi kết nối đam mê lập trình sinh viên.'
        ]);
        $clb->sinhViens()->attach([$sv1->id, $sv2->id]);

        // 10. DonViToChuc (Linked to CTSV/Admin)
        $donVi = DonViToChuc::create([
            'ma_don_vi' => 'CTSV',
            'ten_don_vi' => 'Phòng Công tác sinh viên (CTSV)',
            'user_id' => $users['admin']->id
        ]);

        // 11. HocKy
        $hk = HocKy::create([
            'ma_hoc_ky' => 'HK1-2025-2026',
            'ten_hoc_ky' => 'Học kỳ 1 năm học 2025-2026',
            'trang_thai' => 'dang_dien_ra'
        ]);

        // 11b. PhanCongCoVan for Thầy Nguyễn Văn Cố Vấn
        \App\Models\PhanCongCoVan::create([
            'user_id' => $users['co_van']->id,
            'lop_id' => $lop->id,
            'hoc_ky_id' => $hk->id
        ]);

        // 12. CauHinhDotDuyet
        CauHinhDotDuyet::create([
            'hoc_ky_id' => $hk->id,
            'gd1_bat_dau' => '2026-06-01',
            'gd1_ket_thuc' => '2026-06-15',
            'gd2_bat_dau' => '2026-06-16',
            'gd2_ket_thuc' => '2026-06-20',
            'gd3_bat_dau' => '2026-06-21',
            'gd3_ket_thuc' => '2026-06-25',
            'gd4_bat_dau' => '2026-06-26',
            'gd4_ket_thuc' => '2026-06-30',
        ]);

        // 13. TieuChiRenLuyen
        $tieuChis = [
            ['ma_tieu_chi' => 'TC1', 'ten_tieu_chi' => 'Đánh giá về ý thức tham gia học tập', 'diem_toi_da' => 20],
            ['ma_tieu_chi' => 'TC2', 'ten_tieu_chi' => 'Đánh giá về ý thức chấp hành luật lệ, nội quy', 'diem_toi_da' => 25],
            ['ma_tieu_chi' => 'TC3', 'ten_tieu_chi' => 'Đánh giá về ý thức tham gia các hoạt động chính trị, xã hội, văn hóa', 'diem_toi_da' => 20],
            ['ma_tieu_chi' => 'TC4', 'ten_tieu_chi' => 'Đánh giá về ý thức công dân trong quan hệ cộng đồng', 'diem_toi_da' => 25],
            ['ma_tieu_chi' => 'TC5', 'ten_tieu_chi' => 'Đánh giá về ý thức và kết quả tham gia công tác phụ trách lớp', 'diem_toi_da' => 10],
        ];

        $tcIds = [];
        foreach ($tieuChis as $tc) {
            $model = TieuChiRenLuyen::create($tc);
            $tcIds[$tc['ma_tieu_chi']] = $model->id;
        }

        // 14. HoatDongs
        $hd1 = HoatDong::create([
            'ma_hoat_dong' => 'HD001',
            'ten_hoat_dong' => 'Ngày hội Việc làm & Công nghệ năm 2026',
            'don_vi_to_chuc_id' => $donVi->id,
            'tieu_chi_id' => $tcIds['TC3'],
            'loai_hoat_dong' => 'trong_truong',
            'diem_du_kien' => 5,
            'slot_toi_da' => 150,
            'thoi_gian_bat_dau' => '2026-06-15 08:00:00',
            'thoi_gian_ket_thuc' => '2026-06-15 17:00:00',
            'hinh_thuc_diem_danh' => 'qr',
            'yeu_cau_minh_chung' => false,
            'trang_thai' => 'da_cong_bo'
        ]);

        $hd2 = HoatDong::create([
            'ma_hoat_dong' => 'HD002',
            'ten_hoat_dong' => 'Hội nghị học tập & Phương pháp nghiên cứu khoa học',
            'don_vi_to_chuc_id' => $donVi->id,
            'tieu_chi_id' => $tcIds['TC1'],
            'loai_hoat_dong' => 'trong_truong',
            'diem_du_kien' => 4,
            'slot_toi_da' => 100,
            'thoi_gian_bat_dau' => '2026-06-18 09:00:00',
            'thoi_gian_ket_thuc' => '2026-06-18 11:30:00',
            'hinh_thuc_diem_danh' => 'qr',
            'yeu_cau_minh_chung' => false,
            'trang_thai' => 'da_cong_bo'
        ]);

        // 15. DangKy & DiemDanh for SinhVien 1
        $dk1 = DangKyHoatDong::create([
            'sinh_vien_id' => $sv1->id,
            'hoat_dong_id' => $hd1->id,
            'trang_thai_dang_ky' => 'da_dang_ky'
        ]);

        DiemDanh::create([
            'dang_ky_hoat_dong_id' => $dk1->id,
            'check_in_time' => '2026-06-15 08:15:00',
            'trang_thai' => 'co_mat'
        ]);

        $dk2 = DangKyHoatDong::create([
            'sinh_vien_id' => $sv1->id,
            'hoat_dong_id' => $hd2->id,
            'trang_thai_dang_ky' => 'da_dang_ky'
        ]);

        DiemDanh::create([
            'dang_ky_hoat_dong_id' => $dk2->id,
            'check_in_time' => '2026-06-18 09:05:00',
            'trang_thai' => 'co_mat'
        ]);

        // 16. DiemHocTap setup
        DiemHocTap::create([
            'sinh_vien_id' => $sv1->id,
            'hoc_ky_id' => $hk->id,
            'diem_he_4' => 3.20,
            'diem_he_10' => 8.00,
            'diem_quy_doi' => 16.00 // out of 20
        ]);

        DiemHocTap::create([
            'sinh_vien_id' => $sv2->id,
            'hoc_ky_id' => $hk->id,
            'diem_he_4' => 3.50,
            'diem_he_10' => 8.75,
            'diem_quy_doi' => 17.50
        ]);

        // 17. Initial DiemRenLuyen transcripts
        // SV1 has 5 + 4 = 9 points from activities + 16.00 GPA = 25.00 points
        DiemRenLuyen::create([
            'sinh_vien_id' => $sv1->id,
            'hoc_ky_id' => $hk->id,
            'tong_diem_tieu_chi' => 9,
            'diem_hoc_tap_quy_doi' => 16.00,
            'diem_tong_hop' => 25.00,
            'xep_loai' => 'Yếu',
            'trang_thai_duyet' => 'tam_tinh'
        ]);

        DiemRenLuyen::create([
            'sinh_vien_id' => $sv2->id,
            'hoc_ky_id' => $hk->id,
            'tong_diem_tieu_chi' => 0,
            'diem_hoc_tap_quy_doi' => 17.50,
            'diem_tong_hop' => 17.50,
            'xep_loai' => 'Yếu',
            'trang_thai_duyet' => 'tam_tinh'
        ]);

        // 18. ThongBao
        ThongBao::create([
            'tieu_de' => 'Khởi động đợt đánh giá rèn luyện Học kỳ 1 2025-2026',
            'noi_dung' => 'Yêu cầu toàn bộ sinh viên truy cập hệ thống để kiểm tra thông tin điểm danh và upload minh chứng hoạt động ngoài trường trước ngày 15/06/2026.',
            'loai_thong_bao' => 'xet_duyet'
        ]);

        ThongBao::create([
            'tieu_de' => 'Về việc nộp các hồ sơ minh chứng chiến dịch Mùa Hè Xanh',
            'noi_dung' => 'Các bạn sinh viên tham gia chiến dịch tình nguyện Mùa Hè Xanh chủ động chụp ảnh và tải giấy chứng nhận lên phần Nộp Minh Chứng để được cộng điểm tiêu chí TC3.',
            'loai_thong_bao' => 'hoat_dong'
        ]);
    }
}
