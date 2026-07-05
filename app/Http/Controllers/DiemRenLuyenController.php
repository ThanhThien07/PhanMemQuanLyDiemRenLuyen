<?php
namespace App\Http\Controllers;

use App\Models\DiemRenLuyen;
use App\Models\SinhVien;
use App\Models\HocKy;
use App\Models\HoatDong;
use App\Models\HoSoMinhChung;
use App\Models\Lop;
use App\Models\Khoa;
use App\Models\Nganh;
use App\Models\HeDaoTao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\EvaluationCriteria;
use App\Models\ChiTietDiemRenLuyen;
use App\Models\DiemHocTap;

class DiemRenLuyenController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $hocKys = HocKy::all();
        $selectedHocKy = $request->hoc_ky_id ?? (HocKy::where("trang_thai", "dang_dien_ra")->first()->id ?? HocKy::first()->id ?? null);

        if ($user->role === "sinh_vien" || $user->role === "ban_can_su") {
            $sinhVien = SinhVien::where("user_id", $user->id)->first();
            if (!$sinhVien) {
                return view("diem_ren_luyen.index", ["no_profile" => true]);
            }

            $diemRenLuyen = DiemRenLuyen::where("sinh_vien_id", $sinhVien->id)
                ->where("hoc_ky_id", $selectedHocKy)
                ->first();

            // Activities details
            $dangKyIds = $sinhVien->dangKyHoatDongs()->pluck("id");
            $attendedRegs = \App\Models\DiemDanh::whereIn("dang_ky_hoat_dong_id", $dangKyIds)
                ->where("trang_thai", "co_mat")
                ->pluck("dang_ky_hoat_dong_id");
                
            $activities = HoatDong::whereIn("id", function($query) use ($attendedRegs) {
                $query->select("hoat_dong_id")->from("dang_ky_hoat_dongs")->whereIn("id", $attendedRegs);
            })->get();

            $evidences = HoSoMinhChung::with(["minhChung", "tieuChi"])
                ->where("sinh_vien_id", $sinhVien->id)
                ->where("trang_thai_duyet", "da_duyet")
                ->get();

            $disciplines = \App\Models\KyLuat::with(["tieuChi"])
                ->where("sinh_vien_id", $sinhVien->id)
                ->get();

            return view("diem_ren_luyen.index", compact("diemRenLuyen", "sinhVien", "activities", "evidences", "disciplines", "hocKys", "selectedHocKy"));
        }

        // Admins/Advisors view transcripts of everyone
        $query = DiemRenLuyen::with(["sinhVien.lop", "hocKy"]);
        if ($selectedHocKy) {
            $query->where("hoc_ky_id", $selectedHocKy);
        }
        
        if ($user->role === 'co_van') {
            $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('hoc_ky_id', $selectedHocKy)
                ->pluck('lop_id');
            $query->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                $q->whereIn('lop_id', $assignedLopIds);
            });
        }

        $diemRenLuyens = $query->get();

        return view("diem_ren_luyen.admin", compact("diemRenLuyens", "hocKys", "selectedHocKy"));
    }

    public function reportIndex(Request $request)
    {
        $hocKys = HocKy::all();
        $lops = Lop::all();
        $khoas = Khoa::all();
        $nganhs = Nganh::all();
        $heDaoTaos = HeDaoTao::all();

        $selectedHocKy = $request->hoc_ky_id ?? (HocKy::where("trang_thai", "dang_dien_ra")->first()->id ?? HocKy::first()->id ?? null);
        $reportType = $request->report_type ?? 'all';

        $user = Auth::user();
        $query = DiemRenLuyen::with(['sinhVien.lop.nganh.khoa', 'sinhVien.lop.nganh', 'sinhVien.heDaoTao', 'hocKy'])
            ->where('hoc_ky_id', $selectedHocKy);

        if ($user->role === 'co_van') {
            $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('hoc_ky_id', $selectedHocKy)
                ->pluck('lop_id');
            $query->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                $q->whereIn('lop_id', $assignedLopIds);
            });
        }

        // Apply filters
        if ($request->filled('mssv')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('ma_sv', 'like', '%' . $request->mssv . '%');
            });
        }
        if ($request->filled('ho_ten')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('ho_ten', 'like', '%' . $request->ho_ten . '%');
            });
        }
        if ($request->filled('lop_id')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('lop_id', $request->lop_id);
            });
        }
        if ($request->filled('khoa_id')) {
            $query->whereHas('sinhVien.lop.nganh', function($q) use ($request) {
                $q->where('khoa_id', $request->khoa_id);
            });
        }
        if ($request->filled('nganh_id')) {
            $query->whereHas('sinhVien.lop', function($q) use ($request) {
                $q->where('nganh_id', $request->nganh_id);
            });
        }
        if ($request->filled('khoa_hoc')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('khoa_hoc', $request->khoa_hoc);
            });
        }
        if ($request->filled('he_dao_tao_id')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('he_dao_tao_id', $request->he_dao_tao_id);
            });
        }
        if ($request->filled('trang_thai_duyet')) {
            $query->where('trang_thai_duyet', $request->trang_thai_duyet);
        }
        if ($request->filled('xep_loai')) {
            $query->where('xep_loai', $request->xep_loai);
        }

        // Apply pre-defined report types
        if ($reportType === 'insufficient') {
            $query->where(function($q) {
                $q->where('diem_tong_hop', '<', 50)
                  ->orWhereIn('sinh_vien_id', function($sub) {
                      $sub->select('sinh_vien_id')
                          ->from('ho_so_minh_chungs')
                          ->where('trang_thai_duyet', 'tu_choi');
                  });
            });
        } elseif ($reportType === 'scholarship') {
            $query->where('diem_tong_hop', '>=', 80)
                  ->whereNotIn('sinh_vien_id', function($sub) {
                      $sub->select('sinh_vien_id')->from('ky_luats');
                  });
        }

        $records = $query->get();

        // Calculate summaries
        $summary = [
            'total' => $records->count(),
            'avg_score' => $records->avg('diem_tong_hop') ?: 0,
            'xuat_sac' => $records->where('xep_loai', 'Xuất sắc')->count(),
            'tot' => $records->where('xep_loai', 'Tốt')->count(),
            'kha' => $records->where('xep_loai', 'Khá')->count(),
            'trung_binh' => $records->where('xep_loai', 'Trung bình')->count(),
            'yeu' => $records->where('xep_loai', 'Yếu')->count(),
        ];

        // Class stage summary dashboard
        $classStages = [];
        if ($reportType === 'stage_summary') {
            $classes = Lop::with('coVan')->get();
            foreach ($classes as $cl) {
                $students = SinhVien::where('lop_id', $cl->id)->pluck('id');
                $transcripts = DiemRenLuyen::whereIn('sinh_vien_id', $students)
                    ->where('hoc_ky_id', $selectedHocKy)
                    ->get();
                
                $total = $students->count();
                $tamTinh = $transcripts->where('trang_thai_duyet', 'tam_tinh')->count();
                $choCvht = $transcripts->where('trang_thai_duyet', 'cho_cvht_duyet')->count();
                $choCtsv = $transcripts->where('trang_thai_duyet', 'cho_ctsv_duyet')->count();
                $daKhoa = $transcripts->where('trang_thai_duyet', 'da_khoa')->count();

                $classStages[] = [
                    'class_name' => $cl->ten_lop,
                    'advisor_name' => $cl->coVan ? $cl->coVan->name : 'N/A',
                    'total_students' => $total,
                    'tam_tinh' => $tamTinh,
                    'cho_cvht' => $choCvht,
                    'cho_ctsv' => $choCtsv,
                    'da_khoa' => $daKhoa,
                ];
            }
        }

        return view('diem_ren_luyen.reports', compact(
            'records', 'hocKys', 'selectedHocKy', 'lops', 'khoas', 'nganhs', 'heDaoTaos', 'summary', 'reportType', 'classStages'
        ));
    }

    public function exportCsv(Request $request)
    {
        $selectedHocKy = $request->hoc_ky_id ?? (HocKy::where("trang_thai", "dang_dien_ra")->first()->id ?? HocKy::first()->id ?? null);
        $reportType = $request->report_type ?? 'all';

        $user = Auth::user();
        $query = DiemRenLuyen::with(['sinhVien.lop.nganh.khoa', 'sinhVien.lop.nganh', 'sinhVien.heDaoTao', 'hocKy'])
            ->where('hoc_ky_id', $selectedHocKy);

        if ($user->role === 'co_van') {
            $assignedLopIds = \App\Models\PhanCongCoVan::where('user_id', $user->id)
                ->where('hoc_ky_id', $selectedHocKy)
                ->pluck('lop_id');
            $query->whereHas('sinhVien', function($q) use ($assignedLopIds) {
                $q->whereIn('lop_id', $assignedLopIds);
            });
        }

        // Apply same filters
        if ($request->filled('mssv')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('ma_sv', 'like', '%' . $request->mssv . '%');
            });
        }
        if ($request->filled('ho_ten')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('ho_ten', 'like', '%' . $request->ho_ten . '%');
            });
        }
        if ($request->filled('lop_id')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('lop_id', $request->lop_id);
            });
        }
        if ($request->filled('khoa_id')) {
            $query->whereHas('sinhVien.lop', function($q) use ($request) {
                $q->where('khoa_id', $request->khoa_id);
            });
        }
        if ($request->filled('nganh_id')) {
            $query->whereHas('sinhVien.lop', function($q) use ($request) {
                $q->where('nganh_id', $request->nganh_id);
            });
        }
        if ($request->filled('khoa_hoc')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('khoa_hoc', $request->khoa_hoc);
            });
        }
        if ($request->filled('he_dao_tao_id')) {
            $query->whereHas('sinhVien', function($q) use ($request) {
                $q->where('he_dao_tao_id', $request->he_dao_tao_id);
            });
        }
        if ($request->filled('trang_thai_duyet')) {
            $query->where('trang_thai_duyet', $request->trang_thai_duyet);
        }
        if ($request->filled('xep_loai')) {
            $query->where('xep_loai', $request->xep_loai);
        }

        if ($reportType === 'insufficient') {
            $query->where(function($q) {
                $q->where('diem_tong_hop', '<', 50)
                  ->orWhereIn('sinh_vien_id', function($sub) {
                      $sub->select('sinh_vien_id')
                          ->from('ho_so_minh_chungs')
                          ->where('trang_thai_duyet', 'tu_choi');
                  });
            });
        } elseif ($reportType === 'scholarship') {
            $query->where('diem_tong_hop', '>=', 80)
                  ->whereNotIn('sinh_vien_id', function($sub) {
                      $sub->select('sinh_vien_id')->from('ky_luats');
                  });
        }

        $records = $query->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=bao_cao_diem_ren_luyen.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM
            fputs($file, "\xEF\xBB\xBF");
            
            // CSV Header
            fputcsv($file, ['MSSV', 'Họ tên', 'Lớp', 'Khoa', 'Học kỳ', 'Điểm rèn luyện', 'Điểm học tập', 'Tổng điểm', 'Xếp loại', 'Trạng thái']);

            foreach ($records as $r) {
                fputcsv($file, [
                    $r->sinhVien->ma_sv,
                    $r->sinhVien->ho_ten,
                    $r->sinhVien->lop->ten_lop,
                    $r->sinhVien->lop->khoa->ten_khoa ?? 'N/A',
                    $r->hocKy->ten_hoc_ky,
                    $r->tong_diem_tieu_chi,
                    $r->diem_hoc_tap_quy_doi,
                    $r->diem_tong_hop,
                    $r->xep_loai,
                    $r->trang_thai_duyet
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function showSelfEvaluation(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where("user_id", $user->id)->first();
        if (!$sinhVien) {
            return redirect()->route('diem_ren_luyen.index')->with('warning', 'Hồ sơ sinh viên chưa được khởi tạo.');
        }

        $selectedHocKy = $request->hoc_ky_id ?? (HocKy::where("trang_thai", "dang_dien_ra")->first()->id ?? HocKy::first()->id ?? null);
        $hocKys = HocKy::all();
        $hocKy = HocKy::find($selectedHocKy);

        // Find or create DiemRenLuyen
        $diemRenLuyen = DiemRenLuyen::firstOrCreate(
            ['sinh_vien_id' => $sinhVien->id, 'hoc_ky_id' => $selectedHocKy],
            [
                'lop_id' => $sinhVien->lop_id,
                'tong_diem_tieu_chi' => 0,
                'diem_hoc_tap_quy_doi' => 0,
                'diem_tong_hop' => 0,
                'xep_loai' => 'Yếu',
                'trang_thai_duyet' => 'tam_tinh'
            ]
        );

        if (!$diemRenLuyen->lop_id) {
            $diemRenLuyen->lop_id = $sinhVien->lop_id;
            $diemRenLuyen->save();
        }

        if ($diemRenLuyen->trang_thai_duyet === 'da_khoa') {
            return redirect()->route('diem_ren_luyen.index', ['hoc_ky_id' => $selectedHocKy])
                ->with('warning', 'Phiếu điểm rèn luyện của học kỳ này đã được khóa. Bạn không thể chỉnh sửa.');
        }

        // Fetch criteria config
        $criteria = EvaluationCriteria::getCriteria();

        // Auto-calculate GPA point (I.2)
        $autoGpaScore = 0;
        $gpaNote = '';
        $diemHocTap = DiemHocTap::where('sinh_vien_id', $sinhVien->id)
            ->where('hoc_ky_id', $selectedHocKy)
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
            
            $prevSemester = HocKy::where('id', '<', $selectedHocKy)->orderBy('id', 'desc')->first();
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

        // Fetch existing details
        $existingDetails = ChiTietDiemRenLuyen::where('diem_ren_luyen_id', $diemRenLuyen->id)
            ->get()
            ->keyBy('ma_tieu_chi');

        // Fetch phase configuration
        $dotDuyet = \App\Models\CauHinhDotDuyet::where('hoc_ky_id', $selectedHocKy)->first();

        return view('diem_ren_luyen.self_evaluation', compact(
            'sinhVien', 'diemRenLuyen', 'criteria', 'existingDetails', 'autoGpaScore', 'gpaNote', 'activities', 'hocKys', 'selectedHocKy', 'hocKy', 'dotDuyet'
        ));
    }

    public function saveSelfEvaluation(Request $request)
    {
        $user = Auth::user();
        $sinhVien = SinhVien::where("user_id", $user->id)->first();
        if (!$sinhVien) {
            return redirect()->route('diem_ren_luyen.index')->with('warning', 'Hồ sơ sinh viên chưa được khởi tạo.');
        }

        $selectedHocKy = $request->hoc_ky_id;
        $diemRenLuyen = DiemRenLuyen::where('sinh_vien_id', $sinhVien->id)
            ->where('hoc_ky_id', $selectedHocKy)
            ->first();

        if (!$diemRenLuyen || $diemRenLuyen->trang_thai_duyet === 'da_khoa') {
            return redirect()->route('diem_ren_luyen.index', ['hoc_ky_id' => $selectedHocKy])
                ->with('warning', 'Hành động không hợp lệ hoặc phiếu điểm đã bị khóa.');
        }

        $criteria = EvaluationCriteria::getCriteria();
        $scores = $request->input('diem_sv', []);
        $notes = $request->input('ghi_chu_sv', []);

        $sectionScores = [];
        $totalSum = 0;

        foreach ($criteria as $sectionKey => $section) {
            $sectionScores[$sectionKey] = 0;
            foreach ($section['items'] as $itemKey => $item) {
                $score = floatval($scores[$itemKey] ?? 0);
                if ($score < 0) $score = 0;
                if ($score > $item['max_score']) $score = $item['max_score'];

                $note = $notes[$itemKey] ?? null;

                ChiTietDiemRenLuyen::updateOrCreate(
                    [
                        'diem_ren_luyen_id' => $diemRenLuyen->id,
                        'ma_tieu_chi' => $itemKey
                    ],
                    [
                        'diem_sv' => $score,
                        'ghi_chu_sv' => $note,
                        'diem_bcs' => $score,
                        'diem_cvht' => $score
                    ]
                );

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
        $diemRenLuyen->lop_id = $sinhVien->lop_id; // Capture current class for historical logging
        $diemRenLuyen->diem_hoc_tap_quy_doi = $gpaScore;
        $diemRenLuyen->diem_tong_hop = $finalTotal;
        $diemRenLuyen->tong_diem_tieu_chi = max(0, $finalTotal - $gpaScore);
        $diemRenLuyen->xep_loai = $xepLoai;
        $diemRenLuyen->minh_chung_gdrive = $request->input('minh_chung_gdrive');
        $diemRenLuyen->trang_thai_duyet = 'cho_bcs_duyet';
        $diemRenLuyen->save();

        return redirect()->route('diem_ren_luyen.index', ['hoc_ky_id' => $selectedHocKy])
            ->with('success', 'Nộp phiếu tự đánh giá rèn luyện thành công!');
    }
}