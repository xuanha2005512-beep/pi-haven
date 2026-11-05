# PI HAVEN – SETUP & DEPLOYMENT GUIDE
## Hướng dẫn thiết lập và triển khai hệ thống Pi Haven

**Dự án:** Hệ thống đặt phòng trực tuyến và quản lý tập trung Pi Haven Homestay  
**Vị trí trong GitHub:** `docs/deployment/SETUP_GUIDE.md`  
**Phiên bản tài liệu:** 2.0 – cập nhật từ minh chứng thực tế ngày 05/11/2026  
**Phạm vi:** Sprint 3 – Repository, Database/Schema và Environment Readiness  
**Môi trường ghi nhận:** Website WordPress tại `https://pihaven.io.vn`  
**Trạng thái:** Đã xác minh một phần hạ tầng; chưa nghiệm thu đầy đủ nghiệp vụ MVP.

> **Quy ước bằng chứng:**  
> - **Đã xác minh (ảnh):** Thông tin xuất hiện trực tiếp trong ảnh do nhóm cung cấp.  
> - **Đã cài/kích hoạt, chưa kiểm thử chức năng:** Có plugin hoặc môi trường, nhưng chưa có bằng chứng nghiệp vụ hoạt động.  
> - **Chưa xác minh:** Chưa có ảnh/kết quả kiểm thử đủ để kết luận.  
> - **Thiết kế mục tiêu:** Chức năng/tích hợp dự kiến cho hệ thống hoàn chỉnh; không đồng nghĩa đã xây dựng.

---

## 1. Tổng quan dự án và kiến trúc triển khai

Pi Haven xây dựng website trên **WordPress**, chạy trên hosting **AZDIGI**, sử dụng tên miền **`pihaven.io.vn`** được quản lý tại **Tenten** và cơ sở dữ liệu quan hệ **MariaDB**. Dự án được tổ chức theo bốn lớp kiến trúc trong **Template 4 – System Architecture**:

| Layer | Vai trò trong Pi Haven | Triển khai / trạng thái |
|---|---|---|
| Client / UI | Customer Website và Admin Portal | Giao diện WordPress và khu vực quản trị; đã có bằng chứng truy cập quản trị |
| Application / Backend | WordPress Core, theme, plugin và logic nghiệp vụ | WordPress và một số plugin đã cài; các chức năng tùy chỉnh cần kiểm thử |
| Database | Lưu trữ thông tin nội dung, phòng, booking và dữ liệu nghiệp vụ | MariaDB đã được WordPress kết nối; ánh xạ ERD → bảng vật lý chưa xác minh |
| External Services | Gửi email, thanh toán và Smart Lock | SMTP có plugin; Payment Gateway/Smart Lock API là **kiến trúc mục tiêu**, MVP dự kiến mô phỏng |

### 1.1. Phân biệt hệ thống mục tiêu và MVP

- **Kiến trúc mục tiêu:** Tích hợp Payment Gateway bên thứ ba, Smart Lock API và cơ chế thông báo phục vụ quy trình lưu trú thực tế.
- **MVP:** Dự kiến QR thanh toán/xác nhận giả lập và tích hợp Smart Lock mô phỏng; không được ghi là đã vận hành nếu chưa có demo/kiểm thử.
- **Room Hold 10 phút, chống đặt trùng, booking nhiều phòng:** Là yêu cầu nghiệp vụ cần kiểm tra riêng. Việc cài plugin Hotel Booking Lite **chưa chứng minh** các quy tắc này đã được thực hiện đúng.

---

## 2. Thông tin môi trường đã xác minh

### 2.1. Tên miền, DNS và HTTPS

| Thông tin | Giá trị quan sát từ ảnh | Kết luận |
|---|---|---|
| Tên miền | `pihaven.io.vn` | Đã có trong trang quản lý Tenten và cấu hình WordPress |
| Nhà quản lý tên miền | Tenten | Đã xác minh qua trang quản lý DNS |
| DNS A (`@`) | `45.252.250.44` | Bản ghi được hiển thị **Hoạt động** |
| DNS CNAME (`www`) | `pihaven.io.vn` | Bản ghi được hiển thị **Hoạt động** |
| Nameserver | `ns-b1.tenten.vn`, `ns-b2.tenten.vn`, `ns-b3.tenten.vn` | Theo ảnh Tenten |
| Ngày hết hạn tên miền hiển thị | 27/10/2027 | Theo trang quản lý Tenten tại thời điểm chụp |
| WordPress Address / Site Address | `https://pihaven.io.vn` | Hai địa chỉ trùng nhau |
| WordPress báo sử dụng HTTPS | Có | Theo **Tools → Site Health → Info → WordPress** |
| Tình trạng cấp chứng chỉ SSL cụ thể | Chưa xác minh | Ảnh cPanel ở trang **Issue a certificate**, không phải bằng chứng chứng chỉ đã được phát hành tại trang đó |

**Nhận định:** Domain đã được thiết lập và WordPress ghi nhận HTTPS. Để xác nhận đầy đủ SSL (đơn vị cấp, thời hạn, bảo vệ `www`), cần bổ sung ảnh chi tiết chứng chỉ trong trình duyệt hoặc **SSL/TLS Status** của hosting.

### 2.2. WordPress và Web Server

| Hạng mục | Cấu hình thực tế | Nguồn |
|---|---|---|
| WordPress | **7.1.4** | Site Health → WordPress (theo ảnh nhóm cung cấp) |
| Ngôn ngữ website | `vi` | Site Health → WordPress |
| Múi giờ | `Asia/Ho_Chi_Minh` | Site Health → WordPress |
| Loại môi trường WordPress | `production` | Site Health → WordPress |
| WordPress Multisite | Không | Site Health → WordPress |
| Đăng ký thành viên công khai | Không | Site Health → WordPress |
| Ngăn công cụ tìm kiếm | Có | Site Health → WordPress; cần xem lại khi website chính thức công khai |
| Cấu trúc Permalink | `/%postname%/` | Site Health → WordPress |
| Số tài khoản người dùng WordPress hiển thị | 1 | Site Health → WordPress |
| Hệ điều hành / kiến trúc | Linux x86_64 | Site Health → Server |
| Web server | **LiteSpeed** | Site Health → Server |
| PHP | **8.3.36** | Site Health → Server |
| PHP SAPI | `litespeed` | Site Health → Server |
| PHP memory limit | **512M** | Site Health → Server |
| PHP time limit | **30 giây** | Site Health → Server |
| PHP max input vars | **1000** | Site Health → Server |
| Upload max file size | **512M** | Site Health → Server |
| PHP post max size | **256M** | Site Health → Server |
| Opcode cache | Hiển thị tắt | Site Health → Server |

**Lưu ý:** Những thông số trên là ảnh chụp cấu hình tại thời điểm cung cấp, không bảo đảm bất biến sau khi hosting hoặc WordPress cập nhật. Không tự thay đổi giới hạn PHP chỉ để phù hợp tài liệu nếu hệ thống chưa có nhu cầu.

### 2.3. Cơ sở dữ liệu

| Thông tin | Giá trị đã xác minh | Ghi chú |
|---|---|---|
| DBMS | **MariaDB 10.6.28** | Site Health → Database |
| PHP database extension | `mysqli` | Site Health → Database |
| PHP client library | `mysqlnd 8.3.36` | Site Health → Database |
| DB host | `localhost` | Tồn tại trong cấu hình kết nối nội bộ |
| Encoding | `utf8mb4` | Site Health → Database |
| Collation | `utf8mb4_unicode_520_ci` | Site Health → Database |
| Prefix bảng | Đã có prefix riêng | **Không ghi giá trị thực vào bản công khai** |
| Tên database / tài khoản | Đã hiển thị trong ảnh Site Health | **Không sao chép vào GitHub** |

WordPress đang truy cập được khu vực quản trị và hiển thị thông tin DBMS, vì vậy **kết nối cơ sở dữ liệu ở mức vận hành WordPress có bằng chứng**. Tuy nhiên, chưa có ảnh **phpMyAdmin → Structure** để xác nhận danh sách bảng, bảng do Hotel Booking Lite tạo hoặc quan hệ với Logical ERD.

### 2.4. Tài nguyên hosting

| Chỉ số cPanel | Sử dụng / hạn mức | Tỷ lệ |
|---|---:|---:|
| Disk Usage | 266,13 MB / 10 GB | 2,6% |
| Entry Processes | 1 / 80 | 1,25% |
| Number of Processes | 1 / 120 | 0,83% |
| Physical Memory | 5,05 MB / 3 GB | 0,16% |
| Database Disk Usage | 1,42 MB / 9,74 GB | 0,01% |
| Bandwidth | 28,37 MB / **chưa thấy hạn mức** | Không tính phần trăm |

Các con số là **ảnh chụp một thời điểm**, chỉ phản ánh mức sử dụng tài nguyên khi đo. Chúng không chứng minh website chịu được tải lớn, nhiều booking đồng thời hoặc đã hoàn thành kiểm thử hiệu suất.

---

## 3. Danh sách plugin thực tế và trạng thái

Danh sách sau được ghi theo ảnh **Plugins → Installed Plugins** ngày 05/11/2026. Trạng thái **Active** được xác định khi dòng plugin hiển thị tùy chọn **“Vô hiệu hóa”**; **Inactive** khi hiển thị **“Kích hoạt”**.

| Plugin | Version | Trạng thái trong ảnh | Vai trò / lưu ý |
|---|---|---|---|
| Hotel Booking Lite (MotoPress) | **6.4.0** | **Active** | Nền tảng quản lý chỗ ở và đặt phòng; chưa kiểm thử multi-room booking, Hold 10 phút và chống xung đột |
| UpdraftPlus – Sao lưu/Khôi phục | **1.26.14** | **Active** | Có công cụ sao lưu; chưa có bằng chứng backup thành công hay restore test |
| Wordfence Security | **9.0.2** | **Active** | Plugin bảo mật đã kích hoạt; ảnh trước có thông báo **cài đặt Wordfence chưa hoàn tất** nên phải kiểm tra license, firewall và quét |
| WP Mail SMTP | **4.10.3** | **Active** | Có công cụ SMTP; **chưa xác minh** cấu hình mailer hay email gửi thử |
| Fluent Forms | **6.2.17** | **Inactive** | Plugin tạo form hỗ trợ; cần kích hoạt/cấu hình khi chọn để làm `SUPPORT_REQUEST` |
| Akismet Anti-spam | **5.7.2** | **Inactive** | Plugin chống spam; chưa sử dụng |
| Hello Dolly | **1.7.2** | **Inactive** | Plugin mẫu, không phục vụ nghiệp vụ Pi Haven |

### 3.1. Khuyến nghị xử lý plugin

1. **Hotel Booking Lite:** Giữ một nền tảng booking chính, hoàn thành màn hình/trang mặc định nếu phù hợp và thực hiện thử nghiệm nghiệp vụ trước khi nhập nhiều dữ liệu.
2. **UpdraftPlus:** Thiết lập nơi lưu bản sao lưu an toàn; chụp lịch backup và một lần backup thành công; thử khôi phục trên staging trước khi kết luận đạt.
3. **Wordfence:** Hoàn thành cấu hình Free License và kiểm tra trạng thái bảo vệ; tránh cài thêm firewall plugin chồng chéo khi chưa hiểu rõ.
4. **WP Mail SMTP:** Chọn mailer phù hợp, thiết lập quyền xác thực bằng thông tin bí mật ngoài GitHub và chạy **Send Test Email**.
5. **Fluent Forms:** Chỉ kích hoạt sau khi chốt phương án form hỗ trợ. Tạo form và kiểm thử đường lưu dữ liệu/ thông báo; không mặc nhiên form đã nối với `SUPPORT_REQUEST` trong Logical ERD.
6. **Akismet/Hello Dolly:** Có thể để inactive; chỉ gỡ plugin không dùng sau khi kiểm tra và sao lưu.

**Theme đang kích hoạt:** **Chưa xác minh** do chưa có ảnh **Appearance → Themes**. Không điền tên theme theo suy đoán.

---

## 4. Cấu trúc Repository và vị trí tài liệu

Repository dự kiến/đang sử dụng: `pi-haven` (GitHub, nhánh `main`). Thư mục Deployment có thể chứa:

```text
pi-haven/
├── README.md
├── docs/
│   ├── architecture/
│   ├── database/
│   └── deployment/
│       ├── README.md
│       └── SETUP_GUIDE.md
├── wordpress/
│   ├── TECHNOLOGY_INVENTORY.md        # tạo/cập nhật theo plugin thực tế
│   └── wp-content/
│       ├── themes/
│       └── plugins/
├── database/
│   ├── README.md
│   └── SCHEMA_STATUS.md              # tài liệu trạng thái schema
└── demo/
    └── screenshots/                  # minh chứng đã che dữ liệu nhạy cảm
```

**Nguyên tắc:** GitHub quản lý **tài liệu, phiên bản và mã nguồn được phép phân phối**. Hosting AZDIGI chạy website; WordPress/MariaDB lưu cấu hình và dữ liệu runtime. Không cần tải toàn bộ WordPress Core, file chứa secret, database khách thật hoặc plugin thương mại không được quyền phân phối lên GitHub.

---

## 5. Quy trình kiểm tra và cấu hình website đang triển khai

### 5.1. Kiểm tra domain và chứng chỉ HTTPS

1. Tại **Tenten → DNS Records**, xác nhận bản ghi `A @` và `CNAME www` đúng với cấu hình hosting.
2. Trong WordPress **Settings → General**, xác nhận cả hai URL là `https://pihaven.io.vn` (đã có bằng chứng).
3. Truy cập `https://pihaven.io.vn` bằng cửa sổ trình duyệt mới và mở thông tin chứng chỉ để xác nhận chứng chỉ hợp lệ, chưa hết hạn và đúng hostname.
4. Kiểm tra `https://www.pihaven.io.vn` nếu website sử dụng/điều hướng từ địa chỉ `www`.
5. Không cấp mới một chứng chỉ không cần thiết chỉ vì đang ở trang **Issue a certificate**; trước hết xem trạng thái SSL hiện tại.

### 5.2. Kiểm tra cấu hình theme và plugin

1. WordPress **Appearance → Themes**: ghi tên theme active, phiên bản, theme cha/child theme nếu có; chụp ảnh minh chứng.
2. WordPress **Plugins → Installed Plugins**: đối chiếu bảng plugin ở Mục 3.
3. Hotel Booking Lite: kiểm tra **Accommodation / Booking**, cấu hình các trang search results/checkout và dữ liệu phòng mẫu.
4. WP Mail SMTP: cấu hình mailer, kiểm tra test email, lưu kết quả; không đưa thông tin xác thực lên tài liệu.
5. Wordfence: xác minh hoàn tất setup và status quét/bảo vệ.
6. UpdraftPlus: thiết lập backup và quan sát kết quả thực tế; không thử restore trực tiếp trên website production.

### 5.3. Kiểm tra cơ sở dữ liệu

1. Mở **Tools → Site Health → Info → Database** và ghi nhận phiên bản MariaDB; thông tin này **đã có bằng chứng**.
2. Vào **cPanel → phpMyAdmin → Structure** (chỉ xem), chụp danh sách **tên bảng** đã che các thông tin không cần thiết.
3. Kiểm tra có những bảng dữ liệu WordPress chuẩn và những bảng do plugin tạo (nếu có).
4. Tạo bảng ánh xạ giữa Logical ERD Pi Haven (`BRANCH`, `ROOM`, `BOOKING`, `BOOKING_ROOM`, `ROOM_HOLD`, `PAYMENT`, v.v.) với **cách lưu vật lý thực tế**: bảng plugin, posts/meta hoặc bảng tùy chỉnh.
5. **Không chạy CREATE/DROP/ALTER TABLE lên môi trường production** khi chưa chốt schema, có backup và phương án rollback.

### 5.4. Kiểm tra các trang trên website

Kiểm tra lần lượt trang chủ, danh sách cơ sở/phòng, trang chi tiết phòng, trang tìm phòng, form đặt phòng, khu vực quản trị và các trang hỗ trợ **nếu đã được nhóm tạo**. Với mỗi trang, ghi URL, kết quả hiển thị, ảnh chứng minh và lỗi phát hiện.

---

## 6. Hướng dẫn tái triển khai Pi Haven trên môi trường mới

> **Đây là quy trình đề xuất**, chưa phải bằng chứng nhóm đã cài lại thành công ở staging.

### 6.1. Chuẩn bị

- Chọn môi trường thử nghiệm có hỗ trợ **WordPress + PHP + MariaDB/MySQL**.
- Sao lưu **toàn bộ database**, `wp-content/uploads`, theme/plugin tùy chỉnh và các cài đặt cần thiết; giữ bản sao lưu ở vị trí an toàn.
- Lập danh sách phiên bản plugin/theme và thiết lập cấu hình đặc thù.
- Sử dụng tài khoản và mật khẩu riêng cho môi trường thử nghiệm, không tái sử dụng dữ liệu khách thật nếu không cần.

### 6.2. Triển khai theo hai phương án

**A. Khôi phục từ backup kiểm chứng:**

1. Tạo WordPress/staging và database riêng.
2. Khôi phục database và thư mục website từ bản sao lưu phù hợp.
3. Khi thay URL, dùng công cụ WordPress hỗ trợ xử lý dữ liệu serialized; tránh tìm–thay SQL thô.
4. Xác minh trang, link, hình ảnh, plugin, quản trị, quyền truy cập và email thử.
5. Ghi kết quả restore test (ngày, người thực hiện, kết quả, lỗi và cách xử lý).

**B. Cài mới từ Repository và danh mục plugin:**

1. Cài WordPress phiên bản tương thích với môi trường chạy.
2. Cài theme và các plugin theo **Technology Inventory**; áp dụng phiên bản đã kiểm thử.
3. Đưa mã nguồn theme/child theme/plugin tùy chỉnh (nếu nhóm thực sự phát triển) lên staging.
4. Tạo cấu hình trang, menu, URL, email, phòng mẫu, chính sách giá và quyền quản trị.
5. Nếu có SQL/migration tùy chỉnh, chạy theo tài liệu và bản sao lưu đã kiểm tra.
6. Chạy các kiểm thử ở Mục 8; ghi lại các sai khác so với production.

---

## 7. Cấu hình nghiệp vụ Pi Haven và trạng thái hiện tại

| Nhóm chức năng | Kiến trúc/nguồn triển khai | Đánh giá từ ảnh hiện có |
|---|---|---|
| Room Catalog & Availability | Hotel Booking Lite + cấu hình/tùy chỉnh | Plugin đã Active; **chưa có ảnh dữ liệu phòng hoặc test search** |
| Booking Core / Multi-room | Plugin booking + logic bổ sung nếu cần | Chưa có kết quả kiểm thử một booking nhiều phòng |
| Room Hold 10 phút | Logic backend có thời hạn, kiểm tra xung đột và tác vụ dọn dẹp | Chưa có mã nguồn/test chứng minh; không đồng nhất với pending booking mặc định của plugin |
| Payment Processing | Target: Third-party Payment Gateway; MVP: QR giả lập | Chưa có demo QR hoặc trạng thái giao dịch |
| Stay & Smart Lock Integration | Target: Smart Lock API; MVP: mô phỏng | Chưa có bằng chứng tích hợp hoặc giả lập đã chạy |
| Admin Management | WordPress admin và quyền nghiệp vụ | Có bằng chứng truy cập WP Admin; **chưa xác minh phân quyền nghiệp vụ** |
| Support / Review / Chatbot | Form hỗ trợ, review và FAQ theo kịch bản | Fluent Forms đang Inactive; chưa có demo chức năng |
| Email Service | WP Mail SMTP kết nối mailer | Plugin Active; chưa có test email |
| Backup / Restore | UpdraftPlus | Plugin Active; chưa có backup/restore kết quả |
| Security | Wordfence, HTTPS và cấu hình quyền | Wordfence Active; cần xác minh setup/license/tình trạng bảo vệ |

**Nguyên tắc Room Hold:** Nếu dùng WP-Cron để dọn các Hold quá hạn, không được chỉ dựa vào cron làm điều kiện xác định phòng trống. Backend vẫn phải kiểm tra thời hạn Hold và tính chồng lấn ngay khi tìm phòng, tạo Hold hoặc xác nhận booking, nhằm giảm nguy cơ đặt trùng.

---

## 8. Checklist kiểm thử môi trường và MVP

Ký hiệu: **Quan sát đạt** (có bằng chứng giới hạn) / **Chưa kiểm thử** / **Không đạt**. Không gán “Đạt” nghiệp vụ nếu chỉ nhìn thấy plugin đã kích hoạt.

| ID | Nội dung kiểm tra | Bằng chứng hiện có | Trạng thái |
|---|---|---|---|
| ENV-01 | Website WordPress sử dụng đúng domain/URL | Settings General và Site Health | Quan sát đạt |
| ENV-02 | WordPress nhận biết HTTPS | Site Health ghi “Có” | Quan sát đạt; cần kiểm tra chi tiết chứng chỉ |
| ENV-03 | DNS A và CNAME được cấu hình | Tenten DNS Records | Quan sát đạt |
| ENV-04 | WordPress kết nối DBMS | Site Health nhận diện MariaDB | Quan sát đạt ở mức WordPress |
| ENV-05 | Web server/PHP được xác định | Site Health Server | Quan sát đạt |
| ENV-06 | Theme được xác định | Chưa có ảnh Themes | Chưa kiểm thử |
| ENV-07 | Plugin cốt lõi được cài và kích hoạt | Installed Plugins | Quan sát đạt về trạng thái Active |
| ENV-08 | SMTP gửi email thực tế | Chưa có Test Email | Chưa kiểm thử |
| ENV-09 | Wordfence hoàn tất setup và scan | Chưa có trạng thái setup/scan | Chưa kiểm thử |
| ENV-10 | UpdraftPlus có backup hợp lệ | Chưa có lịch sử backup | Chưa kiểm thử |
| ENV-11 | Backup có thể khôi phục trên staging | Chưa có restore log | Chưa kiểm thử |
| DB-01 | Danh sách bảng WordPress/Plugin | Chưa có phpMyAdmin Structure | Chưa kiểm thử |
| BOOK-01 | Tìm phòng theo cơ sở, ngày, số khách | Chưa có demo | Chưa kiểm thử |
| BOOK-02 | Đặt nhiều phòng trong một booking | Chưa có demo | Chưa kiểm thử |
| BOOK-03 | Room Hold đúng 10 phút | Chưa có demo | Chưa kiểm thử |
| BOOK-04 | Chống booking/hold chồng lấn | Chưa có kiểm thử đồng thời | Chưa kiểm thử |
| PAY-01 | QR thanh toán giả lập và xác nhận | Chưa có demo | Chưa kiểm thử |
| STAY-01 | Smart Lock mô phỏng và check-in/out | Chưa có demo | Chưa kiểm thử |
| SUP-01 | Form hỗ trợ và lưu yêu cầu | Chưa có demo | Chưa kiểm thử |
| SEC-01 | Phân quyền nhân viên đúng vai trò | Chưa có kiểm thử | Chưa kiểm thử |

---

## 9. Minh chứng và lưu trữ an toàn

### 9.1. Bộ ảnh nên lưu sau khi che thông tin nhạy cảm

| Mã ảnh | Nội dung | Đã cung cấp trong cuộc trao đổi? |
|---|---|---|
| E01 | WordPress → Site Health → WordPress | Có |
| E02 | WordPress → Site Health → Server | Có |
| E03 | WordPress → Site Health → Database | Có, cần **che tên DB/user và prefix nếu xuất bản** |
| E04 | WordPress → Plugins → Installed Plugins | Có |
| E05 | WordPress → Settings → General | Có, cần **che email quản trị** |
| E06 | Tenten → DNS Records | Có |
| E07 | cPanel → Issue a certificate / SSL | Có; **không thay thế ảnh SSL Status** |
| E08 | cPanel → Statistics | Có số liệu người dùng cung cấp |
| E09 | WordPress → Appearance → Themes | **Chưa có** |
| E10 | phpMyAdmin → Structure | **Chưa có** |
| E11 | SMTP → Test Email | **Chưa có** |
| E12 | UpdraftPlus → lịch sử backup/restore test | **Chưa có** |

**Không đăng lên Repository:** mật khẩu, token, API key, cấu hình xác thực mailer, tài khoản DB, `wp-config.php`, backup DB có khách thật, mã khóa Smart Lock hoặc ảnh chứa dữ liệu cá nhân. Với ảnh E03/E05 nên tạo bản đã che trước khi upload; không đăng nguyên ảnh thu thập.

### 9.2. Thao tác cập nhật GitHub

1. Vào `pi-haven → docs → deployment`.
2. Mở `SETUP_GUIDE.md`, chọn **Edit** để thay nội dung hoặc **Add file → Upload files** nếu chưa có file.
3. Dùng commit message: `Update verified Pi Haven environment and deployment guide`.
4. Lưu kết quả vào nhánh `main`.
5. Kiểm tra phần Preview Markdown, bảng, đường dẫn và thông tin nhạy cảm trước khi chia sẻ với giảng viên.

---

## 10. Những thông tin còn thiếu để chốt Environment Readiness

| Ưu tiên | Việc cần làm | Minh chứng cần bổ sung |
|---|---|---|
| Cao | Xác minh theme active và phiên bản | Appearance → Themes |
| Cao | Xác minh Wordfence đã hoàn tất cấu hình | Wordfence dashboard / license state / scan |
| Cao | Kiểm thử gửi email | WP Mail SMTP → Email Test (ẩn địa chỉ cá nhân) |
| Cao | Thiết lập và ghi nhận backup | UpdraftPlus backup log; thử restore trên staging |
| Cao | Kiểm tra danh sách bảng vật lý | phpMyAdmin → Structure (không mở dữ liệu hàng) |
| Trung bình | Xác minh chứng chỉ SSL và `www` | Chi tiết HTTPS certificate hoặc SSL/TLS Status |
| Trung bình | Ghi rõ theme/plugin và các trang đã tạo | Inventory + danh sách trang, screenshots |
| Trung bình | Kiểm thử tìm phòng/booking | Demo nghiệp vụ Hotel Booking Lite |
| Theo tiến độ MVP | Room Hold, chống trùng, QR, Smart Lock, chatbot | Test case và screenshot/video thực thi |

---

## 11. Kết luận mức độ sẵn sàng tính đến 05/11/2026

**Đã có bằng chứng cơ sở hạ tầng:** Domain Pi Haven và DNS được cấu hình tại Tenten; WordPress đã cài và có thông tin Site Health; web server LiteSpeed, PHP 8.3.36, MariaDB 10.6.28; website WordPress nhận biết HTTPS; plugin Hotel Booking Lite, UpdraftPlus, Wordfence và WP Mail SMTP được kích hoạt.

**Chưa đủ bằng chứng để kết luận hoàn thiện môi trường kỹ thuật:** Chưa xác minh theme đang dùng, cấu trúc bảng WordPress thực tế, khả năng gửi email, tình trạng cấu hình bảo mật Wordfence, kết quả backup/restore và chứng chỉ SSL chi tiết.

**Chưa đủ bằng chứng để nghiệm thu các chức năng cốt lõi của MVP:** Chưa có kiểm thử booking nhiều phòng, Room Hold 10 phút, xung đột đặt phòng, thanh toán QR giả lập, Smart Lock mô phỏng, hỗ trợ khách hoặc dashboard.

Do đó, **tài liệu Deployment đã được cập nhật theo hạ tầng thực tế**, còn kết quả **Environment Readiness Review** cần tiếp tục hoàn thiện theo checklist và chứng cứ cụ thể. Không đồng nhất việc cài plugin với việc đã triển khai xong nghiệp vụ.
