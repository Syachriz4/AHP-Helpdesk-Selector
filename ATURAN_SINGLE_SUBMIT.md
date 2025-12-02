# 🔒 Aturan: DM Hanya Bisa Mengisi Penilaian 1 Kali

## 📋 Deskripsi Aturan

Setiap Decision Maker (DM) **hanya diizinkan mengisi form penilaian 1 kali saja**. Jika DM mencoba submit untuk kedua kalinya, sistem akan menolak dan menampilkan pesan error.

**Tujuan:** Mencegah penumpukan data duplikat di database.

---

## 🔧 Implementasi Teknis

### 1. **Pengecekan di `proses_penilaian.php`**

Sebelum menerima submit form, sistem check apakah user sudah pernah mengisi:

```php
// CEK APAKAH SUDAH PERNAH MENGISI PENILAIAN
$checkData = query("SELECT COUNT(*) as total FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
if (!empty($checkData) && $checkData[0]['total'] > 0) {
    $_SESSION['error'] = '❌ Maaf, Anda sudah pernah mengisi penilaian sebelumnya. DM hanya bisa mengisi 1 kali saja!';
    header("Location: penilaian.php");
    exit;
}
```

**Cara Kerja:**
- Query: `SELECT COUNT(*) FROM ahp_penilaian_kriteria WHERE user_id = $user_id`
- Jika hasil > 0 = User sudah pernah mengisi
- Jika hasil = 0 = User belum pernah mengisi (bisa lanjut)

### 2. **Alert di `penilaian.php`**

Menampilkan warning jika user sudah pernah mengisi, dan menyembunyikan form:

```php
<?php 
// CEK APAKAH USER SUDAH PERNAH MENGISI PENILAIAN
$user_id = $_SESSION['user_id'];
$checkSubmitted = query("SELECT COUNT(*) as total FROM ahp_penilaian_kriteria WHERE user_id = $user_id");
$alreadySubmitted = !empty($checkSubmitted) && $checkSubmitted[0]['total'] > 0;
?>

<?php if ($alreadySubmitted) : ?>
    <div class="alert alert-warning">
        <strong>⚠️ Perhatian!</strong> Anda sudah pernah mengisi penilaian sebelumnya. 
        Setiap DM hanya bisa mengisi 1 kali saja...
    </div>
<?php endif; ?>

<!-- Form disembunyikan jika sudah submit -->
<form action="proses_penilaian.php" method="POST" <?php echo $alreadySubmitted ? 'style="display:none;"' : ''; ?>>
```

---

## 📊 Database Check Points

Sistem mengecek record di tabel berikut untuk menentukan status DM:

### **Tabel: `ahp_penilaian_kriteria`**
```sql
SELECT COUNT(*) FROM ahp_penilaian_kriteria WHERE user_id = $user_id
```
- Jika > 0 = Sudah mengisi perbandingan kriteria

### **Tabel: `borda_input`** (sebagai alternative check)
```sql
SELECT COUNT(*) FROM borda_input WHERE user_id = $user_id
```
- Jika > 0 = Sudah melakukan voting

---

## 🚨 Alur Sistem

```
DM Klik "Form Penilaian"
        ↓
Sistem check ahp_penilaian_kriteria
        ↓
        ├─ Ada record? → Tampilkan warning + sembunyikan form
        │                User bisa lihat "Lihat Hasil Penilaian"
        │
        └─ Tidak ada? → Tampilkan form biasa
                        DM bisa isi penilaian
                        ↓
                        Submit form
                        ↓
                        proses_penilaian.php cek lagi
                        ↓
                        ├─ Ada record baru? REJECT (error)
                        │
                        └─ Tidak ada? INSERT data
                           Hitung AHP
                           Redirect ke hasil.php
```

---

## 💾 Data Flow

### **PERTAMA KALI SUBMIT (Diterima):**

```
DM 1 (user_id=2) submit penilaian
    ↓
proses_penilaian.php check:
    SELECT COUNT(*) FROM ahp_penilaian_kriteria WHERE user_id=2
    Result: 0 (belum ada)
    ↓
✅ INSERT ke ahp_penilaian_kriteria (10 records perbandingan kriteria)
✅ INSERT ke ahp_penilaian_alternatif (50 records perbandingan alternatif)
✅ INSERT ke borda_input (5 records voting status)
    ↓
Redirect ke hitung_ahp.php
    ↓
Hitung AHP & INSERT ke ahp_prioritas_final
    ↓
Redirect ke hasil.php
```

### **KEDUA KALI SUBMIT (Ditolak):**

```
DM 1 (user_id=2) coba submit lagi
    ↓
proses_penilaian.php check:
    SELECT COUNT(*) FROM ahp_penilaian_kriteria WHERE user_id=2
    Result: 10 (sudah ada dari sebelumnya)
    ↓
❌ REJECT! Tampilkan error:
   "Anda sudah pernah mengisi penilaian sebelumnya..."
    ↓
Redirect ke penilaian.php
```

---

## 📌 Status Display

### **Jika Belum Mengisi:**
```
┌─────────────────────────────────────┐
│ Form Penilaian (Normal)             │
├─────────────────────────────────────┤
│ [Tabel Perbandingan Kriteria]       │
│ [Tabel Perbandingan Alternatif]     │
│ [Tombol Submit]                     │
└─────────────────────────────────────┘
```

### **Jika Sudah Mengisi:**
```
┌─────────────────────────────────────┐
│ ⚠️ WARNING ALERT                    │
├─────────────────────────────────────┤
│ Anda sudah pernah mengisi penilaian │
│ sebelumnya. Setiap DM hanya bisa    │
│ mengisi 1 kali saja.                │
│                                     │
│ [Tombol: Lihat Hasil Penilaian]     │
└─────────────────────────────────────┘

Form tidak ditampilkan (display: none)
```

---

## 🔄 Cara Reset Data (Admin Only)

Jika perlu reset data DM tertentu (misal data salah input):

```sql
-- HATI-HATI! Ini menghapus data penilaian
DELETE FROM ahp_penilaian_kriteria WHERE user_id = 2;
DELETE FROM ahp_penilaian_alternatif WHERE user_id = 2;
DELETE FROM ahp_prioritas_final WHERE user_id = 2;
DELETE FROM borda_input WHERE user_id = 2;
```

**Setelah itu:** DM bisa mengisi penilaian lagi.

---

## ✅ File yang Diupdate

1. **`proses_penilaian.php`** - Tambah check count sebelum insert
2. **`penilaian.php`** - Tambah warning alert & hide form jika sudah submit
3. **`hitung_ahp.php`** - Sudah correct (tidak perlu ubah)

---

## 🎯 Keuntungan Implementasi

✅ **Mencegah Duplikasi Data**
- Tidak ada penumpukan record yang sama

✅ **Konsistensi Voting**
- Setiap DM hanya punya 1 set penilaian yang consistent

✅ **Database Efisien**
- Tidak ada perlu delete/cleanup duplikat

✅ **User Friendly**
- User tahu sudah mengisi dan tidak bisa mengisi lagi

✅ **Audit Trail**
- Admin bisa lihat kapan user mengisi (timestamp di database)

---

## 📝 Contoh Skenario

### **Skenario 1: Normal Flow**
```
Hari 1, Jam 10:00 - DM Rina submit penilaian
    → Data tersimpan di ahp_penilaian_kriteria
    → Bisa lihat hasil di hasil.php

Hari 1, Jam 14:00 - DM Rina buka penilaian.php lagi
    → Sistem check: Ada record untuk user_id Rina? YA
    → Tampilkan warning: "Anda sudah pernah mengisi..."
    → Form tidak ditampilkan
    → DM Rina bisa lihat "Lihat Hasil Penilaian"
```

### **Skenario 2: Coba Curang**
```
User mencoba bypass dengan F12 Developer Tools
    → Menghapus script `style="display:none;"`
    → Submit form lagi

Hasil:
    → proses_penilaian.php tetap check di database
    → Tetap di-REJECT karena data sudah ada
    → Tampilkan error message
    
✅ Aman! Tidak bisa bypass!
```

---

## 🛡️ Security Notes

- ✅ Check dilakukan di **backend** (proses_penilaian.php), bukan hanya frontend
- ✅ User tidak bisa bypass dengan manipulasi HTML/JavaScript
- ✅ Database constraint bisa ditambah jika perlu (UNIQUE key)
- ✅ Timestamp captured untuk audit trail

