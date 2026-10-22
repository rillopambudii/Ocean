# Repositori Dokumen Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menu penyimpanan dokumen untuk Dokumen QHSE (Manual, Procedure, Work Instruction, Form/Checklist) dan Departemen (Technical, Crewing, Purchasing, Budget Plan & Monitoring).

**Architecture:** Satu tabel `documents` + model `Document` (berisi peta `CATEGORIES` slug → label & gate) + satu `DocumentController` dengan route `admin/documents/{category}/...`. File disimpan di disk `local` (privat). Akses dicek per kategori dengan `Gate::authorize`.

**Tech Stack:** Laravel 13, PHP 8.4, AdminLTE 3 (Blade), PHPUnit (sqlite in-memory).

Spec: `docs/superpowers/specs/2026-10-22-document-repository-design.md`

## Global Constraints

- Upload: `mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png`, `max:20480`.
- Dokumen QHSE → gate `qhse-access`; Technical/Crewing/Purchasing/Budget → gate `super-admin`.
- File di disk `local` (`storage/app/private`), tidak pernah di `public/`.
- Views extend `layouts.admin`, URL absolut gaya `/admin/...` seperti halaman lain.

---

### Task 1: Model, migrasi, controller, route (dengan test)

**Files:**
- Create: `database/migrations/2026_10_22_000000_create_documents_table.php`
- Create: `app/Models/Document.php`
- Create: `app/Http/Controllers/DocumentController.php`
- Modify: `routes/web.php` (di dalam grup `auth`, sebelum blok SUPER ADMIN)
- Create: `resources/views/admin/documents/index.blade.php`, `form.blade.php`
- Test: `tests/Feature/DocumentTest.php`

**Interfaces:**
- Produces: `Document::CATEGORIES` (`array<string, array{label:string, gate:string}>`), route `/admin/documents/{category}`.

- [ ] Step 1: Tulis `tests/Feature/DocumentTest.php` (upload, download, update ganti file, delete, 403 per role, 404 kategori, validasi mime).
- [ ] Step 2: `php artisan test --filter=DocumentTest` → FAIL (route/model belum ada).
- [ ] Step 3: Buat migrasi, model, controller, route, views (kode lengkap: lihat file di commit task ini).
- [ ] Step 4: `php artisan test --filter=DocumentTest` → PASS.
- [ ] Step 5: Commit `feat: repositori dokumen QHSE & departemen`.

### Task 2: Sidebar + migrasi DB lokal + uji di app

**Files:**
- Modify: `config/adminlte.php` (submenu "Dokumen QHSE" setelah Management Visit; header DEPARTEMEN setelah blok OPERATION)

- [ ] Step 1: Tambah item menu.
- [ ] Step 2: `php artisan migrate` di DB MySQL lokal.
- [ ] Step 3: Login sebagai admin, upload/unduh/hapus via curl atau browser; cek sidebar muncul.
- [ ] Step 4: `php artisan test` (seluruh suite) → PASS.
- [ ] Step 5: Commit `feat: menu sidebar dokumen`.
