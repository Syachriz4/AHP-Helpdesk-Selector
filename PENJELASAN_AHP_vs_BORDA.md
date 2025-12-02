# 🎓 Penjelasan: AHP Comparison vs Borda Ranking

## ❓ Pertanyaan Anda
> Input ranking di borda_input itu hasil akhir penilaian AHP atau nilai perbandingan tiap alternatif?

**Jawaban: Keduanya BERBEDA!** Mari kita jelaskan step-by-step.

---

## 📊 PHASE 1: AHP (Analytical Hierarchy Process)

### Apa itu AHP Comparison?
AHP adalah metode untuk melakukan **pairwise comparison** (perbandingan berpasangan).

### Contoh Input AHP (Perbandingan):
Setiap DM melakukan perbandingan **DEMI DEMI** untuk setiap kriteria:

**Contoh: Kriteria "Harga"**
```
Dibandingkan yang mana LEBIH BAIK dari segi Harga?
┌─────────────────────────────────┐
│ Zendesk    vs    osTicket       │
│     Pilih: 1 3 5 7 9            │
│            ↑                      │
│     (1=sama, 9=jauh lebih baik)  │
└─────────────────────────────────┘

┌─────────────────────────────────┐
│ Zendesk    vs    UVdesk         │
│     Pilih: 1 3 5 7 9            │
└─────────────────────────────────┘

... dan seterusnya untuk semua pasangan
```

**Ini adalah DATA PERBANDINGAN (bukan ranking!)**

| Perbandingan | Skor Pilihan | Arti |
|-------------|-------------|------|
| Zendesk vs osTicket | 3 | Zendesk **sedikit lebih baik** |
| Zendesk vs UVdesk | 5 | Zendesk **lebih baik** |
| osTicket vs UVdesk | 0.33 | osTicket **lebih buruk** (kebalikan dari 3) |

### Output AHP (Hasil Perhitungan):
Dari perbandingan di atas, kita kalkulasi **bobot/prioritas** setiap sistem:

```
Hasil Akhir AHP untuk Kriteria "Harga":
┌──────────────────────────────┐
│ Zendesk:   0.523 (52.3%)     │ ← Alternatif terbaik
│ osTicket:  0.298 (29.8%)     │
│ UVdesk:    0.179 (17.9%)     │
│ Zammad:    0.000 (0%)        │
│ Manual:    0.000 (0%)        │
└──────────────────────────────┘
```

**Ini adalah BOBOT/PRIORITAS (bukan ranking!)**

---

## 🏆 PHASE 2: RANKING (Hasil Akhir AHP)

### Dari Bobot ke Ranking:
Setelah menghitung bobot untuk SEMUA kriteria, kita gabungkan hasilnya:

```
Bobot Akhir untuk Semua Kriteria:
┌────────────────────────────────┐
│ Zendesk:   0.4520  ← Tertinggi │
│ osTicket:  0.3105  ← Tertinggi kedua │
│ UVdesk:    0.1895  ← Tertinggi ketiga │
│ Zammad:    0.0380  ← Tertinggi keempat │
│ Manual:    0.0100  ← Terendah │
└────────────────────────────────┘

Dikonversi ke RANKING:
┌────────────────────┐
│ Zendesk:   RANKING 1 │ ✅
│ osTicket:  RANKING 2 │ ✅
│ UVdesk:    RANKING 3 │ ✅
│ Zammad:    RANKING 4 │ ✅
│ Manual:    RANKING 5 │ ✅
└────────────────────┘
```

**Ini adalah RANKING PERSONAL** (masing-masing DM punya ranking sendiri)

---

## 🗳️ PHASE 3: BORDA COUNT VOTING

### Input Borda (Ranking Akhir AHP):
`borda_input` menyimpan **RANKING AKHIR dari AHP**:

```
DM 1 (Rina - Manager):
- Zendesk:   Ranking 1
- osTicket:  Ranking 2
- UVdesk:    Ranking 3
- Zammad:    Ranking 4
- Manual:    Ranking 5

DM 2 (Ahmad - Staff):
- osTicket:  Ranking 1
- Zendesk:   Ranking 2
- UVdesk:    Ranking 3
- Zammad:    Ranking 4
- Manual:    Ranking 5

DM 3 (Siti - Staff):
- UVdesk:    Ranking 1
- Zendesk:   Ranking 2
- osTicket:  Ranking 3
- Manual:    Ranking 4
- Zammad:    Ranking 5
```

### Tabel borda_input di Database:
```sql
INSERT INTO borda_input (user_id, alternatif_id, ranking) VALUES
-- DM 1 (user_id=2)
(2, 1, 1),  -- Zendesk rank 1
(2, 2, 2),  -- osTicket rank 2
(2, 3, 3),  -- UVdesk rank 3
(2, 4, 4),  -- Zammad rank 4
(2, 5, 5),  -- Manual rank 5

-- DM 2 (user_id=3)
(3, 1, 2),  -- Zendesk rank 2
(3, 2, 1),  -- osTicket rank 1
(3, 3, 3),  -- UVdesk rank 3
(3, 4, 4),  -- Zammad rank 4
(3, 5, 5),  -- Manual rank 5

-- DM 3 (user_id=4)
(4, 1, 2),  -- Zendesk rank 2
(4, 2, 3),  -- osTicket rank 3
(4, 3, 1),  -- UVdesk rank 1
(4, 4, 5),  -- Zammad rank 5
(4, 5, 4);  -- Manual rank 4
```

---

## 🧮 PHASE 4: HITUNG BORDA COUNT

### Formula Borda:
```
Skor Borda = Jumlah Alternatif - Ranking + 1

Dengan 5 alternatif:
- Ranking 1 = 5 - 1 + 1 = 5 poin
- Ranking 2 = 5 - 2 + 1 = 4 poin
- Ranking 3 = 5 - 3 + 1 = 3 poin
- Ranking 4 = 5 - 4 + 1 = 2 poin
- Ranking 5 = 5 - 5 + 1 = 1 poin
```

### Perhitungan Borda untuk Setiap Sistem:
```
ZENDESK:
├─ DM 1: Ranking 1 = 5 poin
├─ DM 2: Ranking 2 = 4 poin
├─ DM 3: Ranking 2 = 4 poin
└─ TOTAL: 5 + 4 + 4 = 13 poin ✅ PERINGKAT 1

OSTÍCKET:
├─ DM 1: Ranking 2 = 4 poin
├─ DM 2: Ranking 1 = 5 poin
├─ DM 3: Ranking 3 = 3 poin
└─ TOTAL: 4 + 5 + 3 = 12 poin ✅ PERINGKAT 2

UVDESK:
├─ DM 1: Ranking 3 = 3 poin
├─ DM 2: Ranking 3 = 3 poin
├─ DM 3: Ranking 1 = 5 poin
└─ TOTAL: 3 + 3 + 5 = 11 poin ✅ PERINGKAT 3

ZAMMAD:
├─ DM 1: Ranking 4 = 2 poin
├─ DM 2: Ranking 4 = 2 poin
├─ DM 3: Ranking 5 = 1 poin
└─ TOTAL: 2 + 2 + 1 = 5 poin ✅ PERINGKAT 4

MANUAL:
├─ DM 1: Ranking 5 = 1 poin
├─ DM 2: Ranking 5 = 1 poin
├─ DM 3: Ranking 4 = 2 poin
└─ TOTAL: 1 + 1 + 2 = 4 poin ✅ PERINGKAT 5
```

### Hasil Akhir Borda (borda_hasil):
```sql
INSERT INTO borda_hasil (alternatif_id, skor_borda, peringkat) VALUES
(1, 13, 1),  -- Zendesk: 13 poin, Peringkat 1 🥇
(2, 12, 2),  -- osTicket: 12 poin, Peringkat 2 🥈
(3, 11, 3),  -- UVdesk: 11 poin, Peringkat 3 🥉
(4, 5, 4),   -- Zammad: 5 poin, Peringkat 4
(5, 4, 5);   -- Manual: 4 poin, Peringkat 5
```

---

## 📋 Perbandingan: AHP Input vs Borda Input

| Aspek | AHP Input (Perbandingan) | Borda Input (Ranking) |
|-------|--------------------------|----------------------|
| **Data** | Nilai perbandingan (1, 3, 5, 7, 9) | Ranking 1-5 |
| **Tabel** | ahp_penilaian_kriteria, ahp_penilaian_alternatif | borda_input |
| **Proses** | Pairwise comparison → Bobot → Ranking AHP | Ranking AHP → Voting → Borda Count |
| **Contoh** | "Zendesk lebih baik dari osTicket dengan nilai 5" | "DM pilih Zendesk ranking 1" |
| **Jumlah data** | Banyak (10 perbandingan per kriteria × 5 kriteria) | Sedikit (5 ranking per DM) |
| **Output** | Prioritas/bobot (0.452, 0.310, dll) | Ranking (1, 2, 3, 4, 5) |
| **Final Result** | Ranking personal AHP | Ranking grup dari Borda |

---

## 🔄 Alur Lengkap (START TO FINISH)

```
┌─────────────────────────────────────────────────────────────────┐
│ STEP 1: VOTING AHP (Setiap DM lakukan)                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ Input: Perbandingan Pairwise (1, 3, 5, 7, 9)                  │
│   └─ Zendesk vs osTicket: 5 (Zendesk lebih baik)              │
│   └─ Zendesk vs UVdesk: 3 (Zendesk sedikit lebih baik)        │
│   └─ ... (total 10+25 perbandingan)                            │
│                                                                 │
│ Processing: Hitung bobot/prioritas (AHP Algorithm)             │
│   └─ Normalize matrix                                          │
│   └─ Calculate eigenvalue                                      │
│   └─ Get priority weights                                      │
│                                                                 │
│ Output: Ranking Personal (1-5)                                 │
│   └─ Zendesk: Ranking 1 (bobot 0.452)                         │
│   └─ osTicket: Ranking 2 (bobot 0.310)                        │
│   └─ UVdesk: Ranking 3 (bobot 0.189)                          │
│   └─ Zammad: Ranking 4 (bobot 0.038)                          │
│   └─ Manual: Ranking 5 (bobot 0.010)                          │
│                                                                 │
│ Simpan ke: ahp_prioritas_final                                │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
          ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 2: SAVE RANKING TO BORDA INPUT                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ Setelah AHP selesai, ranking disimpan ke borda_input           │
│                                                                 │
│ DM 1 (Rina):  Zendesk(1), osTicket(2), UVdesk(3), ...        │
│ DM 2 (Ahmad): osTicket(1), Zendesk(2), UVdesk(3), ...        │
│ DM 3 (Siti):  UVdesk(1), Zendesk(2), osTicket(3), ...        │
│                                                                 │
│ Simpan ke: borda_input                                         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
          ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 3: HITUNG BORDA COUNT (Manager Only)                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ Input: Ranking dari semua DM (dari borda_input)                │
│                                                                 │
│ Processing: Borda Count Formula                                │
│   └─ Zendesk: 5 + 4 + 4 = 13 poin                             │
│   └─ osTicket: 4 + 5 + 3 = 12 poin                            │
│   └─ UVdesk: 3 + 3 + 5 = 11 poin                              │
│   └─ Zammad: 2 + 2 + 1 = 5 poin                               │
│   └─ Manual: 1 + 1 + 2 = 4 poin                               │
│                                                                 │
│ Output: Final Ranking dengan Skor                              │
│   └─ Zendesk: 13 poin (Peringkat 1) 🥇                        │
│   └─ osTicket: 12 poin (Peringkat 2) 🥈                       │
│   └─ UVdesk: 11 poin (Peringkat 3) 🥉                         │
│   └─ Zammad: 5 poin (Peringkat 4)                             │
│   └─ Manual: 4 poin (Peringkat 5)                             │
│                                                                 │
│ Simpan ke: borda_hasil                                         │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
          ↓
┌─────────────────────────────────────────────────────────────────┐
│ STEP 4: LIHAT HASIL (Manager Only)                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ hasil.php menampilkan:                                         │
│   • Status Voting (sudah/belum)                                │
│   • Tombol "Hitung Borda"                                      │
│   • Hasil Akhir (Private untuk Manager)                        │
│                                                                 │
│ ⚠️ PERHATIAN: Hasil HANYA bisa dilihat Manager IT             │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## ✅ Kesimpulan

### Input ke borda_input adalah:
✅ **RANKING AKHIR dari AHP** (Ranking 1-5 per sistem)

### BUKAN:
❌ Nilai perbandingan AHP (1, 3, 5, 7, 9)  
❌ Bobot/prioritas AHP (0.452, 0.310, dll)  
❌ Raw data pairwise comparison  

### Alur:
```
AHP Perbandingan (1,3,5,7,9) 
    ↓ (Proses AHP)
AHP Ranking (1-5) 
    ↓ (Simpan ke borda_input)
Borda Voting Data 
    ↓ (Manager hitung)
Borda Result (Ranking + Skor)
```

---

## 🎯 Praktik di Code

### Di hasil.php (menampilkan voting status):
```php
$statusQuery = query("
    SELECT u.nama,
           CASE WHEN COUNT(bi.id) > 0 THEN 'Sudah Voting' ELSE 'Belum Voting' END as status
    FROM users u
    LEFT JOIN borda_input bi ON u.user_id = bi.user_id
    WHERE u.role = 'dm'
    GROUP BY u.user_id
");
```

**Penjelasan:**
- `COUNT(bi.id) > 0` = Jika ada record di borda_input, berarti DM sudah melakukan voting
- Ini HANYA bisa terjadi setelah AHP selesai dan ranking disimpan
- Jika AHP belum dilakukan, borda_input akan kosong untuk DM itu

### Di proses_borda.php (menghitung Borda):
```php
$userRankings = query("SELECT * FROM borda_input WHERE user_id = $userid ORDER BY ranking ASC");

foreach ($userRankings as $rank) {
    $skor = $jumlahAlternatif - $rank['ranking'] + 1;
    // Contoh: 5 - 1 + 1 = 5 poin (untuk ranking 1)
    // Contoh: 5 - 2 + 1 = 4 poin (untuk ranking 2)
}
```

**Penjelasan:**
- `borda_input` berisi RANKING (1, 2, 3, 4, 5)
- BUKAN nilai perbandingan atau bobot
- Langsung dihitung dengan formula Borda

