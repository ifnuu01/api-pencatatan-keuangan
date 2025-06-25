<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

# Dokumentasi API Pencatatan Keuangan

Dokumentasi ini menjelaskan seluruh endpoint API, field, tipe data, dan contoh respons.

---

## Autentikasi

### Register

**POST** `/api/register`

#### Body Request

| Field                 | Tipe   | Wajib | Keterangan          |
| --------------------- | ------ | ----- | ------------------- |
| name                  | string | Ya    | Nama pengguna       |
| email                 | string | Ya    | Email unik          |
| password              | string | Ya    | Minimal 8 karakter  |
| password_confirmation | string | Ya    | Konfirmasi password |

#### Contoh Respons

```json
{
    "status": true,
    "message": "User registered successfully",
    "data": {
        "user": { "id": 1, "name": "Nama", "email": "email@email.com" },
        "token": "..."
    }
}
```

---

### Login

**POST** `/api/login`

#### Body Request

| Field    | Tipe   | Wajib | Keterangan     |
| -------- | ------ | ----- | -------------- |
| email    | string | Ya    | Email pengguna |
| password | string | Ya    | Password       |

#### Contoh Respons

```json
{
    "status": true,
    "message": "User logged in successfully",
    "data": {
        "user": { "id": 1, "name": "Nama", "email": "email@email.com" },
        "token": "..."
    }
}
```

---

### Logout

**POST** `/api/logout`  
**Header:** `Authorization: Bearer {token}`

#### Contoh Respons

```json
{
    "status": true,
    "message": "User logged out successfully"
}
```

---

## Kategori

### List Kategori

**GET** `/api/categories`  
**Header:** `Authorization: Bearer {token}`

#### Contoh Respons

```json
{
    "status": true,
    "message": "Categories retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Makanan",
            "type": "expense",
            "icon": "fa fa-utensils",
            "color": "#FF0000"
        }
    ]
}
```

### Tambah Kategori

**POST** `/api/categories`

| Field | Tipe   | Wajib | Keterangan         |
| ----- | ------ | ----- | ------------------ |
| name  | string | Ya    | Nama kategori      |
| type  | string | Ya    | `income`/`expense` |
| icon  | string | Ya    | Kode ikon          |
| color | string | Ya    | Kode warna HEX     |

---

## Dompet

### List Dompet

**GET** `/api/wallets`

#### Contoh Respons

```json
{
    "status": true,
    "message": "Wallets retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Tunai",
            "balance": 100000,
            "currency": "Rp"
        }
    ]
}
```

### Tambah Dompet

**POST** `/api/wallets`

| Field    | Tipe   | Wajib | Keterangan    |
| -------- | ------ | ----- | ------------- |
| name     | string | Ya    | Nama dompet   |
| balance  | number | Ya    | Saldo awal    |
| currency | string | Tidak | Default: "Rp" |

---

## Transaksi

### List Transaksi

**GET** `/api/transactions`

#### Contoh Respons

```json
{
  "status": true,
  "message": "transaction retrieved successfully",
  "data": [
    {
      "id": 1,
      "type": "expense",
      "amount": 50000,
      "description": "Makan siang",
      "transaction_date": "2024-06-25",
      "category": { ... },
      "wallet": { ... }
    }
  ]
}
```

### Tambah Transaksi

**POST** `/api/transactions`

| Field            | Tipe   | Wajib | Keterangan         |
| ---------------- | ------ | ----- | ------------------ |
| wallet_id        | int    | Ya    | ID dompet          |
| category_id      | int    | Ya    | ID kategori        |
| type             | string | Ya    | `income`/`expense` |
| amount           | number | Ya    | Nominal            |
| description      | string | Tidak | Keterangan         |
| transaction_date | date   | Tidak | Default: hari ini  |

---

## Transaksi Berkala

### List Transaksi Berkala

**GET** `/api/recurring`

### Tambah Transaksi Berkala

**POST** `/api/recurring`

| Field            | Tipe   | Wajib | Keterangan                             |
| ---------------- | ------ | ----- | -------------------------------------- |
| wallet_id        | int    | Ya    | ID dompet                              |
| category_id      | int    | Ya    | ID kategori                            |
| amount           | number | Ya    | Nominal                                |
| type             | string | Ya    | `income`/`expense`                     |
| start_date       | date   | Ya    | Tanggal mulai                          |
| repeat_interval  | string | Ya    | `daily`, `weekly`, `monthly`, `yearly` |
| repeat_every     | int    | Ya    | Setiap berapa interval                 |
| description      | string | Tidak | Keterangan                             |
| end_date         | date   | Tidak | Tanggal selesai                        |
| total_occurences | int    | Tidak | Total pengulangan                      |

---

## Anggaran

### List Anggaran

**GET** `/api/budgets`

#### Contoh Respons

```json
{
  "status": true,
  "message": "Budgets retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Anggaran Makan",
      "amount": 1000000,
      "period": "monthly",
      "start_date": "2024-06-01",
      "end_date": "2024-06-30",
      "category": { ... },
      "total_expense": 500000,
      "remaining": 500000
    }
  ]
}
```

### Tambah Anggaran

**POST** `/api/budgets`

| Field       | Tipe   | Wajib | Keterangan                             |
| ----------- | ------ | ----- | -------------------------------------- |
| name        | string | Ya    | Nama anggaran                          |
| amount      | number | Ya    | Nominal anggaran                       |
| period      | string | Ya    | `daily`, `weekly`, `monthly`, `yearly` |
| start_date  | date   | Ya    | Tanggal mulai                          |
| end_date    | date   | Ya    | Tanggal selesai                        |
| category_id | int    | Ya    | ID kategori                            |

---

## Dashboard

### Data Dashboard

**GET** `/api/dashboard`

#### Contoh Respons

-   `pengeluaranKategori`: List pengeluaran per kategori
-   `pemasukanKategori`: List pemasukan per kategori
-   `transaksiTerbaru`: Transaksi terbaru
-   `saldo`: Total saldo
-   `totalPemasukan`: Total pemasukan
-   `totalPengeluaran`: Total pengeluaran

---

## Grafik

### Data Grafik

**GET** `/api/charts`

#### Contoh Respons

-   `pengeluaranKategori`: Pengeluaran per kategori
-   `pemasukanKategori`: Pemasukan per kategori
-   `data`: Rekap bulanan

---

## Autentikasi

Semua endpoint (kecuali `/register` dan `/login`) membutuhkan Bearer Token pada header.

---

## Contoh Respons Error

```json
{
    "status": false,
    "message": "Not found"
}
```

---

**Catatan:**

-   Format tanggal: `YYYY-MM-DD`
-   Semua nominal dalam satuan sesuai dompet
-   Semua endpoint merespons dalam format JSON

---

Untuk detail lebih lanjut, cek file controller di folder `app/Http/Controllers`.

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
