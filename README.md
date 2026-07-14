# SV-DRL

Hệ thống Quản lý Điểm rèn luyện Sinh viên (SV-DRL) được xây dựng bằng Laravel 13 và MySQL/MariaDB, phục vụ quy trình đánh giá rèn luyện trực tuyến của nhà trường một cách minh bạch, tự động và nhanh chóng. Dự án bao gồm các phân hệ chính dành cho Sinh viên, Ban Cán sự lớp, Giảng viên/Cố vấn học tập (CVHT), phòng Công tác sinh viên (CTSV)/Khoa, và Quản trị viên (Admin).

- Phía sinh viên và ban cán sự: Tự chấm điểm rèn luyện, tải minh chứng, đăng ký hoạt động, điểm danh QR code thời gian thực, xem lịch sử và gửi khiếu nại.
- Phía giảng viên/cố vấn học tập: Duyệt minh chứng, đánh giá, phản hồi và chốt điểm rèn luyện cho lớp chủ nhiệm.
- Phía CTSV (Phòng Công tác sinh viên)/Khoa/Admin: Quản lý đợt đánh giá, hoạt động rèn luyện, phân công cố vấn, cấu hình học kỳ, thống kê & xuất báo cáo Excel, sao lưu và khôi phục dữ liệu hệ thống.

## 1. Tổng quan chức năng

### Phân hệ Sinh viên & Ban cán sự lớp
- **Tự chấm điểm rèn luyện cá nhân**: Thực hiện tự đánh giá và chấm điểm trực tuyến theo bộ tiêu chí gồm 5 mục chính và 1 mục vượt khung (từ TC1 đến TC6) theo cấu hình của từng học kỳ.
- **Quản lý và nộp hồ sơ minh chứng**: Tải lên hình ảnh/tập tin minh chứng (chứng chỉ, giấy khen, ảnh hoạt động) làm căn cứ đối chiếu cho các tiêu chí tương ứng hoặc lưu trữ qua liên kết Google Drive.
- **Tham gia hoạt động rèn luyện**: Theo dõi và đăng ký tham gia các hoạt động/sự kiện do nhà trường tổ chức.
- **Điểm danh thời gian thực bằng mã QR**: Điểm danh check-in tại các sự kiện thông qua mã QR động/cố định. Trạng thái tham gia tự động cập nhật thời gian thực sang `co_mat` trên giao diện mà không cần tải lại trang (no-refresh/không cần F5).
- **Theo dõi tiến độ duyệt**: Xem tiến trình phê duyệt hồ sơ điểm rèn luyện cá nhân qua các cấp (Chờ duyệt, Đã duyệt, Từ chối).
- **Gửi khiếu nại phúc khảo**: Tạo phiếu khiếu nại phản hồi trực tiếp khi có sự sai lệch về điểm rèn luyện hoặc khi minh chứng bị từ chối.
- **Quyền hạn Ban cán sự lớp**:
  - Xem danh sách thành viên lớp, kiểm tra tiến độ nộp phiếu tự đánh giá.
  - Phê duyệt nhanh hàng loạt (Bulk Approve) phiếu điểm của lớp từ trạng thái `Tạm tính (tam_tinh)` sang `Chờ Cố vấn duyệt (cho_cvht_duyet)`.

### Phân hệ Giảng viên / Cố vấn học tập (CVHT)
- **Quản lý lớp chủ nhiệm**: Xem chi tiết danh sách, kết quả tự chấm điểm rèn luyện của sinh viên các lớp thuộc phân công chủ nhiệm.
- **Kiểm duyệt minh chứng**: Xem chi tiết hình ảnh, tài liệu minh chứng sinh viên nộp; thực hiện phê duyệt hoặc từ chối từng minh chứng.
- **Chấm điểm & Phản hồi**: Chỉnh sửa điểm số thực tế dựa trên minh chứng và thái độ học tập của sinh viên, đồng thời nhập ý kiến phản hồi cụ thể.
- **Chốt bảng điểm lớp**: Duyệt tổng thể phiếu điểm của cả lớp và chốt kết quả chuyển lên cấp CTSV/Khoa xét duyệt cuối cùng (`cho_ctsv_duyet`).

### Phân hệ CTSV / Khoa
- **Quản lý học kỳ & Đợt đánh giá**: Tạo mới học kỳ, cấu hình mốc thời gian bắt đầu và kết thúc của từng giai đoạn duyệt.
- **Phân công cố vấn**: Phân công giảng viên phụ trách cố vấn học tập cho các lớp theo từng học kỳ.
- **Quản lý hoạt động rèn luyện**: Tạo mới hoạt động, thiết lập điểm dự kiến, số lượng slot tối đa, và tự động sinh mã QR điểm danh (có tính năng làm mờ/khóa quét QR động bằng CSS blur).
- **Duyệt hồ sơ tổng thể**: Kiểm tra chéo kết quả điểm rèn luyện và duyệt hồ sơ cấp khoa/toàn trường.
- **Thống kê & Xuất báo cáo**:
  - Xem Dashboard trực quan thống kê tỷ lệ xếp loại rèn luyện và tiến độ của từng lớp.
  - Xuất báo cáo điểm rèn luyện ra file Excel (CSV) theo mẫu của nhà trường.
- **Quản lý sao lưu (Backup & Restore)**: Thực hiện sao lưu dữ liệu thủ công hoặc tự động, tải file backup hoặc khôi phục dữ liệu hệ thống từ file SQL.

### Phân hệ Admin (Quản trị hệ thống)
- **Quản lý người dùng**: Thêm mới, cập nhật thông tin và phân quyền tài khoản (Sinh viên, Giảng viên, CTSV, Admin).
- **Quản lý danh mục nền tảng**: Quản lý cơ cấu Khoa, Ngành học, Lớp học, Hệ đào tạo, Khóa học.
- **Cấu hình bộ tiêu chí đánh giá**: Quản lý các tiêu chí rèn luyện chính, các tiêu chí con và giới hạn điểm tối đa của từng mục.

## 2. Công nghệ sử dụng

- **PHP**: Phiên bản 8.3
- **Laravel Framework**: Phiên bản 13.x
- **Frontend**: Blade Template Engine, Tailwind CSS v4, JavaScript, Alpine.js, jQuery, Vite
- **Cơ sở dữ liệu**: MySQL / MariaDB
- **Sao lưu hệ thống**: Laravel Backup & Command-line SQLite/MySQL dump utilities
- **Package Manager**: pnpm, Composer

## 3. Cấu trúc dự án

```text
PhanMemQuanLyDiemRenLuyen/
|-- app/
|   |-- Http/
|   |   |-- Controllers/          # Xử lý các logic nghiệp vụ (Auth, HoatDong, DiemRenLuyen,...)
|   |   |-- Middleware/           # Middleware phân quyền (RoleMiddleware) và sao lưu tự động (AutoBackupMiddleware)
|   |   `-- Requests/             # Validate dữ liệu đầu vào
|   |-- Models/                   # Định nghĩa các bảng CSDL bằng Eloquent ORM
|   |-- Services/                 # Logic phụ trợ như BackupService
|   `-- Jobs/                     # Tác vụ nền (DatabaseBackupJob)
|-- config/                       # Cấu hình hệ thống (database, app, queue,...)
|-- database/
|   |-- migrations/               # Cấu trúc bảng CSDL
|   `-- seeders/                  # Dữ liệu mẫu (DatabaseSeeder.php)
|-- public/                       # File tĩnh, tài liệu minh chứng, database backups
|-- resources/
|   `-- views/                    # Giao diện Blade (layouts, diem_ren_luyen, hoat_dong, xet_duyet, backup,...)
|-- routes/
|   `-- web.php                   # Định nghĩa tất cả URL và Middleware áp dụng
|-- storage/                      # Chứa log hệ thống, session, cache và file uploads tạm
|-- composer.json                 # Quản lý dependencies PHP
`-- package.json / pnpm-lock.yaml # Quản lý dependencies JavaScript/Vite
```

## 4. Yêu cầu môi trường

- Laragon (Apache/Nginx + MySQL/MariaDB) hoặc môi trường PHP/MySQL độc lập.
- PHP từ 8.3 trở lên (bật các extension: `pdo`, `mbstring`, `openssl`).
- MySQL 8.x hoặc MariaDB 10.x trở lên.
- Composer 2.x trở lên.
- Node.js (tối thiểu v18) & pnpm / npm.

## 5. Hướng dẫn cài đặt

1. Đặt source code vào thư mục `www` của Laragon:
   ```text
   C:\laragon\www\PhanMemQuanLyDiemRenLuyen
   ```

2. Mở Laragon và nhấn **Start All** để khởi động Apache và MySQL.

3. Mở Terminal tại thư mục dự án và chạy lệnh thiết lập tự động:
   ```bash
   composer run setup
   ```
   *Lưu ý: Lệnh này sẽ tự động chạy `composer install`, sao chép file `.env.example` thành `.env`, tạo app key, chạy migrations để tạo cấu trúc bảng, cài đặt dependencies frontend và build assets qua Vite.*

4. Chạy Seeder để nạp dữ liệu mẫu cấu hình tiêu chí và tài khoản thử nghiệm:
   ```bash
   php artisan db:seed
   ```

5. Chạy server phát triển (chạy đồng thời server PHP và Vite):
   ```bash
   composer run dev
   ```

6. Truy cập website qua địa chỉ mặc định: `http://localhost:8000` (hoặc domain Virtual Host của Laragon: `http://PhanMemQuanLyDiemRenLuyen.test`).

## 6. Tài khoản mặc định

Hệ thống sử dụng mật khẩu chung là `password` cho tất cả các tài khoản thử nghiệm sau:

- **Sinh viên (Sinh viên thông thường)**:
  - Email: `sinhvien@sv.com`
  - Mật khẩu: `password`

- **Ban cán sự lớp (Lớp trưởng/Lớp phó)**:
  - Email: `bcs@sv.com`
  - Mật khẩu: `password`

- **Giảng viên / Cố vấn học tập (CVHT)**:
  - Email: `covan@sv.com`
  - Mật khẩu: `password`

- **Phòng CTSV / Khoa / Admin**:
  - Email: `ctsv@sv.com`
  - Mật khẩu: `password`

## 7. Luồng sử dụng chính

1. **Khởi động học kỳ**: Phòng CTSV tạo học kỳ mới, thiết lập thời hạn cho 5 giai đoạn của đợt đánh giá và phân công Giảng viên làm CVHT cho các lớp hành chính.
2. **Khai báo & Điểm danh**: Sinh viên đăng ký tham gia hoạt động rèn luyện trong trường và quét mã QR tại sự kiện để điểm danh trực tiếp. Sinh viên cũng có thể nộp minh chứng hoạt động ngoài trường.
3. **Tự đánh giá**: Sinh viên vào giao diện tự đánh giá, điền điểm tự chấm cho từng mục tiêu chí (hệ thống tự động tính điểm GPA học tập quy đổi) và gửi phiếu điểm (trạng thái đổi sang `Chờ BCS lớp duyệt`).
4. **Họp lớp & BCS duyệt**: Ban cán sự lớp kiểm duyệt, rà soát tiến độ của lớp và thực hiện phê duyệt hàng loạt (Bulk Approve) để gửi phiếu điểm của lớp lên cho Cố vấn học tập (trạng thái đổi sang `Chờ Cố vấn duyệt`).
5. **Cố vấn chấm điểm**: CVHT kiểm tra các minh chứng đính kèm, thực hiện duyệt hoặc từ chối minh chứng, điều chỉnh điểm rèn luyện thực tế của sinh viên và chốt bảng điểm của lớp gửi lên trường (trạng thái đổi sang `Chờ CTSV duyệt`).
6. **CTSV thẩm định & Khóa điểm**: Phòng CTSV duyệt tổng thể toàn trường, tiếp nhận và phản hồi khiếu nại (nếu có) từ sinh viên, sau đó thực hiện chốt sổ và khóa điểm của học kỳ.

## 8. Các route chính

### Phân hệ chung & Xác thực
- `/login`: Đăng nhập (GET/POST)
- `/register`: Đăng ký tài khoản (GET/POST)
- `/logout`: Đăng xuất (POST)
- `/`: Dashboard hiển thị thống kê tổng quan theo vai trò (GET)

### Hoạt động rèn luyện & Điểm danh
- `/hoat-dong`: Xem danh sách hoạt động rèn luyện (GET)
- `/hoat-dong/create`: Tạo mới hoạt động - dành cho CTSV (GET/POST)
- `/hoat-dong/{id}`: Xem chi tiết hoạt động (GET)
- `/hoat-dong/{id}/dang-ky`: Đăng ký tham gia hoạt động (POST)
- `/hoat-dong/{id}/huy-dang-ky`: Hủy đăng ký hoạt động (POST)
- `/hoat-dong/{id}/diem-danh-qr`: Màn hình quét QR điểm danh của sinh viên (GET)
- `/hoat-dong/{id}/diem-danh`: Danh sách điểm danh của hoạt động - dành cho CTSV (GET)
- `/hoat-dong/diem-danh/{id}`: Lưu cập nhật trạng thái điểm danh - dành cho CTSV (POST)
- `/hoat-dong/check-attendance/{id}`: API check điểm danh thời gian thực (GET)

### Minh chứng & Khiếu nại
- `/minh-chung`: Xem danh sách và tải lên hồ sơ minh chứng (GET/POST)
- `/minh-chung/duyet/{id}`: Duyệt/từ chối minh chứng của sinh viên - dành cho CVHT/CTSV (POST)
- `/khieu-nai`: Xem và gửi khiếu nại phúc khảo điểm rèn luyện (GET/POST)
- `/khieu-nai/reply/{id}`: Phản hồi nội dung khiếu nại của sinh viên (POST)

### Xét duyệt & Bảng điểm
- `/diem-ren-luyen`: Xem bảng điểm rèn luyện cá nhân hoặc danh sách toàn trường (GET)
- `/diem-ren-luyen/tu-danh-gia`: Sinh viên tự đánh giá rèn luyện học kỳ (GET/POST)
- `/diem-ren-luyen/bao-cao`: Giao diện báo cáo, thống kê điểm rèn luyện (GET)
- `/diem-ren-luyen/bao-cao/export`: Xuất báo cáo điểm rèn luyện ra file CSV (GET)
- `/xet-duyet`: Danh sách phiếu đánh giá cần duyệt theo lớp (GET)
- `/xet-duyet/update/{id}`: Duyệt chuyển trạng thái phiếu điểm (POST)
- `/xet-duyet/{id}/danh-gia`: Xem chi tiết và đánh giá chấm điểm phiếu rèn luyện (GET/POST)
- `/xet-duyet/bulk-approve`: Phê duyệt nhanh hàng loạt phiếu điểm (POST)
- `/xet-duyet/unlock/{id}`: Mở khóa phiếu điểm rèn luyện đã chốt - dành cho CTSV (POST)

### Quản lý cấu hình & Hệ thống (Chỉ dành cho CTSV/Admin)
- `/xet-duyet/phan-cong`: Giao diện và lưu phân công cố vấn học tập lớp (GET/POST)
- `/xet-duyet/phan-cong/delete/{id}`: Xóa phân công cố vấn lớp (POST)
- `/hoc-ky/settings`: Thiết lập thời gian các giai đoạn đánh giá rèn luyện (GET/POST)
- `/backup`: Giao diện quản lý sao lưu dữ liệu (GET)
- `/backup/run`: Thực hiện sao lưu dữ liệu thủ công (POST)
- `/backup/settings`: Cấu hình tự động sao lưu dữ liệu (POST)
- `/backup/download/{file}`: Tải file sao lưu CSDL (GET)
- `/backup/delete/{file}`: Xóa bản sao lưu dữ liệu (DELETE)
- `/backup/restore/{file}`: Khôi phục CSDL từ file có sẵn trên server (POST)
- `/backup/restore-upload`: Tải file CSDL lên để khôi phục (POST)

## 9. Cơ sở dữ liệu

Hệ thống sử dụng cơ sở dữ liệu quan hệ gồm 26 bảng chính:

- `users` & `roles` & `role_user`: Quản lý thông tin tài khoản, danh mục vai trò (`ctsv`, `sinh_vien`, `ban_can_su`, `co_van`) và phân quyền.
- `he_dao_taos`, `khoas`, `nganhs`, `khoa_hocs`, `lops`: Danh mục tổ chức hành chính của nhà trường.
- `sinh_viens`: Thông tin chi tiết sinh viên liên kết với lớp và tài khoản đăng nhập.
- `cau_lac_bos` & `clb_sinh_vien`: Quản lý danh sách các câu lạc bộ và thành viên sinh hoạt.
- `don_vi_to_chucs`: Đơn vị tổ chức các hoạt động/sự kiện (ví dụ: Đoàn trường, Hội sinh viên).
- `hoc_kys` & `phan_cong_co_vans`: Quản lý học kỳ và phân công cố vấn học tập quản lý lớp theo học kỳ tương ứng.
- `cau_hinh_dot_duyets`: Lưu trữ mốc thời gian diễn ra của 5 giai đoạn chấm điểm (Tự đánh giá, BCS duyệt, CVHT duyệt, CTSV duyệt, Khiếu nại).
- `tieu_chi_ren_luyens`: Định nghĩa danh mục các tiêu chí rèn luyện và mức điểm trần tối đa.
- `hoat_dongs` & `dang_ky_hoat_dongs`: Quản lý hoạt động rèn luyện và lịch sử đăng ký tham gia của sinh viên.
- `diem_danhs`: Lưu vết thời gian check-in/check-out điểm danh của sinh viên tại các hoạt động.
- `minh_chungs` & `ho_so_minh_chungs`: Lưu trữ file minh chứng và liên kết hồ sơ đề xuất cộng điểm của sinh viên.
- `ky_luats`: Ghi nhận các trường hợp sinh viên vi phạm nội quy để tự động trừ điểm rèn luyện.
- `khieu_nais`: Ghi nhận khiếu nại, phản hồi của sinh viên và câu trả lời của các cấp xét duyệt.
- `diem_hoc_taps`: Điểm GPA học kỳ của sinh viên được nạp vào hệ thống để tự động quy đổi sang điểm rèn luyện (TC1).
- `diem_ren_luyens` & `chi_tiet_diem_ren_luyens`: Lưu kết quả tổng hợp điểm rèn luyện (xếp loại, trạng thái duyệt) và chi tiết điểm số tự chấm/duyệt của từng tiêu chí.
- `thong_baos` & `nguoi_nhan_thong_baos`: Gửi thông báo hệ thống đến người dùng.
- `audit_logs`: Nhật ký hoạt động hệ thống ghi lại lịch sử chỉnh sửa điểm, duyệt minh chứng phục vụ việc đối soát thông tin.

## 10. Cấu hình tự động sao lưu dữ liệu

Hệ thống tích hợp chức năng sao lưu dữ liệu tự động định kỳ nhằm đảm bảo an toàn thông tin:
- **Cơ chế hoạt động**: Sử dụng `AutoBackupMiddleware` để kiểm tra thời gian sao lưu tiếp theo khi có người dùng truy cập. Khi đến hạn, hệ thống sẽ tự động kích hoạt tiến trình chạy ngầm qua queue (`DatabaseBackupJob`) giúp người dùng không cảm thấy độ trễ.
- **Cấu hình hàng ngày/hàng tuần**: CTSV có thể thay đổi tần suất sao lưu (hàng ngày, hàng tuần, hàng tháng) và thời gian sao lưu mong muốn ngay trên giao diện `/backup`.
- **Hàng đợi (Queue)**: Để đảm bảo việc sao lưu không gây nghẽn hệ thống, các bản sao lưu được đẩy vào queue. Do đó, cần khởi chạy hàng đợi bằng lệnh:
  ```bash
  php artisan queue:listen
  ```
  (Hoặc chạy qua lệnh gộp `composer run dev`).

## 11. Điểm nổi bật & Khả năng mở rộng

### Điểm nổi bật
- **Điểm danh QR thời gian thực không cần tải lại trang**: Tích hợp cơ chế realtime. Khi sinh viên quét mã QR thành công, giao diện hoạt động tự động cập nhật trạng thái đã điểm danh sang `co_mat` ngay lập tức mà không cần F5 trình duyệt.
- **Tính năng khóa/mở khóa QR động**: Mã QR điểm danh được bảo mật bằng hiệu ứng làm mờ (blur) tự động trước thời gian diễn ra sự kiện và chỉ hiển thị rõ nét khi đến giờ điểm danh.
- **Xét duyệt đa cấp chặt chẽ**: Quy trình 4 bước khép kín (Sinh viên -> Ban cán sự -> Cố vấn -> CTSV) phản ánh đúng nghiệp vụ thực tế của các trường Đại học.
- **Phê duyệt hàng loạt tiện lợi (Bulk Approve)**: Cho phép BCS lớp và CVHT duyệt nhanh toàn bộ danh sách sinh viên chỉ với 1 click chuột, tiết kiệm tối đa thời gian.
- **Hệ thống Backup & Restore toàn diện**: Tích hợp công cụ quản lý sao lưu trực tiếp trên Web Admin, cho phép CTSV khôi phục nhanh hệ thống từ các bản sao lưu cũ hoặc upload file SQL mới.

### Khả năng mở rộng
- **Tích hợp API Google Drive/OneDrive**: Mở rộng dung lượng lưu trữ minh chứng bằng cách đồng bộ trực tiếp lên đám mây thay vì lưu trữ cục bộ trên máy chủ.
- **Hệ thống Điểm danh nhận diện khuôn mặt (FaceID) hoặc GPS**: Tích hợp các công nghệ xác thực vị trí và nhận diện sinh viên để phòng chống tình trạng điểm danh hộ qua QR Code.
- **Tự động đồng bộ điểm GPA từ Cổng thông tin đào tạo**: Xây dựng Webhook/API kết nối trực tiếp với phân hệ Quản lý đào tạo để nạp điểm GPA của sinh viên thay vì nhập tay/import file excel.

## 12. Lưu ý khi chấm hoặc demo

- **Học kỳ hiện tại**: Hệ thống kiểm tra thời gian thực để mở/khóa các nút chấm điểm rèn luyện dựa trên cấu hình các giai đoạn của đợt xét duyệt (`cau_hinh_dot_duyets`). Khi demo, nếu thấy nút tự chấm hoặc nút duyệt bị ẩn, hãy truy cập tài khoản CTSV (`ctsv@sv.com`) -> Vào **Cấu hình học kỳ** để nới rộng thời gian của giai đoạn tương ứng hoặc nhấn **Mở khóa chỉnh sửa** cho sinh viên đó.
- **Chạy Queue**: Cần chạy queue lắng nghe tác vụ (`php artisan queue:listen`) để xử lý các yêu cầu sao lưu ngầm hoặc chạy đồng thời bằng `composer run dev`.
- **Thư mục Upload & Backup**: Đảm bảo các thư mục `storage/app/backups`, `storage/app/public/minh_chung` và các thư mục tương tự có quyền ghi (chạy `php artisan storage:link` để tạo liên kết public).

## 13. Gợi ý kiểm thử nhanh

1. Đăng nhập bằng tài khoản **Sinh viên** (`sinhvien@sv.com`): Đăng ký một hoạt động, xem giao diện tự đánh giá rèn luyện học kỳ và tải lên file minh chứng.
2. Đăng nhập tài khoản **Ban cán sự** (`bcs@sv.com`): Vào trang quản lý lớp chủ nhiệm để xem tiến độ tự chấm của sinh viên, sau đó thực hiện duyệt hàng loạt lớp.
3. Đăng nhập tài khoản **Cố vấn học tập** (`covan@sv.com`): Kiểm duyệt hồ sơ minh chứng đã nộp, điều chỉnh điểm rèn luyện thực tế của sinh viên, điền lý do/phản hồi và chốt điểm của lớp.
4. Đăng nhập tài khoản **CTSV** (`ctsv@sv.com`):
   - Tạo một hoạt động rèn luyện mới, mở mã QR điểm danh.
   - Thử chức năng sao lưu dữ liệu thủ công, tải xuống bản backup và thử khôi phục lại hệ thống.
   - Xem dashboard thống kê biểu đồ và xuất báo cáo Excel điểm rèn luyện.

## 14. Tác giả

- Họ và tên: Thanh Thiên
- Tài khoản: ThanhThien07
- Email: slen010207@gmail.com

## 15. Ghi chú

README này được cấu trúc lại hoàn chỉnh theo đúng nghiệp vụ thực tế của Phần mềm Quản lý Điểm rèn luyện Sinh viên (SV-DRL) đang chạy trong mã nguồn.
