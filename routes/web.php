<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HoatDongController;
use App\Http\Controllers\MinhChungController;
use App\Http\Controllers\XetDuyetController;
use App\Http\Controllers\DiemRenLuyenController;
use App\Http\Controllers\KhieuNaiController;
use App\Http\Controllers\HocKyController;
use App\Http\Controllers\BackupController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Hoạt động rèn luyện
    Route::get('/hoat-dong', [HoatDongController::class, 'index'])->name('hoat_dong.index');
    Route::get('/hoat-dong/create', [HoatDongController::class, 'create'])->name('hoat_dong.create')->middleware('role:ctsv');
    Route::post('/hoat-dong', [HoatDongController::class, 'store'])->name('hoat_dong.store')->middleware('role:ctsv');
    Route::get('/hoat-dong/{id}', [HoatDongController::class, 'show'])->name('hoat_dong.show');
    Route::post('/hoat-dong/{id}/dang-ky', [HoatDongController::class, 'register'])->name('hoat_dong.register')->middleware('role:sinh_vien,ban_can_su');
    Route::post('/hoat-dong/{id}/huy-dang-ky', [HoatDongController::class, 'cancel'])->name('hoat_dong.cancel')->middleware('role:sinh_vien,ban_can_su');
    Route::get('/hoat-dong/{id}/diem-danh', [HoatDongController::class, 'attendanceList'])->name('hoat_dong.attendance')->middleware('role:ctsv');
    Route::post('/hoat-dong/diem-danh/{id}', [HoatDongController::class, 'updateAttendance'])->name('hoat_dong.update_attendance')->middleware('role:ctsv');
    Route::get('/hoat-dong/{id}/diem-danh-qr', [HoatDongController::class, 'diemDanhQr'])->name('hoat_dong.diem_danh_qr')->middleware('role:sinh_vien,ban_can_su');
    Route::get('/hoat-dong/check-attendance/{id}', [HoatDongController::class, 'checkAttendance'])->name('hoat_dong.check_attendance');

    // Minh chứng
    Route::get('/minh-chung', [MinhChungController::class, 'index'])->name('minh_chung.index');
    Route::post('/minh-chung', [MinhChungController::class, 'store'])->name('minh_chung.store')->middleware('role:sinh_vien,ban_can_su');
    Route::post('/minh-chung/duyet/{id}', [MinhChungController::class, 'updateStatus'])->name('minh_chung.update_status')->middleware('role:co_van,ctsv');

    // Xét duyệt điểm lớp
    Route::get('/xet-duyet/phan-cong', [XetDuyetController::class, 'showPhanCong'])->name('xet_duyet.phan_cong')->middleware('role:ctsv');
    Route::post('/xet-duyet/phan-cong', [XetDuyetController::class, 'savePhanCong'])->name('xet_duyet.save_phan_cong')->middleware('role:ctsv');
    Route::post('/xet-duyet/phan-cong/delete/{id}', [XetDuyetController::class, 'deletePhanCong'])->name('xet_duyet.delete_phan_cong')->middleware('role:ctsv');
    Route::get('/xet-duyet', [XetDuyetController::class, 'index'])->name('xet_duyet.index')->middleware('role:ban_can_su,co_van,ctsv');
    Route::post('/xet-duyet/update/{id}', [XetDuyetController::class, 'updateStatus'])->name('xet_duyet.update_status')->middleware('role:ban_can_su,co_van,ctsv');
    Route::get('/xet-duyet/{id}/danh-gia', [XetDuyetController::class, 'showReviewEvaluation'])->name('xet_duyet.review_evaluation')->middleware('role:ban_can_su,co_van,ctsv');
    Route::post('/xet-duyet/{id}/danh-gia', [XetDuyetController::class, 'saveReviewEvaluation'])->name('xet_duyet.save_review_evaluation')->middleware('role:ban_can_su,co_van,ctsv');
    Route::post('/xet-duyet/bulk-approve', [XetDuyetController::class, 'bulkApprove'])->name('xet_duyet.bulk_approve')->middleware('role:ban_can_su,co_van,ctsv');
    Route::post('/xet-duyet/unlock/{id}', [XetDuyetController::class, 'unlockEvaluation'])->name('xet_duyet.unlock')->middleware('role:ctsv');

    // Bảng điểm rèn luyện
    Route::get('/diem-ren-luyen', [DiemRenLuyenController::class, 'index'])->name('diem_ren_luyen.index');
    Route::get('/diem-ren-luyen/tu-danh-gia', [DiemRenLuyenController::class, 'showSelfEvaluation'])->name('diem_ren_luyen.self_evaluation')->middleware('role:sinh_vien,ban_can_su');
    Route::post('/diem-ren-luyen/tu-danh-gia', [DiemRenLuyenController::class, 'saveSelfEvaluation'])->name('diem_ren_luyen.save_self_evaluation')->middleware('role:sinh_vien,ban_can_su');
    Route::get('/diem-ren-luyen/bao-cao', [DiemRenLuyenController::class, 'reportIndex'])->name('diem_ren_luyen.report')->middleware('role:co_van,ctsv');
    Route::get('/diem-ren-luyen/bao-cao/export', [DiemRenLuyenController::class, 'exportCsv'])->name('diem_ren_luyen.export')->middleware('role:co_van,ctsv');

    // Cấu hình học kỳ
    Route::get('/hoc-ky/settings', [HocKyController::class, 'settings'])->name('hoc_ky.settings')->middleware('role:ctsv');
    Route::post('/hoc-ky/settings', [HocKyController::class, 'updateSettings'])->name('hoc_ky.settings.update')->middleware('role:ctsv');

    // Sao lưu và khôi phục dữ liệu
    Route::get('/backup', [BackupController::class, 'index'])->name('backup.index')->middleware('role:ctsv');
    Route::post('/backup/run', [BackupController::class, 'runManual'])->name('backup.run')->middleware('role:ctsv');
    Route::post('/backup/settings', [BackupController::class, 'updateSettings'])->name('backup.settings.update')->middleware('role:ctsv');
    Route::get('/backup/download/{file}', [BackupController::class, 'download'])->name('backup.download')->middleware('role:ctsv');
    Route::delete('/backup/delete/{file}', [BackupController::class, 'delete'])->name('backup.delete')->middleware('role:ctsv');
    Route::post('/backup/restore/{file}', [BackupController::class, 'restore'])->name('backup.restore')->middleware('role:ctsv');
    Route::post('/backup/restore-upload', [BackupController::class, 'restoreUpload'])->name('backup.restore_upload')->middleware('role:ctsv');

    // Khiếu nại phúc khảo
    Route::get('/khieu-nai', [KhieuNaiController::class, 'index'])->name('khieu_nai.index');
    Route::post('/khieu-nai', [KhieuNaiController::class, 'store'])->name('khieu_nai.store')->middleware('role:sinh_vien,ban_can_su');
    Route::post('/khieu-nai/reply/{id}', [KhieuNaiController::class, 'reply'])->name('khieu_nai.reply')->middleware('role:ban_can_su,co_van,ctsv');

});