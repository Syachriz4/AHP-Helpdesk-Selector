# 📋 Penjelasan Mapping Database vs Code

## 🎯 Masalah yang Terjadi

Sebelumnya ada **kebingungan antara nama tabel di code dan nama tabel yang sebenarnya di database**. Berikut adalah penjelasan lengkapnya:

---

## ✅ Tabel yang BENAR di Database

Database `db_gdss_helpdesk` memiliki tabel-tabel berikut:

### **Tabel Utama**
| Nama Tabel | Deskripsi |
|-----------|-----------|
| `users` | Data pengguna (Admin, Decision Maker) |
| `alternatif` | Daftar sistem helpdesk yang dibandingkan |
| `kriteria` | Daftar kriteria evaluasi |

### **Tabel Voting & Ranking**
| Nama Tabel | Deskripsi |
|-----------|-----------|
| `borda_input` | Input ranking dari setiap Decision Maker |
| `borda_hasil` | Hasil akhir Borda Count |
| `log_status` | Log status voting |

### **Tabel AHP (Analytical Hierarchy Process)**
| Nama Tabel | Deskripsi |
|-----------|-----------|
| `ahp_penilaian_kriteria` | Pairwise comparison untuk kriteria |
| `ahp_penilaian_alternatif` | Pairwise comparison untuk alternatif |
| `ahp_prioritas_final` | Hasil perhitungan prioritas akhir AHP |

---

## ❌ Nama Tabel YANG SALAH di Code

Code sebelumnya menggunakan nama tabel yang **TIDAK ada di database**:

```php
// ❌ SALAH - Tabel tidak ada!
SELECT * FROM alternatives    // Seharusnya: alternatif
SELECT * FROM criteria         // Seharusnya: kriteria
SELECT * FROM penilai          // Seharusnya: users (WHERE role='dm')
```

---

## ✅ Koreksi yang Sudah Dilakukan

### 1. **File: `index.php`**

**Sebelum (SALAH):**
```php
$totalAlternatif = countRows("SELECT * FROM alternatives");
$totalKriteria = countRows("SELECT * FROM criteria");
```

**Sesudah (BENAR):**
```php
$totalAlternatif = countRows("SELECT * FROM alternatif");
$totalKriteria = countRows("SELECT * FROM kriteria");
```

### 2. **File: `admin/data_penilai.php`**

**Sebelum (SALAH):**
```php
// Menggunakan tabel 'penilai' yang tidak ada
mysqli_query($conn, "SELECT * FROM penilai WHERE id=$id");
mysqli_query($conn, "INSERT INTO penilai (nama, username, password, role)");
```

**Sesudah (BENAR):**
```php
// Menggunakan tabel 'users' dengan WHERE role='dm'
mysqli_query($conn, "SELECT * FROM users WHERE user_id=$id AND role='dm'");
mysqli_query($conn, "INSERT INTO users (nama, username, password, role, jabatan) 
    VALUES ('$nama', '$username', '$password_hash', 'dm', '$jabatan')");
```

---

## 📊 Mapping Struktur Database

### **Tabel: `users`**
```
user_id (PK)
├── nama
├── username
├── password (hashed)
├── role (admin / dm)
└── jabatan (manager / staff) ← BARU! Untuk membedakan tipe DM
```

### **Tabel: `alternatif`**
```
alternatif_id (PK)
├── kode
├── nama_alternatif (5 sistem: Zendesk, osTicket, UVdesk, Zammad, Manual)
├── keterangan
└── created_at
```

### **Tabel: `kriteria`**
```
kriteria_id (PK)
├── kode
├── nama_kriteria (5 kriteria: Penggunaan, Harga, UkuranKomunitas, Omnichannel, KonstruksiApp)
├── keterangan
└── created_at
```

### **Tabel: `borda_input`**
```
id (PK)
├── user_id (FK → users)
├── alternatif_id (FK → alternatif)
├── ranking (1-5, dari masing-masing DM)
└── created_at
```

### **Tabel: `borda_hasil`**
```
id (PK)
├── alternatif_id (FK → alternatif)
├── skor_borda (hasil kalkulasi: Rank 1 = 5 poin, Rank 2 = 4 poin, dst)
├── peringkat (1-5)
└── created_at
```

### **Tabel: `ahp_prioritas_final`**
```
id (PK)
├── user_id (FK → users)
├── alternatif_id (FK → alternatif)
├── nilai_final (hasil AHP per user)
├── ranking (1-5)
└── created_at
```

---

## 🔍 Bagaimana Query Harus Bekerja

### ✅ **Contoh Query BENAR:**

**1. Ambil semua Decision Maker:**
```php
SELECT * FROM users WHERE role='dm'
```

**2. Ambil ranking dari seorang DM:**
```php
SELECT * FROM borda_input WHERE user_id=$user_id ORDER BY ranking ASC
```

**3. Ambil nama alternatif berdasarkan ranking:**
```php
SELECT bi.ranking, a.nama_alternatif 
FROM borda_input bi
JOIN alternatif a ON bi.alternatif_id = a.alternatif_id
WHERE bi.user_id=$user_id
ORDER BY bi.ranking ASC
```

**4. Ambil hasil Borda Count akhir:**
```php
SELECT bh.peringkat, a.nama_alternatif, bh.skor_borda
FROM borda_hasil bh
JOIN alternatif a ON bh.alternatif_id = a.alternatif_id
ORDER BY bh.peringkat ASC
```

---

## 🚀 File yang Sudah di-Update

✅ `index.php` - Fixed table names  
✅ `admin/data_penilai.php` - Fixed all CRUD operations  
✅ `hasil.php` - Uses correct table names  
✅ `proses_borda.php` - Uses correct table names  

---

## ⚠️ Perhatian Penting

1. **MySQL Service harus berjalan** - Pastikan Laragon MySQL90 aktif
2. **Database `db_gdss_helpdesk` harus ada** - Sudah dibuat
3. **Tabel-tabel harus sesuai** - Lihat daftar tabel di atas
4. **Field `jabatan` di tabel `users`** - Untuk membedakan manager vs staff DM

---

## 🧪 Testing

Untuk memverifikasi tabel, jalankan query di MySQL:

```sql
SHOW TABLES FROM db_gdss_helpdesk;
DESCRIBE users;
DESCRIBE alternatif;
DESCRIBE kriteria;
DESCRIBE borda_input;
DESCRIBE borda_hasil;
```

---

## 📝 Kesimpulan

| Aspek | Sebelum | Sesudah |
|-------|---------|--------|
| Nama tabel `alternatif` | ❌ `alternatives` | ✅ `alternatif` |
| Nama tabel `kriteria` | ❌ `criteria` | ✅ `kriteria` |
| Tabel penilai | ❌ `penilai` (tidak ada) | ✅ `users` (WHERE role='dm') |
| Status kesamaan | ❌ Error 500 | ✅ Berjalan normal |

**Semua mismatch sudah dikoreksi!** ✅
