<?php

namespace App\Helpers;

class EvaluationCriteria
{
    public static function getCriteria()
    {
        return [
            'I' => [
                'name' => 'Đánh giá về ý thức tham gia học tập',
                'max_score' => 30,
                'items' => [
                    'I.1' => [
                        'name' => 'Ý thức, thái độ, tinh thần vượt khó, phấn đấu vươn lên trong học tập',
                        'max_score' => 5,
                        'description' => 'Tự đánh giá tinh thần đi học đầy đủ, đúng giờ, chuẩn bị bài tốt và nghiêm túc trong giờ học.'
                    ],
                    'I.2' => [
                        'name' => 'Kết quả học tập (Từ loại Xuất sắc: 5đ; Giỏi: 4đ; Khá: 3đ; Trung bình/Khá: 2đ; Có cải thiện so với HK trước: +2đ, tối đa 5đ)',
                        'max_score' => 5,
                        'description' => 'Điểm học tập quy đổi dựa vào kết quả học tập GPA của học kỳ.'
                    ],
                    'I.3' => [
                        'name' => 'Tuân thủ quy chế thi',
                        'max_score' => 5,
                        'description' => 'Không vi phạm quy chế thi cử, kiểm tra học phần.'
                    ],
                    'I.4' => [
                        'name' => 'Tham gia hội thảo, workshop, tọa đàm, chương trình kỹ năng, ngoại khóa về học thuật, khoa học (Lưu ý: Tham gia đầy đủ tất cả các sự kiện được cộng 10đ)',
                        'max_score' => 10,
                        'description' => 'Các buổi chuyên đề phát triển kỹ năng, học thuật do trường/khoa tổ chức.'
                    ],
                    'I.5' => [
                        'name' => 'Tham quan thực tế doanh nghiệp/tập huấn do Nhà trường tổ chức',
                        'max_score' => 2,
                        'description' => 'Tham gia các chuyến đi thực tế doanh nghiệp hoặc các đợt tập huấn chuyên môn.'
                    ],
                    'I.6' => [
                        'name' => 'Tham gia các cuộc thi học thuật do Khoa tổ chức thuộc Nhà trường phát động (Là thí sinh: 1đ; Đạt thành tích từ cấp khoa trở lên: 2đ)',
                        'max_score' => 3,
                        'description' => 'Thi Olympic, thi lập trình, học thuật các cấp.'
                    ],
                ]
            ],
            'II' => [
                'name' => 'Đánh giá về ý thức và kết quả chấp hành quy chế, nội quy, quy định trong nhà trường',
                'max_score' => 20,
                'items' => [
                    'II.1' => [
                        'name' => 'Ý thức tham gia sinh hoạt lớp',
                        'max_score' => 2,
                        'description' => 'Tham gia đầy đủ các buổi sinh hoạt lớp định kỳ/đột xuất.'
                    ],
                    'II.2' => [
                        'name' => 'Đóng học phí đúng hạn',
                        'max_score' => 5,
                        'description' => 'Nộp học phí đúng thời hạn thông báo.'
                    ],
                    'II.3' => [
                        'name' => 'Tham gia bảo hiểm y tế - tai nạn theo quy định',
                        'max_score' => 2,
                        'description' => 'Đăng ký đầy đủ BHYT bắt buộc.'
                    ],
                    'II.4' => [
                        'name' => 'Tham gia Tuần lễ sinh hoạt công dân, Sinh hoạt đầu khóa',
                        'max_score' => 5,
                        'description' => 'Hoàn thành học tập Tuần sinh hoạt công dân học sinh sinh viên.'
                    ],
                    'II.5' => [
                        'name' => 'Thực hiện Khảo sát hoạt động giảng dạy và đánh giá học tập theo quy định nhà trường',
                        'max_score' => 2,
                        'description' => 'Làm khảo sát ý kiến phản hồi giảng dạy đúng hạn.'
                    ],
                    'II.6' => [
                        'name' => 'Không vi phạm pháp luật, chủ trương các cấp, nội quy thông báo khác của nhà trường',
                        'max_score' => 2,
                        'description' => 'Không có biên bản vi phạm pháp luật hoặc nội quy kỷ luật.'
                    ],
                    'II.7' => [
                        'name' => 'Thực hiện các khảo sát khác theo chỉ thị nhà trường',
                        'max_score' => 2,
                        'description' => 'Làm các khảo sát khảo sát ý kiến khác.'
                    ],
                ]
            ],
            'III' => [
                'name' => 'Đánh giá về ý thức và kết quả tham gia các hoạt động chính trị - xã hội, văn hóa, văn nghệ, thể thao, phòng chống các tệ nạn xã hội',
                'max_score' => 15,
                'items' => [
                    'III.1' => [
                        'name' => 'Tham gia các hoạt động chính trị - xã hội, văn hóa, văn nghệ, thể thao (Cấp khoa/trường: 2đ; Từ cấp thành phố trở lên: 3đ; Có giải thưởng: +2đ)',
                        'max_score' => 5,
                        'description' => 'Các giải đấu thể thao, văn nghệ, sự kiện lớn của khoa/trường.'
                    ],
                    'III.2' => [
                        'name' => 'Tham gia các CLB/Đội/Nhóm văn hóa, văn nghệ, thể dục, thể thao, sở thích... (Là thành viên: 1đ; Là Thành viên tích cực: 2đ)',
                        'max_score' => 2,
                        'description' => 'Ghi nhận sinh hoạt thường niên tại CLB.'
                    ],
                    'III.3' => [
                        'name' => 'Là Cộng tác viên hỗ trợ tích cực hoạt động/sự kiện (Tham gia: 1đ; Hỗ trợ tích cực: 3đ)',
                        'max_score' => 3,
                        'description' => 'Làm CTV cho ban tổ chức sự kiện khoa/trường.'
                    ],
                    'III.4' => [
                        'name' => 'Thực hiện nghiêm túc các hoạt động, sự kiện của Nhà trường',
                        'max_score' => 5,
                        'description' => 'Chấp hành điều động tham gia các buổi lễ, sự kiện lớn.'
                    ],
                ]
            ],
            'IV' => [
                'name' => 'Đánh giá ý thức công dân trong quan hệ cộng đồng',
                'max_score' => 25,
                'items' => [
                    'IV.1' => [
                        'name' => 'Chấp hành và tham gia tuyên truyền các chủ trương của Đảng, chính sách, pháp luật của Nhà nước trong cộng đồng',
                        'max_score' => 2,
                        'description' => 'Ý thức công dân gương mẫu tại nơi cư trú và trường học.'
                    ],
                    'IV.2' => [
                        'name' => 'Nhận thức về chủ trương của Đảng, chính sách, pháp luật của Nhà nước',
                        'max_score' => 2,
                        'description' => 'Tham gia các cuộc thi tìm hiểu pháp luật, nghị quyết online.'
                    ],
                    'IV.3' => [
                        'name' => 'Tham gia các hoạt động tình nguyện trung hạn được phát động bởi Nhà trường (Mùa hè Xanh, Xuân tình nguyện, Tiếp sức mùa thi...) (Tham gia: 5đ; Tích cực: 10đ)',
                        'max_score' => 10,
                        'description' => 'Chiến dịch tình nguyện được Nhà trường/Đoàn Hội xác nhận.'
                    ],
                    'IV.4' => [
                        'name' => 'Tham gia hiến máu tình nguyện',
                        'max_score' => 4,
                        'description' => 'Có giấy chứng nhận hiến máu tình nguyện (4đ/lần).'
                    ],
                    'IV.5' => [
                        'name' => 'Quyên góp ủng hộ tình nguyện được phát động bởi Nhà nước, Nhà trường hoặc các Đơn vị chính thống được cấp phép',
                        'max_score' => 2,
                        'description' => 'Đóng góp thiên tai, bão lũ, quỹ khuyến học...'
                    ],
                    'IV.6' => [
                        'name' => 'Được biểu dương, khen thưởng trong tham gia các hoạt động xã hội (có giấy khen, giấy chứng nhận từ ban tổ chức)',
                        'max_score' => 2,
                        'description' => 'Khen thưởng của các tổ chức xã hội, chính quyền địa phương.'
                    ],
                    'IV.7' => [
                        'name' => 'Không vi phạm ATGT, trật tự công cộng',
                        'max_score' => 5,
                        'description' => 'Ý thức tốt khi tham gia giao thông, giữ gìn trật tự xã hội.'
                    ],
                ]
            ],
            'V' => [
                'name' => 'Đánh giá về ý thức và kết quả tham gia phụ trách lớp, các đoàn thể trong nhà trường',
                'max_score' => 10,
                'items' => [
                    'V.1' => [
                        'name' => 'BCH Đoàn trường, BCH Hội sinh viên trường và Ban Điều hành/ Ban Chủ nhiệm Câu Lạc bộ/ Đội/ Nhóm',
                        'max_score' => 6,
                        'description' => 'Giữ chức vụ chủ chốt cấp Trường hoặc chủ nhiệm CLB cấp Trường.'
                    ],
                    'V.2' => [
                        'name' => 'Là Ban Cán sự lớp, BCH Đoàn khoa, BCH LCH SV; BCH CĐ, BCH chi hội lớp',
                        'max_score' => 4,
                        'description' => 'Giữ chức vụ trong ban cán sự lớp hoặc ban chấp hành cấp khoa.'
                    ],
                    'V.3' => [
                        'name' => 'Là Đảng viên/Đối tượng Đảng thuộc Đảng CS Việt Nam',
                        'max_score' => 2,
                        'description' => 'Chứng nhận kết nạp Đảng hoặc hoàn thành lớp Bồi dưỡng nhận thức về Đảng.'
                    ],
                    'V.4' => [
                        'name' => 'Là Đoàn viên TNCS Hồ Chí Minh',
                        'max_score' => 5,
                        'description' => 'Hoàn thành trách nhiệm và tham gia sinh hoạt Đoàn đầy đủ.'
                    ],
                    'V.5' => [
                        'name' => 'Được Đoàn thanh niên, Hội sinh viên Trường biểu dương, khen thưởng (có giấy chứng nhận)',
                        'max_score' => 5,
                        'description' => 'Đạt giấy khen cán bộ Đoàn/Hội xuất sắc hoặc thành tích đóng góp phong trào.'
                    ],
                ]
            ],
            'VI' => [
                'name' => 'Đánh giá vượt khung',
                'max_score' => 10,
                'items' => [
                    'VI.1' => [
                        'name' => 'Sinh viên đạt giải thưởng nghiên cứu khoa học hoặc là thành viên đội tuyển trường đạt giải thưởng các cuộc thi, hội thi, hoạt động từ cấp tỉnh, thành phố trực thuộc trung ương trở lên',
                        'max_score' => 10,
                        'description' => 'Giải NCKH cấp Tỉnh/Thành phố hoặc giải đấu cấp tương đương.'
                    ],
                    'VI.2' => [
                        'name' => 'Sinh viên được biểu dương, khen thưởng từ cấp tỉnh, thành phố trực thuộc trung ương trở lên về: công tác giữ gìn trật tự xã hội, đấu tranh bảo vệ pháp luật, cứu người; danh hiệu “Sinh viên 5 tốt”; thành tích xuất sắc trong học tập và làm theo tấm gương đạo đức Hồ Chí Minh',
                        'max_score' => 10,
                        'description' => 'Khen thưởng chuyên đề/danh hiệu cao quý cấp Tỉnh trở lên.'
                    ],
                    'VI.3' => [
                        'name' => 'Sinh viên nhận bằng khen cấp trung ương về công tác Đoàn Thanh niên, Hội Sinh viên, Hội Liên hiệp thanh niên',
                        'max_score' => 10,
                        'description' => 'Bằng khen của Trung ương Đoàn / Trung ương Hội.'
                    ],
                    'VI.4' => [
                        'name' => 'Sinh viên đạt giải thưởng nghiên cứu khoa học cấp trường',
                        'max_score' => 5,
                        'description' => 'Giải khuyến khích/ba/nhì/nhất NCKH cấp Trường.'
                    ],
                    'VI.5' => [
                        'name' => 'Sinh viên có hoàn cảnh gia dịch đặc biệt khó khăn nhưng tích cực trong học tập, rèn luyện',
                        'max_score' => 5,
                        'description' => 'Xác nhận gia đình nghèo/cận nghèo/khó khăn vượt khó học tốt.'
                    ],
                ]
            ]
        ];
    }
}
