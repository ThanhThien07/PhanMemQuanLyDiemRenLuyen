<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add role column to users table if it doesn't exist
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('sinh_vien')->after('password');
            });
        }

        // 1. he_dao_taos
        Schema::create('he_dao_taos', function (Blueprint $table) {
            $table->id();
            $table->string('ten_he'); // e.g., Đại học chính quy, Chất lượng cao
            $table->timestamps();
        });

        // 2. khoas
        Schema::create('khoas', function (Blueprint $table) {
            $table->id();
            $table->string('ma_khoa')->unique();
            $table->string('ten_khoa');
            $table->timestamps();
        });

        // 3. nganhs
        Schema::create('nganhs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_nganh')->unique();
            $table->string('ten_nganh');
            $table->foreignId('khoa_id')->constrained('khoas')->onDelete('cascade');
            $table->timestamps();
        });

        // 4. khoa_hocs
        Schema::create('khoa_hocs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_khoa_hoc')->unique(); // e.g., K18, K19
            $table->integer('nam_bat_dau');
            $table->integer('nam_ket_thuc');
            $table->timestamps();
        });

        // 5. lops
        Schema::create('lops', function (Blueprint $table) {
            $table->id();
            $table->string('ma_lop')->unique();
            $table->string('ten_lop');
            $table->foreignId('nganh_id')->constrained('nganhs')->onDelete('cascade');
            $table->foreignId('khoa_hoc_id')->constrained('khoa_hocs')->onDelete('cascade');
            $table->timestamps();
        });

        // 6. roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., admin, sinh_vien, ban_can_su, co_van, ctsv, don_vi_to_chuc
            $table->string('display_name');
            $table->timestamps();
        });

        // 7. role_user
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        // 8. sinh_viens
        Schema::create('sinh_viens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ma_sv')->unique();
            $table->string('ho_ten');
            $table->foreignId('lop_id')->constrained('lops')->onDelete('cascade');
            $table->foreignId('he_dao_tao_id')->constrained('he_dao_taos')->onDelete('cascade');
            $table->timestamps();
        });

        // 9. cau_lac_bos
        Schema::create('cau_lac_bos', function (Blueprint $table) {
            $table->id();
            $table->string('ma_clb')->unique();
            $table->string('ten_clb');
            $table->text('mo_ta')->nullable();
            $table->timestamps();
        });

        // 10. clb_sinh_vien
        Schema::create('clb_sinh_vien', function (Blueprint $table) {
            $table->foreignId('clb_id')->constrained('cau_lac_bos')->onDelete('cascade');
            $table->foreignId('sinh_vien_id')->constrained('sinh_viens')->onDelete('cascade');
            $table->primary(['clb_id', 'sinh_vien_id']);
        });

        // 11. don_vi_to_chucs
        Schema::create('don_vi_to_chucs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_don_vi')->unique();
            $table->string('ten_don_vi');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 12. hoc_kys
        Schema::create('hoc_kys', function (Blueprint $table) {
            $table->id();
            $table->string('ma_hoc_ky')->unique(); // e.g., HK1-2025-2026
            $table->string('ten_hoc_ky');
            $table->string('trang_thai')->default('dang_dien_ra'); // dang_dien_ra, da_khoa
            $table->timestamps();
        });

        // 13. cau_hinh_dot_duyets
        Schema::create('cau_hinh_dot_duyets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hoc_ky_id')->constrained('hoc_kys')->onDelete('cascade');
            $table->date('gd1_bat_dau')->nullable(); // Sinh viên nộp minh chứng / khai báo hoạt động ngoài
            $table->date('gd1_ket_thuc')->nullable();
            $table->date('gd2_bat_dau')->nullable(); // BCS lớp duyệt
            $table->date('gd2_ket_thuc')->nullable();
            $table->date('gd3_bat_dau')->nullable(); // Cố vấn học tập duyệt
            $table->date('gd3_ket_thuc')->nullable();
            $table->date('gd4_bat_dau')->nullable(); // CTSV duyệt
            $table->date('gd4_ket_thuc')->nullable();
            $table->date('gd5_bat_dau')->nullable(); // Khiếu nại và phúc khảo
            $table->date('gd5_ket_thuc')->nullable();
            $table->timestamps();
        });

        // 14. tieu_chi_ren_luyens
        Schema::create('tieu_chi_ren_luyens', function (Blueprint $table) {
            $table->id();
            $table->string('ma_tieu_chi')->unique(); // e.g., TC01, TC02
            $table->string('ten_tieu_chi');
            $table->integer('diem_toi_da');
            $table->timestamps();
        });

        // 15. hoat_dongs
        Schema::create('hoat_dongs', function (Blueprint $table) {
            $table->id();
            $table->string('ma_hoat_dong')->unique();
            $table->string('ten_hoat_dong');
            $table->foreignId('don_vi_to_chuc_id')->constrained('don_vi_to_chucs')->onDelete('cascade');
            $table->foreignId('tieu_chi_id')->constrained('tieu_chi_ren_luyens')->onDelete('cascade');
            $table->string('loai_hoat_dong')->default('trong_truong'); // trong_truong, ngoai_truong
            $table->integer('diem_du_kien');
            $table->integer('slot_toi_da')->default(100);
            $table->dateTime('thoi_gian_bat_dau');
            $table->dateTime('thoi_gian_ket_thuc');
            $table->string('hinh_thuc_diem_danh')->default('qr'); // qr, check_in, signature
            $table->boolean('yeu_cau_minh_chung')->default(false);
            $table->string('trang_thai')->default('da_cong_bo'); // nhap, da_cong_bo, dang_dien_ra, da_ket_thuc, da_huy
            $table->timestamps();
        });

        // 16. dang_ky_hoat_dongs
        Schema::create('dang_ky_hoat_dongs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('sinh_viens')->onDelete('cascade');
            $table->foreignId('hoat_dong_id')->constrained('hoat_dongs')->onDelete('cascade');
            $table->string('trang_thai_dang_ky')->default('da_dang_ky'); // da_dang_ky, da_huy, vangs
            $table->timestamps();
        });

        // 17. diem_danhs
        Schema::create('diem_danhs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dang_ky_hoat_dong_id')->constrained('dang_ky_hoat_dongs')->onDelete('cascade');
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->string('trang_thai')->default('co_mat'); // co_mat, vang_mat
            $table->timestamps();
        });

        // 18. minh_chungs
        Schema::create('minh_chungs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();
        });

        // 19. ho_so_minh_chungs
        Schema::create('ho_so_minh_chungs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('sinh_viens')->onDelete('cascade');
            $table->foreignId('hoat_dong_id')->nullable()->constrained('hoat_dongs')->onDelete('cascade'); // can be null for outside activities
            $table->string('ten_hoat_dong_ngoai')->nullable(); // if outside activity
            $table->foreignId('minh_chung_id')->constrained('minh_chungs')->onDelete('cascade');
            $table->foreignId('tieu_chi_id')->nullable()->constrained('tieu_chi_ren_luyens')->onDelete('cascade');
            $table->integer('diem_de_xuat')->default(0);
            $table->string('trang_thai_duyet')->default('cho_duyet'); // cho_duyet, da_duyet, tu_choi, yeu_cau_bo_sung
            $table->text('ly_do_tu_choi')->nullable();
            $table->foreignId('nguoi_duyet_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 20. ky_luats
        Schema::create('ky_luats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('sinh_viens')->onDelete('cascade');
            $table->foreignId('tieu_chi_id')->constrained('tieu_chi_ren_luyens')->onDelete('cascade');
            $table->foreignId('nguoi_tao_id')->constrained('users')->onDelete('cascade');
            $table->integer('diem_tru')->default(0);
            $table->text('noi_dung');
            $table->foreignId('minh_chung_id')->nullable()->constrained('minh_chungs')->onDelete('cascade');
            $table->timestamps();
        });

        // 21. khieu_nais
        Schema::create('khieu_nais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('sinh_viens')->onDelete('cascade');
            $table->foreignId('hoat_dong_id')->nullable()->constrained('hoat_dongs')->onDelete('cascade');
            $table->text('ly_do');
            $table->foreignId('minh_chung_id')->nullable()->constrained('minh_chungs')->onDelete('cascade');
            $table->string('trang_thai_xu_ly')->default('cho_tiep_nhan'); // cho_tiep_nhan, dang_xu_ly, da_giai_quyet, tu_choi
            $table->text('phan_hoi')->nullable();
            $table->foreignId('nguoi_xu_ly_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 22. diem_hoc_taps
        Schema::create('diem_hoc_taps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('sinh_viens')->onDelete('cascade');
            $table->foreignId('hoc_ky_id')->constrained('hoc_kys')->onDelete('cascade');
            $table->decimal('diem_he_4', 3, 2);
            $table->decimal('diem_he_10', 4, 2);
            $table->decimal('diem_quy_doi', 5, 2); // converted to out of 20 points
            $table->timestamps();
        });

        // 23. diem_ren_luyens
        Schema::create('diem_ren_luyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sinh_vien_id')->constrained('sinh_viens')->onDelete('cascade');
            $table->foreignId('hoc_ky_id')->constrained('hoc_kys')->onDelete('cascade');
            $table->integer('tong_diem_tieu_chi'); // points from criteria up to 80 max
            $table->decimal('diem_hoc_tap_quy_doi', 5, 2); // points from GPA up to 20 max
            $table->decimal('diem_tong_hop', 5, 2); // sum of both, out of 100 max
            $table->string('xep_loai')->nullable(); // Xuất sắc, Tốt, Khá...
            $table->text('minh_chung_gdrive')->nullable();
            $table->string('trang_thai_duyet')->default('tam_tinh'); // tam_tinh, cho_bcs_duyet, cho_cvht_duyet, cho_ctsv_duyet, da_khoa
            $table->timestamps();
        });

        // 24. thong_baos
        Schema::create('thong_baos', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de');
            $table->text('noi_dung');
            $table->string('loai_thong_bao')->default('chung'); // chung, hoat_dong, diem, xet_duyet
            $table->timestamps();
        });

        // 25. nguoi_nhan_thong_baos
        Schema::create('nguoi_nhan_thong_baos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thong_bao_id')->constrained('thong_baos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('da_xem')->default(false);
            $table->timestamps();
        });

        // 26. audit_logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('target_table');
            $table->text('old_data')->nullable();
            $table->text('new_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('nguoi_nhan_thong_baos');
        Schema::dropIfExists('thong_baos');
        Schema::dropIfExists('diem_ren_luyens');
        Schema::dropIfExists('diem_hoc_taps');
        Schema::dropIfExists('khieu_nais');
        Schema::dropIfExists('ky_luats');
        Schema::dropIfExists('ho_so_minh_chungs');
        Schema::dropIfExists('minh_chungs');
        Schema::dropIfExists('diem_danhs');
        Schema::dropIfExists('dang_ky_hoat_dongs');
        Schema::dropIfExists('hoat_dongs');
        Schema::dropIfExists('tieu_chi_ren_luyens');
        Schema::dropIfExists('cau_hinh_dot_duyets');
        Schema::dropIfExists('hoc_kys');
        Schema::dropIfExists('don_vi_to_chucs');
        Schema::dropIfExists('clb_sinh_vien');
        Schema::dropIfExists('cau_lac_bos');
        Schema::dropIfExists('sinh_viens');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('lops');
        Schema::dropIfExists('khoa_hocs');
        Schema::dropIfExists('nganhs');
        Schema::dropIfExists('khoas');
        Schema::dropIfExists('he_dao_taos');
    }
};
