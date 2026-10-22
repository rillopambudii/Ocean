# Repositori Dokumen (QHSE + Departemen) — Design

Tanggal: 2026-10-22

## Tujuan

Menambah menu penyimpanan dokumen (upload / lihat / unduh / edit / hapus) untuk:

- **QHSE → Dokumen QHSE**: Manual, Procedure, Work Instruction, Form/Checklist
- **DEPARTEMEN**: Technical, Crewing, Purchasing, Budget Plan & Monitoring

Semua menu hanya menyimpan dokumen — tidak ada fitur/data lain.

## Pendekatan

Satu tabel `documents`, satu model `Document`, satu `DocumentController`. Menu dibedakan oleh kolom
`category`. Daftar kategori (slug → label, gate akses) didefinisikan sekali di model `Document::CATEGORIES`.

| Slug | Label | Gate |
|------|-------|------|
| manual | Manual | qhse-access |
| procedure | Procedure | qhse-access |
| work-instruction | Work Instruction | qhse-access |
| form-checklist | Form/Checklist | qhse-access |
| technical | Technical | super-admin |
| crewing | Crewing | super-admin |
| purchasing | Purchasing | super-admin |
| budget | Budget Plan & Monitoring | super-admin |

## Data (`documents`)

- `id`
- `category` (string, index)
- `title` (string, wajib)
- `document_number` (string, nullable)
- `revision` (string, nullable)
- `file_path` (string) — path di disk `local` (privat, `storage/app/private`)
- `original_name` (string)
- `uploaded_by` (FK users, nullable, null on delete)
- timestamps

## Route

Di dalam grup `auth` yang sudah ada:

```
GET    admin/documents/{category}                 index   (+ ?q= pencarian judul/no. dokumen)
GET    admin/documents/{category}/create          create
POST   admin/documents/{category}                 store
GET    admin/documents/{category}/{document}/edit edit
PUT    admin/documents/{category}/{document}      update  (file opsional; jika diisi, file lama dihapus)
DELETE admin/documents/{category}/{document}      destroy (file di disk ikut dihapus)
GET    admin/documents/{category}/{document}/file download (inline untuk PDF/gambar, attachment selain itu)
```

Kategori tidak dikenal → 404. Dokumen yang `category`-nya tidak cocok dengan URL → 404.
Akses dicek di controller dengan `Gate::authorize($gate)` sesuai kategori → 403 bila tidak berhak.

## Validasi upload

`file`: wajib saat create, opsional saat edit; `mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png`; `max:20480` (20 MB).
`title`: wajib, max 255. `document_number`, `revision`: opsional, max 100.

## Tampilan

`resources/views/admin/documents/{index,form}.blade.php`, extends `layouts.admin`, gaya sama dengan halaman
admin lain (tabel bootstrap, tombol aksi). Kolom: No, Judul, No. Dokumen, Revisi, File, Diunggah oleh, Tanggal, Aksi.

## Sidebar (`config/adminlte.php`)

- Di bawah header QHSE: item lipat **Dokumen QHSE** (`can: qhse-access`) dengan 4 submenu.
- Header baru **DEPARTEMEN** (`can: super-admin`) dengan 4 item.

## Pengujian

Feature test (`tests/Feature/DocumentTest.php`, sqlite in-memory, `Storage::fake`):
upload tersimpan, unduh, update ganti file menghapus file lama, hapus menghapus file,
Admin QHSE ditolak (403) di `technical`, Admin Operation ditolak di `procedure`, kategori tak dikenal 404,
validasi tipe file.
