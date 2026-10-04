# Tugas Mandiri: Perancangan ERD E-Library Kampus

## 1. Desain ERD Logis (Entitas dan Atribut)

Dalam perancangan basis data ini, terdapat 5 entitas utama (termasuk satu entitas junction untuk memecah relasi many-to-many antara Transaksi_Peminjaman dan Buku):

1. **Mahasiswa**
   - `NIM` (Primary Key)
   - `Nama_Lengkap`
   - `Fakultas`
   - `Program_Studi`
   - `No_Telepon`

2. **Penerbit**
   - `ID_Penerbit` (Primary Key)
   - `Nama_Penerbit`
   - `Alamat_Kota`
   - `Email`

3. **Buku**
   - `ISBN` (Primary Key)
   - `Judul_Buku`
   - `Pengarang`
   - `Tahun_Terbit`
   - `ID_Penerbit` (Foreign Key - referensi ke Penerbit)

4. **Transaksi_Peminjaman**
   - `ID_Peminjaman` (Primary Key)
   - `NIM` (Foreign Key - referensi ke Mahasiswa)
   - `Tanggal_Pinjam`
   - `Tanggal_Tenggat`

5. **Detail_Peminjaman** _(Entitas Junction)_
   - `ID_Peminjaman` (Composite Primary Key, Foreign Key - referensi ke Peminjaman)
   - `ISBN` (Composite Primary Key, Foreign Key - referensi ke Buku)
   - `Tanggal_Kembali`
   - `Denda`
   - `Status_Pengembalian`

## 2. Visualisasi Relasi Kunci (ERD)

Relasi antar-entitas pada sistem E-Library adalah:

- Mahasiswa dapat melakukan banyak peminjaman.
- Setiap peminjaman dilakukan oleh satu mahasiswa.
- Penerbit dapat menerbitkan banyak buku.
- Setiap buku berasal dari satu penerbit.
- Satu peminjaman dapat memiliki banyak buku.
- Satu buku dapat tercatat pada banyak transaksi peminjaman.
- Relasi many-to-many antara Transaksi \_Peminjaman dan Buku dipecahkan menggunakan `Detail_Peminjaman`.

```mermaid
erDiagram
    MAHASISWA ||--o{ TRANSAKSI_PEMINJAMAN : "melakukan"
    PENERBIT ||--o{ BUKU : "menerbitkan"
    TRANSAKSI_PEMINJAMAN ||--|{ DETAIL_PEMINJAMAN : "memiliki item"
    BUKU ||--o{ DETAIL_PEMINJAMAN : "tercatat sebagai item"

    MAHASISWA {
        varchar NIM PK
        varchar Nama_Lengkap
        varchar Fakultas
        varchar Program_Studi
        varchar No_Telepon
    }

    PENERBIT {
        varchar ID_Penerbit PK
        varchar Nama_Penerbit
        varchar Alamat_Kota
        varchar Email
    }

    BUKU {
        varchar ISBN PK
        varchar Judul_Buku
        varchar Pengarang
        int Tahun_Terbit
        varchar ID_Penerbit FK
    }

    TRANSAKSI_PEMINJAMAN {
        varchar ID_Peminjaman PK
        varchar NIM FK
        date Tanggal_Pinjam
        date Tanggal_Tenggat
    }

    DETAIL_PEMINJAMAN {
        varchar ID_Peminjaman PK,FK
        varchar ISBN PK,FK
        date Tanggal_Kembali
        decimal Denda
        varchar Status_Pengembalian
    }
```

## 3. Simulasi Normalisasi

### 3.1 Bentuk Tidak Normal (UNF)

Pada tahap UNF, data peminjaman masih mengandung repeating group. Satu transaksi peminjaman dapat memiliki beberapa buku yang disimpan dalam `DAFTAR_BUKU`.

```mermaid
erDiagram
    DATA_MENTAH_UNF {
        varchar ID_Peminjaman
        date Tanggal_Pinjam
        date Tanggal_Tenggat
        varchar NIM
        varchar Nama_Mahasiswa
        varchar Fakultas
        string DAFTAR_BUKU
    }
```

`DAFTAR_BUKU` merupakan repeating group yang berisi beberapa data buku, yaitu:

- ISBN
- Judul_Buku
- Pengarang
- ID_Penerbit
- Tahun_Terbit
- Tanggal_Kembali

Karena satu atribut masih dapat berisi lebih dari satu kelompok data buku, struktur tersebut belum memenuhi 1NF.

### 3.2 Bentuk Normal Pertama (1NF)

Pada tahap 1NF, repeating group dihilangkan. Setiap atribut harus memiliki nilai yang atomik.

Jika satu transaksi meminjam tiga buku, maka transaksi tersebut dicatat menjadi tiga baris berbeda.

Struktur tabel pada tahap 1NF:

```mermaid
erDiagram
    TABEL_UNIVERSAL_1NF {
        varchar ID_Peminjaman PK
        varchar ISBN PK
        date Tanggal_Pinjam
        date Tanggal_Tenggat
        varchar NIM
        varchar Nama_Mahasiswa
        varchar Fakultas
        varchar Judul_Buku
        varchar Pengarang
        varchar ID_Penerbit
        int Tahun_Terbit
        date Tanggal_Kembali
    }
```

Primary Key pada tahap 1NF adalah kombinasi:

`ID_Peminjaman + ISBN`

Kombinasi tersebut memastikan setiap buku dalam suatu transaksi peminjaman dapat diidentifikasi secara unik.

## 3.3 Bentuk Normal Kedua (2NF)

Pada 2NF, dependensi parsial dihilangkan.

Atribut yang hanya bergantung pada `ID_Peminjaman` dipisahkan ke tabel `TRANSAKSI_PEMINJAMAN`.

Atribut yang hanya bergantung pada `ISBN` dipisahkan ke tabel `BUKU`.

Atribut yang bergantung pada kombinasi `ID_Peminjaman` dan `ISBN` ditaruh pada entitas baru, yaitu `DETAIL_PEMINJAMAN`.

```mermaid
erDiagram
    TRANSAKSI_PEMINJAMAN ||--|{ DETAIL_PEMINJAMAN : "memiliki"
    BUKU ||--o{ DETAIL_PEMINJAMAN : "tercatat"

    TRANSAKSI_PEMINJAMAN {
        varchar ID_Peminjaman PK
        varchar NIM
        varchar Nama_Mahasiswa
        varchar Fakultas
        date Tanggal_Pinjam
        date Tanggal_Tenggat
    }

    BUKU {
        varchar ISBN PK
        varchar Judul_Buku
        varchar Pengarang
        varchar ID_Penerbit
        varchar Nama_Penerbit
        int Tahun_Terbit
    }

    DETAIL_PEMINJAMAN {
        varchar ID_Peminjaman PK,FK
        varchar ISBN PK,FK
        date Tanggal_Kembali
        decimal Denda
        varchar Status_Pengembalian
    }
```

Pada tahap ini:

- `ID_Peminjaman` menjadi Primary Key pada `TRANSAKSI_PEMINJAMAN`.
- `ISBN` menjadi Primary Key pada `BUKU`.
- Kombinasi `ID_Peminjaman` dan `ISBN` menjadi Primary Key komposit pada `DETAIL_PEMINJAMAN`.
- `DETAIL_PEMINJAMAN` menjadi penghubung antara `TRANSAKSI_PEMINJAMAN` dan `BUKU`.
