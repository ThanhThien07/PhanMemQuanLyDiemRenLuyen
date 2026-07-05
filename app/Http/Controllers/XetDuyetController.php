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

class XetDuyetController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $hocKy = HocKy::where("trang_thai", "dang_dien_ra")->first() ?: HocKy::first();
        
        if (!$hocKy) {
            return view("xet_duyet.index", ["error" => "Chưa cấu hình học kỳ nào."]);
        }

        $query = DiemRenLuyen::with(["sinhVien.lop", "lop", "hocKy"]);

        // BCS Lớp only views their own class
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
        } elseif ($user->role === "co_van") {
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

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $drl = DiemRenLuyen::findOrFail($id);
        $oldState = $drl->trang_thai_duyet;
        $targetState = $request->trang_thai;

        // Perform authorization based on role
        if ($user->role === 'ban_can_su') {
            // BCS can only update students of their own class
            $svBCS = SinhVien::where("user_id", $user->id)->first();
            $student = SinhVien::findOrFail($drl->sinh_vien_id);
            if (!$svBCS || $svBCS->lop_id !== $student->lop_id) {
                return response()->json(["success" => false, "message" => "Bạn không có quyền duyệt sinh viên lớp khác."], 403);
            }
            
            // BCS can only transition from tam_tinh to cho_cvht_duyet
            if (!in_array($oldState, ['tam_tinh', 'cho_bcs_duyet']) || $targetState !== 'cho_cvht_duyet') {
                return response()->json(["success" => false, "message" => "Trạng thái chuyển tiếp không hợp lệ cho Ban cán sự."], 400);
            }
        } elseif ($user->role === 'co_van') {
            $targetLopId = $drl->lop_id ?: ($drl->sinhVien ? $drl->sinhVien->lop_id : null);
            $isAssigned = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('lop_id', $targetLopId)
                ->where('hoc_ky_id', $drl->hoc_ky_id)
                ->exists();
            if (!$isAssigned) {
                return response()->json(["success" => false, "message" => "Bạn không có quyền duyệt sinh viên lớp khác."], 403);
            }

            // CVHT can transition from cho_cvht_duyet to cho_ctsv_duyet OR reject back to tam_tinh
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
        } elseif ($user->role === 'admin') {
            // CTSV can transition from cho_ctsv_duyet to da_khoa OR reject back to cho_cvht_duyet
            if ($targetState === 'da_khoa') {
                if ($oldState !== 'cho_ctsv_duyet') {
                    return response()->json(["success" => false, "message" => "Chỉ có thể khóa khi trạng thái là Chờ CTSV phê duyệt."], 400);
                }
            } elseif ($targetState === 'cho_cvht_duyet') {
                if ($oldState !== 'cho_ctsv_duyet') {
                    return response()->json(["success" => false, "message" => "Chỉ có thể trả về khi trạng thái là Chờ CTSV phê duyệt."], 400);
                }
            } else {
                // CTSV/Admin can set any status if they want, but let's enforce clean bounds
                if (!in_array($targetState, ['tam_tinh', 'cho_cvht_duyet', 'cho_ctsv_duyet', 'da_khoa'])) {
                    return response()->json(["success" => false, "message" => "Trạng thái không hợp lệ."], 400);
                }
            }
        } else {
            return response()->json(["success" => false, "message" => "Vai trò không hợp lệ để duyệt."], 403);
        }

        $drl->trang_thai_duyet = $targetState;
        $drl->save();

        AuditLog::create([
            "user_id" => Auth::id(),
            "action" => "Duyệt học kỳ điểm rèn luyện",
            "target_table" => "diem_ren_luyens",
            "old_data" => json_encode(["status" => $oldState]),
            "new_data" => json_encode(["status" => $targetState])
        ]);

        return response()->json(["success" => true]);
    }

    public function showReviewEvaluation($id)
    {
        $user = Auth::user();
        $diemRenLuyen = DiemRenLuyen::with(['sinhVien.lop.khoa', 'sinhVien.heDaoTao', 'hocKy'])->findOrFail($id);
        $sinhVien = $diemRenLuyen->sinhVien;

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

        // Fetch criteria config
        $criteria = EvaluationCriteria::getCriteria();

        // Fetch existing details
        $existingDetails = ChiTietDiemRenLuyen::where('diem_ren_luyen_id', $diemRenLuyen->id)
            ->get()
            ->keyBy('ma_tieu_chi');

        // GPA reference
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

        // Fetch attended activities
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

    public function saveReviewEvaluation(Request $request, $id)
    {
        $user = Auth::user();
        $diemRenLuyen = DiemRenLuyen::findOrFail($id);

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

        if ($diemRenLuyen->trang_thai_duyet === 'da_khoa' && $user->role !== 'admin') {
            return redirect()->route('xet_duyet.index')->with('warning', 'Phiếu điểm này đã khóa và chỉ Admin mới có quyền điều chỉnh.');
        }

        $criteria = EvaluationCriteria::getCriteria();
        $scores = $request->input('diem_review', []);
        $notes = $request->input('ghi_chu_review', []);

        $sectionScores = [];
        $totalSum = 0;

        foreach ($criteria as $sectionKey => $section) {
            $sectionScores[$sectionKey] = 0;
            foreach ($section['items'] as $itemKey => $item) {
                $score = floatval($scores[$itemKey] ?? 0);
                if ($score < 0) $score = 0;
                if ($score > $item['max_score']) $score = $item['max_score'];

                $note = $notes[$itemKey] ?? null;

                // Load existing row
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
                    $detail->diem_cvht = $score;
                } else {
                    $detail->diem_cvht = $score;
                    $detail->ghi_chu_cvht = $note;
                }
                $detail->save();

                $sectionScores[$sectionKey] += $score;
            }

            if ($sectionScores[$sectionKey] > $section['max_score']) {
                $sectionScores[$sectionKey] = $section['max_score'];
            }

            $totalSum += $sectionScores[$sectionKey];
        }

        $finalTotal = min(100, $totalSum);

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
        $diemRenLuyen->diem_hoc_tap_quy_doi = $gpaScore;
        $diemRenLuyen->diem_tong_hop = $finalTotal;
        $diemRenLuyen->tong_diem_tieu_chi = max(0, $finalTotal - $gpaScore);
        $diemRenLuyen->xep_loai = $xepLoai;
        $diemRenLuyen->save();

        AuditLog::create([
            "user_id" => Auth::id(),
            "action" => "Chấm điểm chi tiết rèn luyện",
            "target_table" => "diem_ren_luyens",
            "old_data" => json_encode(["total_score" => $diemRenLuyen->getOriginal('diem_tong_hop')]),
            "new_data" => json_encode(["total_score" => $finalTotal])
        ]);

        return redirect()->route('xet_duyet.index')
            ->with('success', 'Đã lưu điểm rèn luyện chi tiết cho sinh viên ' . $diemRenLuyen->sinhVien->ho_ten);
    }

    public function showPhanCong()
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        // Fetch all teachers/advisors (users with role co_van or admin)
        $advisors = \App\Models\User::whereIn('role', ['co_van', 'admin'])->get();
        $classes = \App\Models\Lop::with('nganh.khoa')->get();
        $semesters = \App\Models\HocKy::orderBy('id', 'desc')->get();
        $assignments = \App\Models\PhanCongCoVan::with(['user', 'lop', 'hocKy'])->get();

        return view('xet_duyet.phan_cong', compact('advisors', 'classes', 'semesters', 'assignments'));
    }

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
        ]);

        // Upsert assignment (a class has 1 advisor per semester)
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

    public function bulkApprove(Request $request)
    {
        $user = Auth::user();
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->back()->with('warning', 'Không có sinh viên nào được chọn.');
        }

        $records = DiemRenLuyen::whereIn('id', $ids)->get();
        $updatedCount = 0;

        foreach ($records as $record) {
            $currentStatus = $record->trang_thai_duyet;
            $nextStatus = null;

            if ($user->role === 'ban_can_su') {
                $svBCS = SinhVien::where("user_id", $user->id)->first();
                $targetLopId = $record->lop_id ?: ($record->sinhVien ? $record->sinhVien->lop_id : null);
                if (!$svBCS || $svBCS->lop_id !== $targetLopId) {
                    continue; // Unauthorized skip
                }
                if ($currentStatus === 'cho_bcs_duyet') {
                    $nextStatus = 'cho_cvht_duyet';
                }
            } elseif ($user->role === 'co_van') {
                $targetLopId = $record->lop_id ?: ($record->sinhVien ? $record->sinhVien->lop_id : null);
                $isAssigned = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                    ->where('lop_id', $targetLopId)
                    ->where('hoc_ky_id', $record->hoc_ky_id)
                    ->exists();
                if (!$isAssigned) {
                    continue; // Unauthorized skip
                }
                if ($currentStatus === 'cho_cvht_duyet') {
                    $nextStatus = 'cho_ctsv_duyet';
                }
            } elseif ($user->role === 'admin') {
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

        return redirect()->back()->with('success', "Đã phê duyệt hàng loạt thành công cho {$updatedCount} sinh viên.");
    }

    public function unlockEvaluation($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $record = DiemRenLuyen::findOrFail($id);
        $oldStatus = $record->trang_thai_duyet;
        
        $record->trang_thai_duyet = 'tam_tinh';
        $record->save();

        AuditLog::create([
            "user_id" => Auth::id(),
            "action" => "Mở khóa phiếu điểm rèn luyện",
            "target_table" => "diem_ren_luyens",
            "old_data" => json_encode(["id" => $record->id, "status" => $oldStatus]),
            "new_data" => json_encode(["id" => $record->id, "status" => 'tam_tinh'])
        ]);

        return redirect()->back()->with('success', "Đã mở khóa phiếu điểm rèn luyện của sinh viên " . $record->sinhVien->ho_ten);
    }
}