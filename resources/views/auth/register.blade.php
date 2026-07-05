<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng ký tài khoản - ĐIỂM RÈN LUYỆN</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-color: #8c1d40;
      --secondary-color: #d4af37;
    }
    body {
      font-family: 'Outfit', sans-serif;
      background: radial-gradient(circle at 10% 20%, rgba(140, 29, 64, 0.15) 0%, rgba(26, 26, 26, 0.95) 80%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      padding: 40px 0;
    }
    .register-card {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 24px;
      padding: 40px 30px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
      width: 100%;
      max-width: 500px;
    }
    .form-control, .form-select {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.15);
      color: #fff;
      border-radius: 12px;
      padding: 12px 16px;
    }
    .form-select option {
      background: #222;
      color: #fff;
    }
    .btn-premium {
      background: linear-gradient(135deg, var(--primary-color) 0%, #63122b 100%);
      color: #fff;
      border-radius: 12px;
      padding: 12px 20px;
      border: none;
    }
  </style>
</head>
<body>
  <div class="register-card">
    <div class="text-center mb-4">
      <h3 class="fw-bold">ĐĂNG KÝ TÀI KHOẢN</h3>
      <p class="text-secondary small">Tham gia hệ thống quản lý điểm rèn luyện sinh viên</p>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-white p-3 mb-4">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('register') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label class="form-label text-secondary">Họ và tên</label>
        <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" value="{{ old('name') }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label text-secondary">Email</label>
        <input type="email" name="email" class="form-control" placeholder="a@sv.com" value="{{ old('email') }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label text-secondary">Vai trò</label>
        <select name="role" class="form-select" required>
          <option value="sinh_vien">Sinh viên</option>
          <option value="ban_can_su">Ban cán sự lớp</option>
          <option value="co_van">Cố vấn học tập</option>
          <option value="admin">Phòng Công tác sinh viên (CTSV)</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label text-secondary">Mật khẩu</label>
        <input type="password" name="password" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
      </div>
      <div class="mb-4">
        <label class="form-label text-secondary">Xác nhận mật khẩu</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
      </div>
      <button type="submit" class="btn btn-premium w-100 mb-3 py-2.5">Đăng ký tài khoản</button>
    </form>

    <div class="text-center">
      <span class="text-secondary small">Đã có tài khoản? </span>
      <a href="{{ route('login') }}" style="color: var(--secondary-color); text-decoration: none;">Đăng nhập ngay</a>
    </div>
  </div>
</body>
</html>