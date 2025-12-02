# 🗂️ PANDUAN CEPAT - Tabel Database & Penggunaan

## Ringkas Tabel & Field

### 📋 Tabel `users`
**Untuk:** Menyimpan data Admin dan Decision Maker  
**Primary Key:** `user_id`

| Field | Type | Keterangan |
|-------|------|-----------|
| `user_id` | INT (PK) | ID unik user |
| `nama` | VARCHAR | Nama lengkap |
| `username` | VARCHAR | Username untuk login |
| `password` | VARCHAR | Password (hashed) |
| `role` | ENUM | 'admin' atau 'dm' |
| `jabatan` | ENUM | 'manager' atau 'staff' (khusus untuk DM) |
| `created_at` | TIMESTAMP | Waktu dibuat |

**Contoh Data:**
- ID: 1 | Nama: Budi Santoso | Role: admin | Jabatan: - 
- ID: 2 | Nama: Rina | Role: dm | Jabatan: manager ← **Manager DM (bisa hitung Borda)**
- ID: 3 | Nama: Ahmad | Role: dm | Jabatan: staff ← **Staff DM (hanya voting)**

---

### 🔧 Tabel `alternatif`
**Untuk:** Daftar sistem helpdesk yang dibandingkan  
**Primary Key:** `alternatif_id`

| Field | Type | Keterangan |
|-------|------|-----------|
| `alternatif_id` | INT (PK) | ID sistem |
| `kode` | VARCHAR | Kode singkat (misal: ZEN, TIC, UVD) |
| `nama_alternatif` | VARCHAR | Nama lengkap sistem |
| `keterangan` | TEXT | Deskripsi singkat |
| `created_at` | TIMESTAMP | Waktu dibuat |

**Data Tetap (5 Sistem):**
1. Zendesk
2. osTicket
3. UVdesk
4. Zammad
5. Manual Ticketing System

---

### 📊 Tabel `kriteria`
**Untuk:** Daftar kriteria evaluasi  
**Primary Key:** `kriteria_id`

| Field | Type | Keterangan |
|-------|------|-----------|
| `kriteria_id` | INT (PK) | ID kriteria |
| `kode` | VARCHAR | Kode singkat |
| `nama_kriteria` | VARCHAR | Nama kriteria |
| `keterangan` | TEXT | Penjelasan kriteria |
| `created_at` | TIMESTAMP | Waktu dibuat |

**Data Tetap (5 Kriteria):**
1. Kemudahan Penggunaan
2. Harga/Biaya
3. Ukuran Komunitas
4. Omnichannel Support
5. Konstruksi Aplikasi

---

### 🗳️ Tabel `borda_input`
**Untuk:** Input ranking dari setiap DM  
**Primary Key:** `id` | **Foreign Keys:** `user_id`, `alternatif_id`

| Field | Type | Keterangan |
|-------|------|-----------|
| `id` | INT (PK) | ID record |
| `user_id` | INT (FK) | Siapa yang voting (→ users) |
| `alternatif_id` | INT (FK) | Sistem mana (→ alternatif) |
| `ranking` | INT | Peringkat 1-5 yang dipilih |
| `created_at` | TIMESTAMP | Waktu voting |

**Contoh:**
- User 2 ranking alternatif 1 = 1 (Zendesk di peringkat #1)
- User 2 ranking alternatif 2 = 2 (osTicket di peringkat #2)
- User 3 ranking alternatif 1 = 2 (Zendesk di peringkat #2)
- User 3 ranking alternatif 2 = 1 (osTicket di peringkat #1)

---

### 🏆 Tabel `borda_hasil`
**Untuk:** Hasil akhir Borda Count (Private untuk Manager)  
**Primary Key:** `id` | **Foreign Key:** `alternatif_id`

| Field | Type | Keterangan |
|-------|------|-----------|
| `id` | INT (PK) | ID record |
| `alternatif_id` | INT (FK) | Sistem (→ alternatif) |
| `skor_borda` | INT | Total poin Borda (jumlah dari semua DM) |
| `peringkat` | INT | Ranking akhir (1=terbaik) |
| `created_at` | TIMESTAMP | Waktu perhitungan |

**Contoh Hasil:**
- Zendesk: Skor 13, Peringkat 1 🥇
- osTicket: Skor 11, Peringkat 2 🥈
- UVdesk: Skor 9, Peringkat 3 🥉
- Zammad: Skor 7, Peringkat 4
- Manual: Skor 5, Peringkat 5

---

### 📈 Tabel `ahp_prioritas_final`
**Untuk:** Hasil perhitungan AHP per user  
**Primary Key:** `id` | **Foreign Keys:** `user_id`, `alternatif_id`

| Field | Type | Keterangan |
|-------|------|-----------|
| `id` | INT (PK) | ID record |
| `user_id` | INT (FK) | Penilai (→ users) |
| `alternatif_id` | INT (FK) | Sistem (→ alternatif) |
| `nilai_final` | DECIMAL | Score AHP per sistem (0-1) |
| `ranking` | INT | Ranking personal (1-5) |
| `created_at` | TIMESTAMP | Waktu perhitungan |

**Contoh:**
- User 2 → Zendesk: 0.4520, Ranking 1
- User 2 → osTicket: 0.3105, Ranking 2
- User 3 → Zendesk: 0.3890, Ranking 2
- User 3 → osTicket: 0.4210, Ranking 1

---

## 🔄 Alur Data & Tabel

```
FLOW VOTING & RANKING:

1. USER LOGIN
   ↓
   users.role = 'admin'  → Dashboard Admin
   users.role = 'dm'     → Dashboard DM
              ↓
              users.jabatan = 'manager' → Manager (bisa hitung Borda)
              users.jabatan = 'staff'   → Staff (hanya voting)

2. VOTING (Decision Maker)
   DM input ranking untuk setiap alternatif
   ↓
   Data masuk ke → borda_input
   (user_id, alternatif_id, ranking)

3. HITUNG BORDA (Manager Only)
   Manager klik "Hitung Borda Count"
   ↓
   proses_borda.php membaca borda_input
   ↓
   Hitung: Skor = Jumlah DM - Ranking + 1
   ↓
   Simpan hasil ke → borda_hasil
   (alternatif_id, skor_borda, peringkat)

4. LIHAT HASIL (Manager Only)
   hasil.php menampilkan borda_hasil
   Hanya Manager yang bisa lihat section ini
```

---

## 🎯 Query Cheatsheet

### Ambil Data DM
```php
$dmUsers = query("SELECT * FROM users WHERE role='dm'");
```

### Ambil Ranking 1 DM
```php
$rankings = query("SELECT * FROM borda_input WHERE user_id=$user_id ORDER BY ranking");
```

### Ambil Nama Sistem dari Ranking
```php
$data = query("
    SELECT a.nama_alternatif, bi.ranking
    FROM borda_input bi
    JOIN alternatif a ON bi.alternatif_id = a.alternatif_id
    WHERE bi.user_id=$user_id
    ORDER BY bi.ranking ASC
");
```

### Ambil Hasil Borda Akhir
```php
$results = query("
    SELECT a.nama_alternatif, bh.skor_borda, bh.peringkat
    FROM borda_hasil bh
    JOIN alternatif a ON bh.alternatif_id = a.alternatif_id
    ORDER BY bh.peringkat ASC
");
```

### Cek Status Voting (Manager)
```php
$status = query("
    SELECT u.nama, u.jabatan,
           CASE WHEN COUNT(bi.id) > 0 THEN 'Sudah' ELSE 'Belum' END as status
    FROM users u
    LEFT JOIN borda_input bi ON u.user_id = bi.user_id
    WHERE u.role='dm'
    GROUP BY u.user_id
");
```

---

## ⚡ Kunci Penting

1. **Nama Tabel Benar:**
   - ✅ `alternatif` (bukan alternatives)
   - ✅ `kriteria` (bukan criteria)
   - ✅ `users` dengan WHERE role='dm' (bukan penilai)

2. **Field Penting:**
   - ✅ `users.role` = 'admin' atau 'dm'
   - ✅ `users.jabatan` = 'manager' atau 'staff' (untuk DM)
   - ✅ `borda_input` untuk voting data
   - ✅ `borda_hasil` untuk hasil akhir

3. **Access Control:**
   - ✅ Admin → hanya akses admin/
   - ✅ Manager DM → lihat Borda + hasil
   - ✅ Staff DM → hanya lihat personal ranking

---

**File ini dibuat untuk referensi cepat. Untuk penjelasan lengkap, lihat DATABASE_MAPPING.md**
