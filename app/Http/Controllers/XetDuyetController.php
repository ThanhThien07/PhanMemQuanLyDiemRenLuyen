<?php
namespace App\Http\Controllers;

use App\Models\SinhVien;
use App\Models\DiemRenLuyen;
use App\Models\HocKy;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\EvaluationCriteria;
use App\Models\ChiTietDiemRenLuyen;
use App\Models\DiemHocTap;
use App\Models\HoatDong;
use Illuminate\Support\Facades\DB;

/**
 * Controller xử lý xét duyệt điểm rèn luyện của sinh viên theo các cấp.
 * Các vai trò tham gia: Ban cán sự lớp, Cố vấn học tập, Admin (Phòng CTSV).
 */
class XetDuyetController extends Controller
{
    /**
     * Hiển thị danh sách phiếu điểm rèn luyện chờ xét duyệt.
     * - Ban cán sự: Xem danh sách sinh viên của lớp mình.
     * - Cố vấn học tập: Xem danh sách sinh viên các lớp mình được phân công trong học kỳ.
     * - Admin/CTSV: Xem toàn bộ danh sách sinh viên hệ thống.
     */
    public function index()
    {
        $user = Auth::user();
        $hocKy = HocKy::where("trang_thai", "dang_dien_ra")->first() ?: HocKy::first();
        
        if (!$hocKy) {
            return view("xet_duyet.index", ["error" => "Chưa cấu hình học kỳ nào."]);
        }

        // Eager load các quan hệ để tránh lỗi N+1 query
        $query = DiemRenLuyen::with(["sinhVien.lop", "lop", "hocKy"]);

        // 1. Phân quyền hiển thị danh sách cho Ban cán sự lớp
        if ($user->role === "ban_can_su") {
            $sv = SinhVien::where("user_id", $user->id)->first();
            if ($sv) {
                $lopId = $sv->lop_id;
                $query->where(function($q) use ($lopId) {
                    $q->where("lop_id", $lopId)
                      ->orWhere(function($sub) use ($lopId) {
                          $sub->whereNull("lop_id")
                              ->whereIn("sinh_vien_id", function($svQ) use ($lopId) {
                                  $svQ->select("id")->from("sinh_viens")->where("lop_id", $lopId);
                              });
                      });
                });
            }
        } 
        // 2. Phân quyền hiển thị danh sách cho Cố vấn học tập
        elseif ($user->role === "co_van") {
            $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('hoc_ky_id', $hocKy->id)
                ->pluck('lop_id');
                
            $query->where(function($q) use ($assignedLopIds) {
                $q->whereIn("lop_id", $assignedLopIds)
                  ->orWhere(function($sub) use ($assignedLopIds) {
                      $sub->whereNull("lop_id")
                          ->whereIn("sinh_vien_id", function($svQ) use ($assignedLopIds) {
                              $svQ->select("id")->from("sinh_viens")->whereIn("lop_id", $assignedLopIds);
                          });
                  });
            });
        }
        
        $diemRenLuyens = $query->get();

        return view("xet_duyet.index", compact("diemRenLuyens", "hocKy"));
    }

    /**
     * Cập nhật trạng thái duyệt của một phiếu điểm rèn luyện (chuyển giai đoạn).
     * Sử dụng Transaction để đồng bộ lưu dữ liệu và ghi Audit Log.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $drl = DiemRenLuyen::findOrFail($id);
        $oldState = $drl->trang_thai_duyet;
        $targetState = $request->trang_thai;

        // 1. Kiểm tra quyền hạn khi Ban cán sự cập nhật
        if ($user->role === 'ban_can_su') {
            $svBCS = SinhVien::where("user_id", $user->id)->first();
            $student = SinhVien::findOrFail($drl->sinh_vien_id);
            if (!$svBCS || $svBCS->lop_id !== $student->lop_id) {
                return response()->json(["success" => false, "message" => "Bạn không có quyền duyệt sinh viên lớp khác."], 403);
            }
            
            // BCS chỉ được phép chuyển trạng thái lên Chờ cố vấn duyệt
            if (!in_array($oldState, ['tam_tinh', 'cho_bcs_duyet']) || $targetState !== 'cho_cvht_duyet') {
                return response()->json(["success" => false, "message" => "Trạng thái chuyển tiếp không hợp lệ cho Ban cán sự."], 400);
            }
        } 
        // 2. Kiểm tra quyền hạn khi Cố vấn học tập cập nhật
        elseif ($user->role === 'co_van') {
            $targetLopId = $drl->lop_id ?: ($drl->sinhVien ? $drl->sinhVien->lop_id : null);
            $isAssigned = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('lop_id', $targetLopId)
                ->where('hoc_ky_id', $drl->hoc_ky_id)
                ->exists();
            if (!$isAssigned) {
                return response()->json(["success" => false, "message" => "Bạn không có quyền duyệt sinh viên lớp khác."], 403);
            }

            // CVHT duyệt gửi lên CTSV hoặc trả về trạng thái tạm tính cho sinh viên sửa đổi
            if ($targetState === 'cho_ctsv_duyet') {
                if ($oldState !== 'cho_cvht_duyet') {
                    return response()->json(["success" => false, "message" => "Chỉ có thể duyệt khi trạng thái là Chờ cố vấn duyệt."], 400);
                }
            } elseif ($targetState === 'tam_tinh') {
                if ($oldState !== 'cho_cvht_duyet') {
                    return response()->json(["success" => false, "message" => "Chỉ có thể trả về khi trạng thái là Chờ cố vấn duyệt."], 400);
                }
            } else {
                return response()->json(["success" => false, "message" => "Hành động duyệt không hợp lệ cho Cố vấn."], 400);
            }
        } 
        // 3. Kiểm tra quyền hạn của Admin (Phòng CTSV)
        elseif ($user->role === 'admin') {
            if ($targetState === 'da_khoa') {
                if ($oldState !== 'cho_ctsv_duyet') {
                    return response()->json(["success" => false, "message" => "Chỉ có thể khóa khi trạng thái là Chờ CTSV phê duyệt."], 400);
                }
            } elseif ($targetState === 'cho_cvht_duyet') {
                if ($oldState !== 'cho_ctsv_duyet') {
                    return response()->json(["success" => false, "message" => "Chỉ có thể trả về khi trạng thái là Chờ CTSV phê duyệt."], 400);
                }
            } else {
                if (!in_array($targetState, ['tam_tinh', 'cho_cvht_duyet', 'cho_ctsv_duyet', 'da_khoa'])) {
                    return response()->json(["success" => false, "message" => "Trạng thái không hợp lệ."], 400);
                }
            }
        } else {
            return response()->json(["success" => false, "message" => "Vai trò không hợp lệ để duyệt."], 403);
        }

        // Thực hiện ghi dữ liệu an toàn với Database Transaction
        DB::transaction(function() use ($drl, $targetState, $oldState) {
            $drl->trang_thai_duyet = $targetState;
            $drl->save();

            AuditLog::create([
                "user_id" => Auth::id(),
                "action" => "Duyệt học kỳ điểm rèn luyện",
                "target_table" => "diem_ren_luyens",
                "old_data" => json_encode(["status" => $oldState]),
                "new_data" => json_encode(["status" => $targetState])
            ]);
        });

        return response()->json(["success" => true]);
    }

    /**
     * Hiển thị bảng chi tiết các tiêu chí điểm để Cán sự/Cố vấn rà soát chỉnh sửa điểm.
     */
    public function showReviewEvaluation($id)
    {
        $user = Auth::user();
        $diemRenLuyen = DiemRenLuyen::with(['sinhVien.lop.nganh.khoa', 'sinhVien.heDaoTao', 'hocKy'])->findOrFail($id);
        $sinhVien = $diemRenLuyen->sinhVien;

        // Xác thực bảo mật quyền truy cập hồ sơ
        if ($user->role === 'ban_can_su') {
            $svBCS = SinhVien::where('user_id', $user->id)->first();
            if (!$svBCS || $svBCS->lop_id !== ($diemRenLuyen->lop_id ?: $sinhVien->lop_id)) {
                abort(403, 'Bạn không có quyền xem phiếu điểm của lớp này.');
            }
        } elseif ($user->role === 'co_van') {
            $targetLopId = $diemRenLuyen->lop_id ?: ($sinhVien ? $sinhVien->lop_id : null);
            $isAssigned = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('lop_id', $targetLopId)
                ->where('hoc_ky_id', $diemRenLuyen->hoc_ky_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'Bạn không có quyền xem phiếu điểm của lớp này.');
            }
        }

        // Lấy cấu hình tiêu chí rèn luyện
        $criteria = EvaluationCriteria::getCriteria();

        // Lấy thông tin điểm chi tiết đã lưu
        $existingDetails = ChiTietDiemRenLuyen::where('diem_ren_luyen_id', $diemRenLuyen->id)
            ->get()
            ->keyBy('ma_tieu_chi');

        // Tự động lấy điểm học tập tham chiếu (Tiêu chí I.2)
        $autoGpaScore = 0;
        $gpaNote = '';
        $diemHocTap = DiemHocTap::where('sinh_vien_id', $sinhVien->id)
            ->where('hoc_ky_id', $diemRenLuyen->hoc_ky_id)
            ->first();
        if ($diemHocTap) {
            $gpa = $diemHocTap->diem_he_4;
            $gpa10 = $diemHocTap->diem_he_10;
            if ($gpa >= 3.6 || $gpa10 >= 9.0) {
                $autoGpaScore = 5;
                $gpaNote = "GPA: {$gpa10} (Xuất sắc) -> 5đ";
            } elseif ($gpa >= 3.2 || $gpa10 >= 8.0) {
                $autoGpaScore = 4;
                $gpaNote = "GPA: {$gpa10} (Giỏi) -> 4đ";
            } elseif ($gpa >= 2.5 || $gpa10 >= 7.0) {
                $autoGpaScore = 3;
                $gpaNote = "GPA: {$gpa10} (Khá) -> 3đ";
            } elseif ($gpa >= 2.0 || $gpa10 >= 5.0) {
                $autoGpaScore = 2;
                $gpaNote = "GPA: {$gpa10} (Trung bình) -> 2đ";
            } else {
                $autoGpaScore = 0;
                $gpaNote = "GPA: {$gpa10} -> 0đ";
            }
            
            // Tham chiếu kết quả học kỳ trước xem có cải thiện không (+2đ)
            $prevSemester = HocKy::where('id', '<', $diemRenLuyen->hoc_ky_id)->orderBy('id', 'desc')->first();
            if ($prevSemester) {
                $prevGpa = DiemHocTap::where('sinh_vien_id', $sinhVien->id)
                    ->where('hoc_ky_id', $prevSemester->id)
                    ->first();
                if ($prevGpa && $diemHocTap->diem_he_10 > $prevGpa->diem_he_10) {
                    $autoGpaScore = min(5, $autoGpaScore + 2);
                    $gpaNote .= " | Cải thiện (GPA trước: {$prevGpa->diem_he_10}) -> +2đ (Cận 5đ)";
                }
            }
        } else {
            $gpaNote = "Chưa có điểm GPA.";
        }

        // Lấy danh sách hoạt động đã điểm danh có mặt trong học kỳ
        $dangKyIds = $sinhVien->dangKyHoatDongs()->pluck("id");
        $attendedRegs = \App\Models\DiemDanh::whereIn("dang_ky_hoat_dong_id", $dangKyIds)
            ->where("trang_thai", "co_mat")
            ->pluck("dang_ky_hoat_dong_id");
            
        $activities = HoatDong::with('tieuChi')
            ->whereIn("id", function($query) use ($attendedRegs) {
                $query->select("hoat_dong_id")->from("dang_ky_hoat_dongs")->whereIn("id", $attendedRegs);
            })->get();

        return view('xet_duyet.review_evaluation', compact(
            'sinhVien', 'diemRenLuyen', 'criteria', 'existingDetails', 'autoGpaScore', 'gpaNote', 'activities'
        ));
    }

    /**
     * Xử lý lưu kết quả chấm điểm rà soát (Dành cho Cán sự lớp hoặc Cố vấn học tập).
     * Sử dụng Transaction để bảo vệ tính đồng bộ giữa bảng điểm tổng quan và bảng điểm chi tiết.
     */
    public function saveReviewEvaluation(Request $request, $id)
    {
        $user = Auth::user();
        $diemRenLuyen = DiemRenLuyen::findOrFail($id);

        // 1. Kiểm tra phân quyền cập nhật điểm rà soát
        if ($user->role === 'ban_can_su') {
            $svBCS = SinhVien::where("user_id", $user->id)->first();
            $targetLopId = $diemRenLuyen->lop_id ?: ($diemRenLuyen->sinhVien ? $diemRenLuyen->sinhVien->lop_id : null);
            if (!$svBCS || $svBCS->lop_id !== $targetLopId) {
                abort(403, 'Bạn không có quyền chỉnh sửa phiếu điểm của sinh viên lớp khác.');
            }
        } elseif ($user->role === 'co_van') {
            $targetLopId = $diemRenLuyen->lop_id ?: ($diemRenLuyen->sinhVien ? $diemRenLuyen->sinhVien->lop_id : null);
            $isAssigned = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('lop_id', $targetLopId)
                ->where('hoc_ky_id', $diemRenLuyen->hoc_ky_id)
                ->exists();
            if (!$isAssigned) {
                abort(403, 'Bạn không có quyền chỉnh sửa phiếu điểm của lớp này.');
            }
        }

        // Chặn chỉnh sửa nếu phiếu điểm đã được CTSV khóa
        if ($diemRenLuyen->trang_thai_duyet === 'da_khoa' && $user->role !== 'admin') {
            return redirect()->route('xet_duyet.index')->with('warning', 'Phiếu điểm này đã khóa và chỉ Admin mới có quyền điều chỉnh.');
        }

        $criteria = EvaluationCriteria::getCriteria();
        $scores = $request->input('diem_review', []);
        $notes = $request->input('ghi_chu_review', []);

        // Chạy quy trình cập nhật an toàn bằng Database Transaction
        DB::transaction(function () use ($user, $diemRenLuyen, $criteria, $scores, $notes) {
            $sectionScores = [];
            $totalSum = 0;

            foreach ($criteria as $sectionKey => $section) {
                $sectionScores[$sectionKey] = 0;
                foreach ($section['items'] as $itemKey => $item) {
                    $score = floatval($scores[$itemKey] ?? 0);
                    if ($score < 0) $score = 0;
                    if ($score > $item['max_score']) $score = $item['max_score'];

                    $note = $notes[$itemKey] ?? null;

                    // Lưu hoặc cập nhật điểm chi tiết của tiêu chí rèn luyện
                    $detail = ChiTietDiemRenLuyen::firstOrCreate(
                        [
                            'diem_ren_luyen_id' => $diemRenLuyen->id,
                            'ma_tieu_chi' => $itemKey
                        ],
                        [
                            'diem_sv' => 0,
                            'diem_bcs' => 0,
                            'diem_cvht' => 0
                        ]
                    );

                    if ($user->role === 'ban_can_su') {
                        $detail->diem_bcs = $score;
                        $detail->ghi_chu_bcs = $note;
                        $detail->diem_cvht = $score; // Mặc định gán tạm cho CVHT để tham khảo
                    } else {
                        $detail->diem_cvht = $score;
                        $detail->ghi_chu_cvht = $note;
                    }
                    $detail->save();

                    $sectionScores[$sectionKey] += $score;
                }

                // Giới hạn điểm của từng phần không vượt quá điểm tối đa
                if ($sectionScores[$sectionKey] > $section['max_score']) {
                    $sectionScores[$sectionKey] = $section['max_score'];
                }

                $totalSum += $sectionScores[$sectionKey];
            }

            $finalTotal = min(100, $totalSum);

            // Xếp loại rèn luyện
            $xepLoai = 'Yếu';
            if ($finalTotal >= 90) {
                $xepLoai = 'Xuất sắc';
            } elseif ($finalTotal >= 80) {
                $xepLoai = 'Tốt';
            } elseif ($finalTotal >= 65) {
                $xepLoai = 'Khá';
            } elseif ($finalTotal >= 50) {
                $xepLoai = 'Trung bình';
            }

            $gpaScore = floatval($scores['I.2'] ?? 0);
            
            // Cập nhật kết quả tổng quan
            $diemRenLuyen->diem_hoc_tap_quy_doi = $gpaScore;
            $diemRenLuyen->diem_tong_hop = $finalTotal;
            $diemRenLuyen->tong_diem_tieu_chi = max(0, $finalTotal - $gpaScore);
            $diemRenLuyen->xep_loai = $xepLoai;
            $diemRenLuyen->save();

            // Ghi nhận lịch sử thay đổi vào AuditLog
            AuditLog::create([
                "user_id" => Auth::id(),
                "action" => "Chấm điểm chi tiết rèn luyện",
                "target_table" => "diem_ren_luyens",
                "old_data" => json_encode(["total_score" => $diemRenLuyen->getOriginal('diem_tong_hop')]),
                "new_data" => json_encode(["total_score" => $finalTotal])
            ]);
        });

        return redirect()->route('xet_duyet.index')
            ->with('success', 'Đã lưu điểm rèn luyện chi tiết cho sinh viên ' . $diemRenLuyen->sinhVien->ho_ten);
    }

    /**
     * Hiển thị trang cấu hình Phân công Cố vấn học tập (Dành cho Admin).
     */
    public function showPhanCong()
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $advisors = \App\Models\User::whereIn('role', ['co_van', 'admin'])->get();
        $classes = \App\Models\Lop::with('nganh.khoa')->get();
        $semesters = \App\Models\HocKy::orderBy('id', 'desc')->get();
        $assignments = \App\Models\PhanCongCoVan::with(['user', 'lop', 'hocKy'])->get();

        return view('xet_duyet.phan_cong', compact('advisors', 'classes', 'semesters', 'assignments'));
    }

    /**
     * Lưu thông tin phân công Cố vấn học tập quản lý lớp học trong học kỳ.
     */
    public function savePhanCong(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'lop_id' => 'required|exists:lops,id',
            'hoc_ky_id' => 'required|exists:hoc_kys,id',
        ], [
            'user_id.required' => 'Vui lòng chọn Cố vấn học tập.',
            'user_id.exists' => 'Cố vấn học tập không hợp lệ.',
            'lop_id.required' => 'Vui lòng chọn lớp học.',
            'lop_id.exists' => 'Lớp học không hợp lệ.',
            'hoc_ky_id.required' => 'Vui lòng chọn học kỳ.',
            'hoc_ky_id.exists' => 'Học kỳ không hợp lệ.',
        ]);

        \App\Models\PhanCongCoVan::updateOrCreate(
            [
                'lop_id' => $request->lop_id,
                'hoc_ky_id' => $request->hoc_ky_id
            ],
            [
                'user_id' => $request->user_id
            ]
        );

        return redirect()->route('xet_duyet.phan_cong')->with('success', 'Phân công Cố vấn học tập thành công.');
    }

    /**
     * Xóa thông tin phân công Cố vấn học tập.
     */
    public function deletePhanCong($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $assignment = \App\Models\PhanCongCoVan::findOrFail($id);
        $assignment->delete();

        return redirect()->route('xet_duyet.phan_cong')->with('success', 'Đã xóa phân công Cố vấn học tập.');
    }

    /**
     * Phê duyệt hàng loạt các phiếu điểm rèn luyện của sinh viên (Dành cho cán sự/Cố vấn/Admin).
     * Sử dụng Transaction để cập nhật an toàn và đồng bộ Audit Log cho toàn bộ danh sách.
     */
    public function bulkApprove(Request $request)
    {
        $user = Auth::user();
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->back()->with('warning', 'Không có sinh viên nào được chọn.');
        }

        $records = DiemRenLuyen::whereIn('id', $ids)->get();
        $updatedCount = 0;

        // Thực hiện cập nhật danh sách an toàn trong Database Transaction
        DB::transaction(function () use ($user, $records, &$updatedCount) {
            foreach ($records as $record) {
                $currentStatus = $record->trang_thai_duyet;
                $nextStatus = null;

                // 1. Phân quyền và cập nhật đối với Ban cán sự lớp
                if ($user->role === 'ban_can_su') {
                    $svBCS = SinhVien::where("user_id", $user->id)->first();
                    $targetLopId = $record->lop_id ?: ($record->sinhVien ? $record->sinhVien->lop_id : null);
                    if (!$svBCS || $svBCS->lop_id !== $targetLopId) {
                        continue;
                    }
                    if ($currentStatus === 'cho_bcs_duyet' || $currentStatus === 'tam_tinh') {
                        $nextStatus = 'cho_cvht_duyet';
                    }
                } 
                // 2. Phân quyền và cập nhật đối với Cố vấn học tập
                elseif ($user->role === 'co_van') {
                    $targetLopId = $record->lop_id ?: ($record->sinhVien ? $record->sinhVien->lop_id : null);
                    $isAssigned = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                        ->where('lop_id', $targetLopId)
                        ->where('hoc_ky_id', $record->hoc_ky_id)
                        ->exists();
                    if (!$isAssigned) {
                        continue;
                    }
                    if ($currentStatus === 'cho_cvht_duyet') {
                        $nextStatus = 'cho_ctsv_duyet';
                    }
                } 
                // 3. Phân quyền và cập nhật đối với Admin (Phòng CTSV)
                elseif ($user->role === 'admin') {
                    if ($currentStatus === 'cho_ctsv_duyet') {
                        $nextStatus = 'da_khoa';
                    } elseif ($currentStatus === 'cho_cvht_duyet') {
                        $nextStatus = 'cho_ctsv_duyet';
                    } elseif ($currentStatus === 'cho_bcs_duyet') {
                        $nextStatus = 'cho_cvht_duyet';
                    } elseif ($currentStatus === 'tam_tinh') {
                        $nextStatus = 'cho_bcs_duyet';
                    }
                }

                if ($nextStatus) {
                    $record->trang_thai_duyet = $nextStatus;
                    $record->save();

                    AuditLog::create([
                        "user_id" => Auth::id(),
                        "action" => "Duyệt hàng loạt điểm rèn luyện",
                        "target_table" => "diem_ren_luyens",
                        "old_data" => json_encode(["id" => $record->id, "status" => $currentStatus]),
                        "new_data" => json_encode(["id" => $record->id, "status" => $nextStatus])
                    ]);
                    $updatedCount++;
                }
            }
        });

        return redirect()->back()->with('success', "Đã phê duyệt hàng loạt thành công cho {$updatedCount} sinh viên.");
    }

    /**
     * Mở khóa phiếu điểm rèn luyện đã khóa để cho phép chấm lại điểm từ đầu (Dành cho Admin).
     */
    public function unlockEvaluation($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $record = DiemRenLuyen::findOrFail($id);
        $oldStatus = $record->trang_thai_duyet;
        
        DB::transaction(function () use ($record, $oldStatus) {
            $record->trang_thai_duyet = 'tam_tinh';
            $record->save();

            AuditLog::create([
                "user_id" => Auth::id(),
                "action" => "Mở khóa phiếu điểm rèn luyện",
                "target_table" => "diem_ren_luyens",
                "old_data" => json_encode(["id" => $record->id, "status" => $oldStatus]),
                "new_data" => json_encode(["id" => $record->id, "status" => 'tam_tinh'])
            ]);
        });

        return redirect()->back()->with('success', "Đã mở khóa phiếu điểm rèn luyện của sinh viên " . $record->sinhVien->ho_ten);
    }
}