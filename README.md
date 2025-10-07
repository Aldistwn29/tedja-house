Mantap banget, ini sudah jadi proyek Laravel yang cukup kompleks dan lengkap — ada **CMS Admin (Filament)**, **Midtrans Payment Gateway**, **Auth via Laravel Breeze**, dan **Role Permission via Spatie**.
Berikut aku bantu buatin **dokumentasi profesional dan rapi** yang bisa kamu simpan di file `README.md` atau dijadikan laporan proyek ✨

---

# 🏠 TEDJA Project: Web Property Selling and Installments

## 📖 About

**TEDJA** adalah website yang membantu masyarakat membeli rumah atau apartemen dengan sistem **cicilan terintegrasi**.
Platform ini memfasilitasi pembeli berpenghasilan rendah untuk mendapatkan hunian impian dengan proses yang **mudah, aman, dan transparan**.

Website ini dibangun menggunakan **Laravel Framework** dan mengintegrasikan:

* **Filament Admin Panel** untuk CMS pengelolaan data.
* **Midtrans** untuk sistem pembayaran online.
* **Laravel Breeze** untuk otentikasi login dan register.
* **Spatie Laravel Permission** untuk pengaturan role-based access control.

---

## ⚙️ Tech Stack

| Layer           | Technology                       |
| --------------- | -------------------------------- |
| Backend         | Laravel 11.x                     |
| Frontend        | Blade Template + Tailwind CSS    |
| Authentication  | Laravel Breeze                   |
| Authorization   | Spatie Laravel Permission        |
| Admin Dashboard | Filament Admin Panel             |
| Payment Gateway | Midtrans (Snap API)              |
| Database        | MySQL / MariaDB                  |
| Web Server      | Laravel Artisan / Nginx / Apache |
| Environment     | PHP 8.3, Composer 2.x            |

---

## 👥 User Roles

### 🏗️ 1. House Developer (Admin)

Admin bertugas mengelola semua data dan memantau aktivitas pembelian.

**Admin Features (via Filament Dashboard):**

* Login ke dashboard admin.
* CRUD:

  * Category
  * Users
  * Roles
  * City
  * Bank
  * Facility
  * House Facilities
  * House (beserta detailnya)
  * Interest
  * Mortgage Request
  * Installments

**Admin Pages:**

* `/admin` → Filament Dashboard
* `/admin/*` → CRUD Resource Pages (otomatis dari Filament)

---

### 🏡 2. Customer

Customer dapat menjelajahi katalog rumah, mengajukan cicilan, dan melakukan pembayaran.

**Customer Features:**

* [x] Register / Login (Laravel Breeze)
* [x] Jelajahi katalog rumah
* [x] Lihat detail rumah
* [x] Simulasi perhitungan cicilan
* [x] Pilih bank penyedia pinjaman
* [x] Ajukan *Mortgage Request* dengan dokumen pendukung
* [x] Tunggu proses persetujuan dari admin/bank
* [x] Setelah disetujui, bayar cicilan pertama melalui Midtrans
* [x] Lihat riwayat pembayaran cicilan

**Customer Pages:**

* **No Auth:**

  * `/` → Homepage
  * `/houses` → Katalog rumah
  * `/houses/{id}` → Detail rumah
  * `/calculation` → Perhitungan cicilan
* **With Auth:**

  * `/dashboard/mortgage` → List pengajuan
  * `/dashboard/installment` → Riwayat pembayaran
  * `/dashboard/installment/payment` → Halaman pembayaran Midtrans

---

## 💳 Payment Integration (Midtrans)

### 🔗 Flow Integrasi Midtrans

1. **Customer klik tombol "Bayar"**

   * Request dikirim ke endpoint Laravel untuk generate **Snap Token**.
2. **Backend (PaymentService)** membuat parameter transaksi:

   * order_id
   * gross_amount
   * custom_field (user_id & mortgage_request_id)
3. **Midtrans mengirimkan Snap Token**

   * Frontend membuka pop-up pembayaran Midtrans Snap.
4. **Customer melakukan pembayaran**

   * Midtrans mengirimkan **Webhook Notification** ke endpoint Laravel.
5. **Backend menangani notifikasi**

   * Mengecek status `settlement` atau `capture`.
   * Membuat data `Installment` baru di database.
6. **Customer diarahkan kembali ke dashboard**

   * Status pembayaran dan riwayat cicilan diperbarui otomatis.

---

## 🔢 Amortization Formula (Perhitungan Cicilan)

Formula cicilan mengikuti standar internasional perbankan:

[
M = P \times \frac{r(1 + r)^n}{(1 + r)^n - 1}
]

**Keterangan:**

* `M` = Cicilan bulanan
* `P` = Jumlah pinjaman (principal)
* `r` = Suku bunga per bulan (bunga tahunan ÷ 12)
* `n` = Total jumlah cicilan (tahun × 12)

Contoh implementasi:

```php
$monthlyRate = ($interestRate / 100) / 12;
$months = $loanTerm * 12;
$monthlyPayment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
```

---

## 🔐 Authentication & Authorization

### 🧩 Laravel Breeze

Digunakan untuk:

* Login
* Register
* Logout
* Reset password
* Session management

### ⚖️ Spatie Laravel Permission

Mendefinisikan role & permission untuk user:

* **Role:**

  * `admin` → Akses penuh Filament CMS
  * `customer` → Akses halaman pengguna

Middleware:

```php
Route::group(['middleware' => ['role:admin']], function() {
    // Filament dashboard
});

Route::group(['middleware' => ['role:customer']], function() {
    // User dashboard
});
```

---

## 🧱 Filament CMS (Admin Panel)

Filament digunakan untuk membuat halaman CRUD secara cepat tanpa harus membuat controller & view manual.

Contoh konfigurasi resource:

```php
class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            Textarea::make('description'),
            Select::make('city_id')->relationship('city', 'name'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->sortable()->searchable(),
            TextColumn::make('city.name'),
        ]);
    }
}
```

---

## 🧰 Project Structure Overview

```
TEDJA/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── DashboardController.php
│   │   │   ├── PaymentController.php
│   │   │   └── HouseController.php
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   │   ├── House.php
│   │   ├── Installment.php
│   │   ├── MortgageRequest.php
│   │   └── User.php
│   ├── Services/
│   │   ├── PaymentService.php
│   │   └── MidtransService.php
│   └── Policies/
│
├── resources/
│   ├── views/
│   │   ├── front/
│   │   └── dashboard/
│
├── routes/
│   ├── web.php
│   ├── api.php
│   └── filament.php
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
│
├── config/
│   ├── midtrans.php
│   ├── permission.php
│   └── filament.php
│
└── .env
```

---

## 🚀 How to Run the Project

### 1️⃣ Clone & Install Dependencies

```bash
git clone https://github.com/yourusername/tedja.git
cd tedja
composer install
npm install && npm run dev
```

### 2️⃣ Setup Environment

Salin file `.env.example` ke `.env` dan ubah konfigurasi sesuai kebutuhan:

```bash
cp .env.example .env
php artisan key:generate
```

Atur Midtrans di `.env`:

```env
MIDTRANS_SERVER_KEY=Mid-server-xxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SANITIZE=true
MIDTRANS_3DS=true
```

### 3️⃣ Migrate Database

```bash
php artisan migrate --seed
```

### 4️⃣ Jalankan Server

```bash
php artisan serve
```

### 5️⃣ Akses Dashboard

* Admin Dashboard: `/admin`
* Customer Frontend: `/`

---

## 📦 Deployment Notes

* Gunakan HTTPS untuk URL webhook Midtrans (wajib SSL).
* Jalankan `php artisan queue:work` untuk memproses event async jika dibutuhkan.
* Untuk testing Midtrans gunakan:

  * Snap URL: `https://app.sandbox.midtrans.com/snap/snap.js`
  * Dashboard: [https://dashboard.sandbox.midtrans.com/](https://dashboard.sandbox.midtrans.com/)

---

## 🧾 License

Project ini dikembangkan untuk tujuan pembelajaran dan penelitian.
Silakan gunakan dan modifikasi sesuai kebutuhan dengan mencantumkan atribusi kepada pengembang asli.

---
