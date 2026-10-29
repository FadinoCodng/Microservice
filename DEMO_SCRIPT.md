# Panduan & Skrip Presentasi Video Demo UTS Microservices (Order Service)

> **Durasi Rekomendasi:** 8 – 15 Menit  
> **Gaya Pembawaan:** Sebagai seorang *Tech Lead / Senior Software Engineer* yang menjelaskan arsitektur, trade-offs, serta pembuktian reliabilitas sistem.

---

## 1. Pembukaan (±1 Menit)
- Sampaikan salam, nama, NIM, dan judul tugas UTS: **"Implementasi Order Service pada Ekosistem Microservices E-Commerce"**.
- Jelaskan secara ringkas peran `order-service`: layanan yang bertanggung jawab mengubah isi keranjang belanja (`cart-service`) menjadi pesanan resmi dengan validasi ketersediaan katalog dan stok (`product-service`), pencatatan transaksi atomik, dan snapshotting harga.

---

## 2. Penjelasan Arsitektur & Lapisan Kode (±2 Menit)
Tunjukkan struktur folder `order-service` di editor/IDE:
- **Arsitektur Berlapis (*Layered Architecture*):**
  - `routes/api.php`: Pendaftaran rute dan batasan parameter angka (`whereNumber`).
  - `app/Http/Requests`: Validasi input (`CreateOrderRequest`, `GetOrdersByUserRequest`).
  - `app/Http/Controllers`: Penanganan HTTP, try-catch, dan pemetaan status response (201, 404, 409, 422, 503, 500).
  - `app/Services/OrderService.php`: Logika bisnis murni (DB Transaction, kalkulasi snapshot harga, orkestrasi checkout).
  - `app/Clients`: Pemanggilan REST keluar (`CartClient`, `ProductClient`) dengan batas timeout 5 detik.
  - `app/Models`: Model Eloquent `Order` (`hasMany`) dan `OrderItem` (`belongsTo`).

---

## 3. Menjalankan Sistem via Docker Compose (±1 Menit)
- Buka terminal dan perlihatkan eksekusi:
  ```bash
  docker compose up --build
  ```
- Jelaskan container yang sedang naik:
  - `product-service` (Port 3000) & MySQL (3306)
  - `cart-service` (Port 3001) & PostgreSQL (5432)
  - `review-service` (Port 3002) & MongoDB (27017)
  - `order-service` (Port 3003) & MySQL (Host Port 3308)

---

## 4. Demo Skenario Endpoint (Postman / Thunder Client) (±5–6 Menit)

### A. Health Check
- `GET http://localhost:3003/health`
- **Ekspektasi:** Status `200 OK`, `{"status": "ok", "service": "order-service"}`.

### B. Skenario Gagal `POST /orders` (Wajib)
1. **Validasi Body Kosong (422):**
   - `POST /orders` dengan body `{}` (tanpa `user_id`).
   - Respon: `422 Unprocessable Content` -> `"Field user_id wajib diisi"`.
2. **Keranjang Kosong (409):**
   - Pastikan user ID 7 belum memiliki item di keranjang.
   - `POST /orders` body `{"user_id": 7}`.
   - Respon: `409 Conflict` -> `"Keranjang belanja kosong"`.
3. **Stok Tidak Cukup (409):**
   - Tambahkan item Monitor 24 Inch (stok 10) sebanyak 11 unit ke keranjang user 7 via cart-service.
   - `POST /orders` body `{"user_id": 7}`.
   - Respon: `409 Conflict` -> `"Stok Monitor 24 Inch tidak mencukupi: diminta 11, tersedia 10"`.
4. **Product Service Dimatikan (503) & Bukti Log:**
   - Matikan container product-service: `docker stop product-service`.
   - `POST /orders` body `{"user_id": 7}`.
   - Respon: `503 Service Unavailable` -> `"Product service tidak dapat dihubungi, coba lagi nanti"`.
   - Tunjukkan terminal / log order-service: perlihatkan log error tercatat rapi beserta URL target dan pesan kegagalan koneksi.
   - Nyalakan kembali: `docker start product-service`.

### C. Skenario Sukses `POST /orders` & Snapshot Harga (Wajib)
1. Isi keranjang user 7 dengan item valid (misal Keyboard & Mouse).
2. `POST /orders` body `{"user_id": 7}`.
   - Respon: `201 Created` dengan `status: "PENDING"`, total belanja, dan daftar items.
   - Periksa cart user 7 via `GET /carts/7` -> keranjang sudah otomatis kosong.
3. **Bukti Snapshot Harga:**
   - Ubah harga produk di `product-service` (misal via database atau update endpoint).
   - Buka kembali pesanan via `GET /orders/{id}` -> tunjukkan bahwa harga pada order yang sudah dibuat **tidak ikut berubah**.

### D. Endpoint Lainnya
1. `GET /orders/{id}`:
   - Sukses `200 OK` dengan relasi items.
   - Gagal `404 Not Found` (uji ID tidak ada misal 9999, dan ID non-angka misal `/orders/abc`).
2. `GET /orders/user/{userId}`:
   - `GET /orders/user/7?status=PENDING` -> filter pesanan pending.
   - `GET /orders/user/7?status=SHIPPED` -> `422 Unprocessable Content` ("Status harus PENDING atau PAID").
3. `PATCH /orders/{id}/pay`:
   - Bayar pesanan: status berubah menjadi `PAID` (`200 OK`).
   - Coba bayar ulang pesanan yang sama -> `409 Conflict` ("Pesanan berstatus PAID, hanya pesanan PENDING yang dapat dibayar").

---

## 5. Keputusan Desain & Keterbatasan (±1–2 Menit)
- **Keputusan Penting:** Snapshot data produk dan transaksi database (`DB::transaction`). Jika harga master berubah, kontrak finansial pembeli tetap valid.
- **Graceful Degradation:** Jika cart-service gagal dikosongkan setelah pesanan tercatat, transaksi pesanan tidak di-rollback agar tidak merugikan user, melainkan dicatat warning ke log untuk rekonsiliasi.
- **Keterbatasan Solusi:** Komunikasi antar-service saat ini bersifat synchronous REST via HTTP. Pada sistem skala besar dengan trafik tinggi, komunikasi seperti checkout lebih optimal menggunakan message broker asinkron (misal RabbitMQ/Kafka) untuk event-driven checkout.

---

## 6. Penutup (±1 Menit)
- Ringkasan singkat keberhasilan demo.
- Terima kasih kepada dosen pengampu.
