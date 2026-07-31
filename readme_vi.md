> 🌐 **Ngôn ngữ:** 🇻🇳 Tiếng Việt (hiện tại) · [🇬🇧 English](./readme.md)

# ShopDiscount — Plugin mã giảm giá (Coupon) cho GP247 / S-Cart

## Giới thiệu
ShopDiscount là plugin giúp bạn tạo và quản lý **mã giảm giá (coupon)** cho website bán hàng chạy trên
GP247 / S-Cart 2.0. Người quản trị tạo các mã giảm giá; khách hàng nhập mã ở **bước thanh toán** để được
giảm tiền cho đơn hàng. Tài liệu này dành cho chủ shop và người quản trị (không cần rành lập trình): đọc
xong bạn sẽ biết cách cài, tạo mã giảm giá, và hiểu khách dùng mã như thế nào.

## Tính năng chính
- **Hai kiểu giảm giá:**
  - **Point** — giảm một **số tiền cố định** (ví dụ giảm 50.000đ).
  - **Percent (%)** — giảm theo **phần trăm** giá trị đơn (ví dụ giảm 10%).
- **Giới hạn số lần dùng** cho mỗi mã (ví dụ chỉ dùng được 100 lần).
- **Yêu cầu đăng nhập** (tuỳ chọn): chỉ khách đã đăng nhập mới dùng được mã, và mỗi khách chỉ dùng 1 lần.
- **Ngày hết hạn** cho mã.
- **Bật / tắt** từng mã bất cứ lúc nào.
- **Hỗ trợ nhiều cửa hàng (multi-store):** chọn mã áp dụng cho cửa hàng nào.
- **Áp mã ngay tại trang thanh toán:** khách nhập mã → thấy dòng giảm giá và tổng tiền cập nhật ngay
  (không phải tải lại trang).

## Yêu cầu hệ thống
| Thành phần | Yêu cầu |
| --- | --- |
| GP247 Core | **2.0 trở lên** |
| Gói bắt buộc | **gp247/shop** (dành cho website bán hàng) |
| Phiên bản plugin | 2.0 |

> Nếu website chưa cài `gp247/shop`, plugin sẽ không hoạt động đúng (nó phục vụ cho chức năng bán hàng).

## Cài đặt
Bạn có 3 cách cài (giống mọi extension của GP247). Cách nhanh nhất khi bạn đã có sẵn thư mục plugin:

1. Chép thư mục plugin vào đúng vị trí trên máy chủ:

   ```
   app/GP247/Plugins/ShopDiscount
   ```

2. Đăng nhập **admin** bằng tài khoản có quyền quản lý extension.
3. Vào menu **Plugin**, tìm **ShopDiscount** trong danh sách rồi bấm **Cài đặt (Install)**.
   Nếu thành công, bạn sẽ thấy thông báo cài đặt thành công và một mục menu mới tên **Discount**
   (Coupon/Discount) xuất hiện trong admin.
4. (Nếu admin vẫn hiển thị cũ) mở **Terminal** tại thư mục gốc website, chạy đúng dòng sau rồi Enter để
   xoá cache:

   ```
   php artisan optimize:clear
   ```

> Muốn cài theo cách **Online (kho extension)** hoặc **Import file .zip**? Xem hướng dẫn cài đặt chung:
> [Hướng dẫn cài đặt Plugin & Template cho GP247](https://github.com/gp247net/gp247-docs/blob/main/extension/install-extension_vi.md).

## Cách dùng (1): Người quản trị tạo mã giảm giá
1. Trong admin, mở menu **Discount** (Coupon/Discount).
2. Bấm nút **Thêm mới (Add new)**.
3. Điền các ô:
   - **Code** — mã khách sẽ nhập (ví dụ `SALE10`). Chỉ dùng chữ, số và các ký tự `. - _`.
   - **Reward** — giá trị giảm: nếu chọn *Point* thì đây là **số tiền**; nếu chọn *Percent* thì đây là **số phần trăm**.
   - **Type** — chọn **Point** (giảm số tiền) hoặc **Percent (%)** (giảm theo %).
   - **Description** — ghi chú cho mã (không bắt buộc).
   - **Limit** — số lần tối đa mã được dùng.
   - **Login require** — tích vào nếu bắt buộc khách phải đăng nhập mới dùng được.
   - **Expires** — ngày hết hạn (chọn từ lịch).
   - **Status** — tích để **bật** mã (bỏ tích là tắt).
   - **(Chỉ khi chạy nhiều cửa hàng)** chọn **cửa hàng** áp dụng mã.
4. Bấm **Gửi (Submit)** để lưu. Mã mới xuất hiện trong danh sách bên phải; cột **Used** cho biết mã đã
   được dùng bao nhiêu lần trên tổng số cho phép (ví dụ `3/100`).

## Cách dùng (2): Khách hàng áp mã khi thanh toán
1. Khách thêm sản phẩm vào giỏ và tiến hành **Thanh toán (Checkout)**.
2. Ở **bước xác nhận đơn**, khách nhập mã vào ô **coupon** rồi bấm **Áp dụng (Apply)**.
3. Nếu mã hợp lệ, một dòng **giảm giá** xuất hiện và **tổng tiền cập nhật ngay**. Muốn bỏ mã, bấm **Gỡ (Remove)**.

Mã chỉ được chấp nhận khi: còn hiệu lực (đang bật, chưa hết hạn), **còn lượt dùng**, đúng **cửa hàng** áp
dụng, và (nếu mã yêu cầu) khách **đã đăng nhập** và chưa dùng mã này trước đó.

## Bật / tắt / gỡ plugin
- Vào admin → **Plugin** → **ShopDiscount**.
- **Enable / Disable** để bật hoặc tắt tạm thời (không mất dữ liệu mã đã tạo).
- **Uninstall** để gỡ hẳn — thao tác này **xoá dữ liệu mã giảm giá** của plugin, hãy cân nhắc trước khi làm.

## Hỏi & Đáp (Q&A)
**Câu 1: Khách nhập mã giảm giá ở đâu?**
Ở **bước xác nhận** trong trang thanh toán (checkout) có ô nhập mã coupon. Nhập mã rồi bấm Áp dụng.

**Câu 2: "Point" và "Percent" khác nhau thế nào?**
*Point* giảm một **số tiền cố định** (ví dụ 50.000đ). *Percent* giảm theo **phần trăm** giá trị đơn
(ví dụ 10%). Ô **Reward** là con số tương ứng cho kiểu bạn chọn.

**Câu 3: Cài xong nhưng không thấy menu "Discount" trong admin?**
Chạy `php artisan optimize:clear` để xoá cache rồi tải lại trang admin. Cũng kiểm tra plugin đã ở trạng
thái **đã cài + đang bật (Enable)** trong menu Plugin chưa.

**Câu 4: Khách áp mã nhưng không thấy giảm tiền?**
Kiểm tra mã còn **bật (Status)**, **chưa hết hạn**, **còn lượt dùng** (Used chưa đạt Limit), và đúng
**cửa hàng**. Nếu mã bật "Login require" thì khách phải **đăng nhập** mới dùng được.

**Câu 5: Một mã dùng được bao nhiêu lần?**
Bằng số bạn nhập ở ô **Limit**. Khi số lần dùng (Used) đạt tới Limit, mã báo hết lượt và không dùng được nữa.

**Câu 6: Gỡ (Uninstall) plugin có làm mất các mã đã tạo không?**
Có. Uninstall sẽ xoá dữ liệu mã giảm giá. Nếu chỉ muốn tạm ngừng, hãy dùng **Disable** thay vì Uninstall.

**Câu 7: Tôi chạy nhiều cửa hàng thì mã áp dụng thế nào?**
Khi website bật chế độ multi-store, lúc tạo mã bạn chọn **cửa hàng** áp dụng. Mã chỉ có hiệu lực ở cửa hàng đã chọn.

**Câu 8: Plugin có cần gp247/shop không?**
Có. ShopDiscount phục vụ chức năng bán hàng nên **bắt buộc** website đã cài `gp247/shop`.

**Câu 9: "Login require" nghĩa là gì?**
Nghĩa là chỉ khách **đã đăng nhập** mới dùng được mã đó, và mỗi tài khoản chỉ dùng mã đó **một lần**.

**Câu 10: Tôi đang dùng bản plugin cũ (1.x), nâng cấp thế nào?**
Bản 2.0 này viết cho GP247 Core 2.0. GP247 2.0 không hỗ trợ nâng cấp trực tiếp từ 1.x — hãy cài trên nền
Core 2.0. Cấu trúc dữ liệu mã vẫn giữ nguyên như trước.

---

<sub>📅 **Cập nhật lần cuối:** 2026-07-31 · ✍️ **Tác giả (Author):** GP247</sub>
