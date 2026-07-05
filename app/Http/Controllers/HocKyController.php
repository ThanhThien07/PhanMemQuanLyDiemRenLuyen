<?php

namespace App\Http\Controllers;

use App\Models\HocKy;
use App\Models\DiemRenLuyen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HocKyController extends Controller
{
    /**
     * Display semester settings and configuration.
     */
    public function settings()
    {
        $hocKys = HocKy::all();
        return view('hoc_ky.settings', compact('hocKys'));
    }

    /**
     * Update semester status or weights.
     */
    public function updateSettings(Request $request)
    {
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

        if (floatval($request->ti_le_ren_luyen) + floatval($request->ti_le_hoc_tap) != 100.0) {
            return back()->with('warning', 'Tổng hai tỷ lệ điểm rèn luyện và học tập phải bằng 100%.')->withInput();
        }

        $hk = HocKy::findOrFail($request->hoc_ky_id);
        
        // If transitioning to "dang_dien_ra", make sure all other semesters are "da_khoa"
        if ($request->trang_thai === 'dang_dien_ra') {
            HocKy::where('id', '!=', $hk->id)->update(['trang_thai' => 'da_khoa']);
        }

        $hk->update([
            'trang_thai' => $request->trang_thai,
            'ti_le_ren_luyen' => $request->ti_le_ren_luyen,
            'ti_le_hoc_tap' => $request->ti_le_hoc_tap,
        ]);

        // If weights changed, trigger recalculation for all student transcripts in this semester
        if ($hk->wasChanged('ti_le_ren_luyen') || $hk->wasChanged('ti_le_hoc_tap')) {
            $transcripts = DiemRenLuyen::where('hoc_ky_id', $hk->id)->get();
            $minhChungController = new MinhChungController();
            foreach ($transcripts as $t) {
                // Call public recalculate method (we will make sure this helper recalculates correctly)
                // Wait! Since recalculatePoints is private in MinhChungController, let's make it public!
                $minhChungController->recalculatePoints($t->sinh_vien_id, $hk->id);
            }
        }

        return redirect()->route('hoc_ky.settings')->with('success', 'Cập nhật cấu hình học kỳ thành công!');
    }
}
