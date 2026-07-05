# Báo Cáo Tính Năng & Chức Năng Của Từng Vai Trò (Roles) - Hệ Thống SV-DRL

Tài liệu này tổng hợp chi tiết các tính năng và chức năng của từng vai trò người dùng trong **Hệ thống Quản lý Điểm rèn luyện Sinh viên (SV-DRL)**, được phân tích dựa trên nghiệp vụ thực tế của nhà trường:

---

## 1. Sinh Viên (Student)
Sinh viên là đối tượng cốt lõi thực hiện tự đánh giá rèn luyện và nộp hồ sơ minh chứng.
* **Tự chấm điểm rèn luyện cá nhân**: Thực hiện tự đánh giá và chấm điểm theo bộ tiêu chí (Từ Mục I đến Mục VI) được cấu hình bởi nhà trường cho từng học kỳ.
* **Quản lý và nộp minh chứng trực tuyến**: Tải lên hình ảnh/tập tin minh chứng (chứng chỉ, giấy khen, ảnh chụp hoạt động) làm căn cứ đối chiếu cho các tiêu chí tương ứng.
* **Tham gia hoạt động rèn luyện**: Đăng ký tham gia các hoạt động/sự kiện do nhà trường hoặc các đơn vị tổ chức.
* **Điểm danh bằng mã QR thời gian thực**: Quét mã QR điểm danh cố định tại buổi sinh hoạt/sự kiện. Hệ thống sẽ tự động cập nhật trạng thái điểm danh sang **co_mat** trên màn hình chi tiết hoạt động ngay lập tức mà không cần tải lại trang (Không cần F5).
* **Theo dõi trạng thái duyệt**: Xem tiến trình phê duyệt hồ sơ minh chứng của mình (Chờ duyệt, Đã duyệt, Từ chối).
* **Gửi khiếu nại**: Tạo phiếu khiếu nại phản hồi trực tiếp khi có sự sai lệch về điểm rèn luyện hoặc minh chứng bị từ chối.

---

## 2. Ban Cán Sự Lớp (Class Monitor - BCS)
Ban cán sự lớp (lớp trưởng/lớp phó) hỗ trợ Cố vấn học tập duyệt sơ bộ thông tin điểm số của các thành viên trong lớp.
* **Thừa hưởng quyền hạn Sinh viên**: Có đầy đủ các chức năng tự đánh giá, nộp minh chứng, đăng ký hoạt động và điểm danh QR của một sinh viên thông thường.
* **Theo dõi tiến độ của lớp**: Xem danh sách thành viên lớp, kiểm tra tiến độ nộp phiếu điểm rèn luyện của từng sinh viên.
* **Phê duyệt hàng loạt (Bulk Approve)**: Thực hiện duyệt nhanh phiếu điểm của các thành viên trong lớp từ trạng thái **Tạm tính (tam_tinh)** chuyển lên **Chờ Cố vấn duyệt (cho_cvht_duyet)**.

---

## 3. Giảng Viên / Cố Vấn Học Tập (Advisor - CVHT)
Cố vấn học tập chịu trách nhiệm chính trong việc hậu kiểm, đánh giá và chấm điểm rèn luyện cho lớp chủ nhiệm.
* **Quản lý danh sách lớp**: Xem chi tiết thông tin và kết quả điểm rèn luyện của sinh viên thuộc các lớp được phân công chủ nhiệm.
* **Duyệt hồ sơ minh chứng**: Kiểm tra tính hợp lệ của từng hình ảnh/tập tin minh chứng sinh viên nộp lên; phê duyệt hoặc từ chối minh chứng.
* **Điều chỉnh điểm & Phản hồi**: Chỉnh sửa điểm số thực tế của sinh viên dựa trên minh chứng và thái độ học tập, đồng thời gửi kèm ý kiến phản hồi/lý do điều chỉnh cụ thể.
* **Chốt bảng điểm của lớp**: Duyệt tổng thể phiếu điểm của cả lớp và chốt kết quả chuyển lên cấp Khoa/CTSV (**cho_ctsv_duyet**).

---

## 4. Khoa / Phòng Công Tác Sinh Viên (CTSV)
Phòng CTSV/Khoa là đơn vị quản trị dữ liệu cấp cao, điều phối toàn bộ hoạt động rèn luyện và chốt sổ kết quả cuối cùng.
* **Quản lý đợt đánh giá**: Tạo mới các đợt đánh giá (theo Học kỳ), cấu hình mốc thời gian bắt đầu và kết thúc của đợt chấm điểm.
* **Duyệt hồ sơ tổng thể**: Kiểm tra chéo kết quả điểm rèn luyện và duyệt hồ sơ cấp khoa/toàn trường.
* **Quản lý hoạt động rèn luyện & Điểm danh QR**:
  - Tạo mới các hoạt động rèn luyện cấp trường/khoa, cấu hình điểm dự kiến và số lượng tham gia tối đa.
  - Tự động sinh mã QR điểm danh cố định cho hoạt động. Mã QR này tự động bị làm mờ (CSS blur) để khóa quét trước sự kiện và tự động mở khóa rõ nét để quét điểm danh khi sự kiện diễn ra.
* **Thống kê & Xuất báo cáo**: 
  - Xem dashboard thống kê trực quan theo lớp, ngành, khoa và học kỳ.
  - Xuất báo cáo kết quả điểm rèn luyện ra file Excel/PDF theo mẫu của nhà trường.
* **Chốt sổ và khóa điểm**: Thực hiện chốt kết quả cuối cùng và khóa điểm rèn luyện học kỳ của toàn bộ sinh viên.

---

## 5. Admin (Quản Trị Hệ Thống)
Admin chịu trách nhiệm cấu hình nền tảng và vận hành mặt kỹ thuật của hệ thống.
* **Quản lý người dùng**: Tạo mới, chỉnh sửa thông tin và phân quyền tài khoản (Sinh viên, Giảng viên, CTSV, Admin).
* **Quản lý danh mục nền tảng**: Cập nhật danh sách Khoa, Ngành, Lớp học, Hệ đào tạo, Học kỳ.
* **Cấu hình bộ tiêu chí đánh giá**: Quản lý các tiêu chí rèn luyện chính, các tiêu chí thành phần con và điểm giới hạn tối đa cho từng tiêu chí.
