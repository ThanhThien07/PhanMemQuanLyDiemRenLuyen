<?php

namespace App\Http\Controllers;

use App\Models\HocKy;
use App\Models\DiemRenLuyen;
use App\Services\DiemRenLuyenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller cấu hình và quản lý học kỳ.
 * Dành riêng cho quản trị viên (Admin/CTSV) cấu hình tỉ lệ điểm và trạng thái học kỳ.
 */
class HocKyController extends Controller
{
    /**
     * Hiển thị danh sách và cài đặt học kỳ.
     */
    public function settings()
    {
        $hocKys = HocKy::all();
        return view('hoc_ky.settings', compact('hocKys'));
    }

    /**
     * Cập nhật trạng thái hoặc tỉ lệ điểm của học kỳ.
     * Khi tỉ lệ thay đổi, tự động tính toán lại điểm rèn luyện của toàn bộ sinh viên trong học kỳ đó.
     */
    public function updateSettings(Request $request)
    {
        // Kiểm tra tính hợp lệ của dữ liệu cấu hình
        $request->validate([
            'hoc_ky_id' => 'required|exists:hoc_kys,id',
            'trang_thai' => 'required|in:dang_dien_ra,da_khoa',
            'ti_le_ren_luyen' => 'required|numeric|min:0|max:100',
            'ti_le_hoc_tap' => 'required|numeric|min:0|max:100',
        ], [
            'hoc_ky_id.required' => 'Vui lòng chọn học kỳ.',
            'trang_thai.required' => 'Vui lòng chọn trạng thái.',
            'ti_le_ren_luyen.required' => 'Vui lòng điền tỷ lệ điểm rèn luyện.',
            'ti_le_hoc_tap.required' => 'Vui lòng điền tỷ lệ điểm học tập.',
        ]);

        // Đảm bảo tổng tỉ lệ điểm học tập + rèn luyện phải bằng 100%
        if (floatval($request->ti_le_ren_luyen) + floatval($request->ti_le_hoc_tap) != 100.0) {
            return back()->with('warning', 'Tổng hai tỷ lệ điểm rèn luyện và học tập phải bằng 100%.')->withInput();
        }

        $hk = HocKy::findOrFail($request->hoc_ky_id);
        
        // Nếu học kỳ được đặt là 'dang_dien_ra', khóa tất cả các học kỳ khác
        if ($request->trang_thai === 'dang_dien_ra') {
            HocKy::where('id', '!=', $hk->id)->update(['trang_thai' => 'da_khoa']);
        }

        $hk->update([
            'trang_thai' => $request->trang_thai,
            'ti_le_ren_luyen' => $request->ti_le_ren_luyen,
            'ti_le_hoc_tap' => $request->ti_le_hoc_tap,
        ]);

        // Nếu tỷ lệ cấu hình thay đổi, tính toán lại toàn bộ điểm rèn luyện của học kỳ này
        if ($hk->wasChanged('ti_le_ren_luyen') || $hk->wasChanged('ti_le_hoc_tap')) {
            $transcripts = DiemRenLuyen::where('hoc_ky_id', $hk->id)->get();
            foreach ($transcripts as $t) {
                DiemRenLuyenService::recalculatePoints($t->sinh_vien_id, $hk->id);
            }
        }

        return redirect()->route('hoc_ky.settings')->with('success', 'Cập nhật cấu hình học kỳ thành công!');
    }
}
