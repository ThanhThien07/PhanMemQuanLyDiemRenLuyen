@extends('layouts.app')

@section('content')
<div class="container-fluid">
  <!-- Title section -->
  <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
    <div>
      <h2 class="font-extrabold text-slate-800 text-2xl mb-1.5 flex items-center gap-2">
        <i class="bi bi-columns-gap text-sky-500"></i> Hệ Thống Thống Kê & Quản Trị
      </h2>
      <p class="text-slate-500 text-sm mb-0">Chào mừng bạn trở lại, <span class="font-semibold text-slate-700">{{ Auth::user()->name }}</span>. Xem báo cáo và chỉnh sửa thông tin tại đây.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      @if(in_array(Auth::user()->role, ['ctsv', 'co_van']))
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition duration-200 shadow-sm">
          <i class="bi bi-printer text-[#0284c7]"></i>
          <span>In Báo Cáo</span>
        </button>
        <a href="{{ route('diem_ren_luyen.report') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition duration-200 text-decoration-none shadow-sm">
          <i class="bi bi-file-earmark-excel text-[#16a34a]"></i>
          <span>Xuất Excel</span>
        </a>
        <a href="{{ route('xet_duyet.index') }}" class="btn-premium py-2 px-4 text-sm font-semibold flex items-center gap-2 shadow-sm text-decoration-none">
          <i class="bi bi-person-plus"></i>
          <span>Xét duyệt lớp</span>
        </a>
      @else
        <a href="{{ route('minh_chung.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition duration-200 text-decoration-none shadow-sm">
          <i class="bi bi-file-earmark-arrow-up text-[#0284c7]"></i>
          <span>Nộp Minh Chứng</span>
        </a>
        <a href="{{ route('diem_ren_luyen.index') }}" class="btn-premium py-2 px-4 text-sm font-semibold flex items-center gap-2 shadow-sm text-decoration-none">
          <i class="bi bi-journal-text"></i>
          <span>Xem Bảng Điểm</span>
        </a>
      @endif
    </div>
  </div>

  @if(isset($no_profile) && $no_profile)
    <div class="alert alert-warning card-premium p-5 mb-6 border-l-4 border-amber-500 shadow-sm" role="alert">
      <div class="flex items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill text-xl text-amber-500"></i>
        <div>
          <h5 class="font-bold text-amber-800 mb-2">Chưa khởi tạo hồ sơ sinh viên!</h5>
          <p class="mb-0 text-amber-700">Hệ thống chưa tìm thấy hồ sơ sinh viên cho tài khoản này. Vui lòng liên hệ Phòng CTSV hoặc Cố vấn học tập để cập nhật thông tin.</p>
        </div>
      </div>
    </div>
  @elseif(Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su')
    <!-- Student Widgets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      
      <!-- Card 1: Cumulative points -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div>
          <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Điểm tổng hợp học kỳ</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-2">
            {{ $diemRenLuyen ? number_format($diemRenLuyen->diem_tong_hop, 2) : '0.00' }}
          </h2>
          <span class="badge bg-success">{{ $diemRenLuyen ? $diemRenLuyen->xep_loai : 'Chưa xếp loại' }}</span>
        </div>
        <div class="bg-sky-50 text-sky-500 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-star-fill text-2xl"></i>
        </div>
      </div>

      <!-- Card 2: Registered Activities -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div>
          <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">Hoạt động tham gia</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-2">
            {{ $hoatDongDaThamGiaCount }}
          </h2>
          <span class="text-xs text-slate-500 font-medium">Có mặt tại sự kiện</span>
        </div>
        <div class="bg-emerald-50 text-emerald-500 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-calendar-check text-2xl"></i>
        </div>
      </div>

      <!-- Card 3: Evidences pending -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div>
          <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block mb-1">Minh chứng chờ duyệt</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-2">
            {{ $minhChungChoDuyetCount }}
          </h2>
          <span class="text-xs text-slate-500 font-medium">Hồ sơ đã nộp</span>
        </div>
        <div class="bg-amber-50 text-amber-500 p-4 rounded-2xl group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-file-earmark-arrow-up text-2xl"></i>
        </div>
      </div>
    </div>

    <!-- Student Profile Summary ID Card -->
    <div class="card-premium p-6 mb-6 relative overflow-hidden">
      <!-- Gradient mesh decoration -->
      <div class="absolute -right-16 -top-16 w-48 h-48 bg-gradient-to-br from-sky-400/10 to-indigo-500/10 rounded-full blur-2xl"></div>
      <div class="absolute -left-16 -bottom-16 w-48 h-48 bg-gradient-to-br from-indigo-400/5 to-purple-500/5 rounded-full blur-2xl"></div>

      <div class="flex flex-col md:flex-row items-center gap-5 relative z-10">
        <div class="flex-shrink-0">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-400 to-indigo-500 text-white flex items-center justify-center font-bold text-2xl shadow-[0_6px_20px_rgba(14,165,233,0.25)]">
            {{ substr($sinhVien->ho_ten, 0, 2) }}
          </div>
        </div>
        <div class="flex-grow text-center md:text-left">
          <h4 class="font-extrabold text-slate-800 text-lg mb-1.5">{{ $sinhVien->ho_ten }}</h4>
          <div class="flex flex-wrap justify-center md:justify-start gap-x-4 gap-y-1.5 text-sm text-slate-500">
            <span>MSSV: <strong class="text-slate-700">{{ $sinhVien->ma_sv }}</strong></span>
            <span class="hidden md:inline text-slate-300">|</span>
            <span>Lớp: <strong class="text-slate-700">{{ $sinhVien->lop->ten_lop }}</strong></span>
            <span class="hidden md:inline text-slate-300">|</span>
            <span>Hệ đào tạo: <strong class="text-slate-700">{{ $sinhVien->heDaoTao->ten_he }}</strong></span>
          </div>
        </div>
      </div>
    </div>
  @else
    <!-- Advisor & CTSV Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      
      <!-- Total Students -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div>
          <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Tổng số sinh viên</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-0">{{ $stats['total_students'] }}</h2>
        </div>
        <div class="bg-sky-50 text-sky-500 p-3.5 rounded-2xl group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-people-fill text-2xl"></i>
        </div>
      </div>

      <!-- Total Activities -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div>
          <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Tổng số hoạt động</span>
          <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1 mb-0">{{ $stats['total_activities'] }}</h2>
        </div>
        <div class="bg-emerald-50 text-emerald-500 p-3.5 rounded-2xl group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-calendar3 text-2xl"></i>
        </div>
      </div>

      <!-- Evidences Pending -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div>
          <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Minh chứng chờ duyệt</span>
          <h2 class="text-3xl font-extrabold text-amber-600 tracking-tight mt-1 mb-0">{{ $stats['pending_evidences'] }}</h2>
        </div>
        <div class="bg-amber-50 text-amber-500 p-3.5 rounded-2xl group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-file-earmark-check text-2xl"></i>
        </div>
      </div>

      <!-- Complaints Pending -->
      <div class="card-premium p-6 flex justify-between items-center relative overflow-hidden group">
        <div>
          <span class="text-slate-400 text-xs font-bold uppercase tracking-wider block">Khiếu nại chờ xử lý</span>
          <h2 class="text-3xl font-extrabold text-rose-600 tracking-tight mt-1 mb-0">{{ $stats['pending_complaints'] }}</h2>
        </div>
        <div class="bg-red-50 text-rose-500 p-3.5 rounded-2xl group-hover:scale-110 transition-transform duration-300">
          <i class="bi bi-exclamation-octagon text-2xl"></i>
        </div>
      </div>

    </div>
  @endif

  @if(in_array(Auth::user()->role, ['ctsv', 'co_van']))
  <!-- Charts Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Donut Chart Card -->
    <div class="card-premium overflow-hidden">
      <div class="card-premium-header bg-slate-50/50">
        <h5 class="card-premium-title text-slate-800 flex items-center gap-2">
          <i class="bi bi-pie-chart-fill text-sky-500"></i> Tỷ Lệ Xếp Loại Điểm Rèn Luyện
        </h5>
        @if(Auth::user()->role === 'sinh_vien' || Auth::user()->role === 'ban_can_su')
          <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2.5 py-1 rounded-lg">Số liệu của Lớp</span>
        @elseif(Auth::user()->role === 'co_van')
          <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2.5 py-1 rounded-lg">Lớp phụ trách</span>
        @else
          <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2.5 py-1 rounded-lg">Toàn trường</span>
        @endif
      </div>
      <div class="p-5 flex items-center justify-center min-h-[300px]">
        <div id="donutChart" class="w-full"></div>
      </div>
    </div>

    <!-- Line Chart Card -->
    <div class="card-premium overflow-hidden">
      <div class="card-premium-header bg-slate-50/50">
        <h5 class="card-premium-title text-slate-800 flex items-center gap-2">
          <i class="bi bi-graph-up text-indigo-500"></i> Lượt Đăng Ký Hoạt Động (7 ngày qua)
        </h5>
        <span class="text-xs text-slate-500 font-semibold bg-slate-100 px-2.5 py-1 rounded-lg">Tốc độ đăng ký</span>
      </div>
      <div class="p-5 min-h-[300px]">
        <div id="lineChart" class="w-full"></div>
      </div>
    </div>
  </div>
  @endif

</div>
@endsection

@section('scripts')
@if(in_array(Auth::user()->role, ['ctsv', 'co_van']))
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // 1. Donut Chart Setup
    const donutData = @json($donutData);
    const donutCategories = Object.keys(donutData);
    const donutSeries = Object.values(donutData);

    const donutOptions = {
      chart: {
        type: 'donut',
        height: 280,
        fontFamily: 'Outfit, sans-serif'
      },
      series: donutSeries,
      labels: donutCategories,
      colors: ['#10b981', '#f59e0b', '#0ea5e9', '#06b6d4', '#ef4444'], // Xuất sắc, Tốt, Khá, Trung bình, Yếu
      legend: {
        position: 'bottom',
        fontSize: '13px',
        fontWeight: 500,
        markers: {
          radius: 12
        }
      },
      dataLabels: {
        enabled: true,
        formatter: function (val, opts) {
          if (opts.w.globals.series[opts.seriesIndex] === 0) return '';
          return val.toFixed(1) + "%";
        }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '72%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Tổng xếp loại',
                fontSize: '14px',
                fontWeight: 600,
                color: '#64748b',
                formatter: function (w) {
                  return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' SV';
                }
              }
            }
          }
        }
      },
      responsive: [{
        breakpoint: 480,
        options: {
          legend: {
            position: 'bottom'
          }
        }
      }]
    };

    const donutChart = new ApexCharts(document.querySelector("#donutChart"), donutOptions);
    donutChart.render();

    // 2. Line Chart Setup
    const lineData = @json($lineData);
    const lineCategories = Object.keys(lineData);
    const lineSeries = Object.values(lineData);

    const lineOptions = {
      chart: {
        type: 'area',
        height: 280,
        fontFamily: 'Outfit, sans-serif',
        toolbar: {
          show: false
        }
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      series: [{
        name: 'Lượt đăng ký',
        data: lineSeries
      }],
      colors: ['#0ea5e9'],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.35,
          opacityTo: 0.05,
          stops: [0, 100]
        }
      },
      xaxis: {
        categories: lineCategories,
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '12px'
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '12px'
          },
          formatter: function(val) {
            return Math.floor(val);
          }
        }
      },
      grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 4
      },
      dataLabels: {
        enabled: false
      },
      tooltip: {
        theme: 'light'
      }
    };

    const lineChart = new ApexCharts(document.querySelector("#lineChart"), lineOptions);
    lineChart.render();
  });
</script>
@endif
@endsection