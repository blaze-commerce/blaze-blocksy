---
title: "Cara Menggunakan Product Carousel Block"
description: "Panduan lengkap menggunakan custom Gutenberg block untuk menampilkan produk WooCommerce dalam carousel slider"
category: "guide"
last_updated: "2024-12-19"
framework: "wordpress"
domain: "catalog"
layer: "frontend"
tags: ["gutenberg", "woocommerce", "carousel", "panduan", "indonesia"]
---

# Cara Menggunakan Product Carousel Block

Panduan lengkap untuk menggunakan custom Gutenberg block yang menampilkan produk WooCommerce dalam bentuk carousel slider yang responsif.

## Overview

Product Carousel Block adalah custom block yang memungkinkan Anda menampilkan produk WooCommerce dalam bentuk slider yang menarik dan responsif. Block ini terintegrasi dengan template product card yang sudah ada dan menyediakan berbagai opsi konfigurasi.

## Cara Menambahkan Block

### Langkah 1: Buka Editor Gutenberg
1. Masuk ke halaman atau post yang ingin diedit
2. Pastikan menggunakan editor Gutenberg (bukan Classic Editor)

### Langkah 2: Tambahkan Block
1. Klik tombol "+" untuk menambah block baru
2. Cari "Product Carousel" di kotak pencarian
3. Atau temukan di kategori "WooCommerce"
4. Klik untuk menambahkan block

## Konfigurasi Block

### Panel Product Selection (Pemilihan Produk)

#### Product Categories (Kategori Produk)
- **Fungsi**: Memfilter produk berdasarkan kategori tertentu
- **Cara Penggunaan**: 
  - Centang kategori yang ingin ditampilkan
  - Biarkan kosong untuk menampilkan semua kategori
- **Tips**: Pilih maksimal 3-4 kategori untuk performa optimal

#### Sale Attribute (Atribut Penjualan)
Pilihan filter berdasarkan status produk:

- **All Products**: Semua produk
- **Featured Products**: Produk unggulan saja
- **On Sale Products**: Produk yang sedang diskon
- **New Products**: Produk baru (30 hari terakhir)
- **In Stock Products**: Produk yang tersedia
- **Out of Stock Products**: Produk yang habis

#### Maximum Products (Jumlah Maksimal Produk)
- **Range**: 1-50 produk
- **Default**: 12 produk
- **Rekomendasi**: 8-16 produk untuk performa optimal

### Panel Carousel Settings (Pengaturan Carousel)

#### Products Per Slide (Produk Per Slide)
Konfigurasi responsif untuk berbagai ukuran layar:

- **Desktop**: 1-8 produk per slide (rekomendasi: 4)
- **Tablet**: 1-6 produk per slide (rekomendasi: 3)
- **Mobile**: 1-4 produk per slide (rekomendasi: 2)

#### Navigation Controls (Kontrol Navigasi)

- **Show Navigation Arrows**: Tampilkan panah navigasi
  - Otomatis disembunyikan di mobile untuk UX yang lebih baik
- **Show Dots Pagination**: Tampilkan indikator titik
- **Enable Loop**: Aktifkan perulangan tak terbatas

#### Autoplay Settings (Pengaturan Autoplay)

- **Enable Autoplay**: Aktifkan slide otomatis
- **Autoplay Timeout**: Durasi antar slide (1000-10000ms)
  - Rekomendasi: 5000ms (5 detik)

#### Spacing (Jarak)

- **Margin Between Items**: Jarak antar produk (0-50px)
- **Default**: 24px
- **Mobile**: Otomatis menyesuaikan untuk layar kecil

## Best Practices

### Pemilihan Produk
1. **Kategori Spesifik**: Pilih kategori yang relevan dengan konten halaman
2. **Produk Unggulan**: Gunakan filter "Featured" untuk halaman utama
3. **Produk Sale**: Gunakan filter "On Sale" untuk halaman promosi

### Pengaturan Responsif
1. **Desktop (4 produk)**: Optimal untuk layar besar
2. **Tablet (3 produk)**: Seimbang antara visibilitas dan detail
3. **Mobile (2 produk)**: Memastikan produk tetap mudah dibaca

### Performa
1. **Batasi Jumlah**: Maksimal 16 produk per carousel
2. **Kategori Terbatas**: Pilih maksimal 3-4 kategori
3. **Autoplay Bijak**: Gunakan autoplay hanya jika diperlukan

## Contoh Penggunaan

### Homepage Hero Section
```
Konfigurasi:
- Categories: Produk Unggulan
- Sale Attribute: Featured Products
- Desktop: 4, Tablet: 3, Mobile: 2
- Autoplay: Enabled (5000ms)
- Navigation: Enabled
```

### Category Page Related Products
```
Konfigurasi:
- Categories: Kategori terkait
- Sale Attribute: All Products
- Desktop: 4, Tablet: 3, Mobile: 2
- Autoplay: Disabled
- Navigation: Enabled
```

### Sale/Promo Page
```
Konfigurasi:
- Categories: Semua kategori
- Sale Attribute: On Sale Products
- Desktop: 5, Tablet: 3, Mobile: 2
- Autoplay: Enabled (4000ms)
- Navigation: Enabled
```

## Troubleshooting

### Block Tidak Muncul di Editor
1. Pastikan plugin WooCommerce aktif
2. Periksa tema mendukung Gutenberg
3. Cek console browser untuk error JavaScript

### Carousel Tidak Berfungsi
1. Pastikan jQuery tersedia
2. Periksa konflik dengan plugin lain
3. Cek apakah Owl Carousel ter-load dengan benar

### Produk Tidak Tampil
1. Pastikan ada produk yang dipublikasi
2. Periksa filter kategori dan atribut
3. Pastikan produk memenuhi kriteria visibilitas

### Tampilan Tidak Responsif
1. Periksa pengaturan responsif di block
2. Cek konflik CSS dengan tema
3. Pastikan viewport meta tag ada di header

## Tips dan Trik

### Optimasi Performa
1. Gunakan CDN untuk gambar produk
2. Batasi jumlah produk yang ditampilkan
3. Aktifkan caching untuk halaman dengan carousel

### Desain yang Menarik
1. Pilih produk dengan gambar berkualitas tinggi
2. Pastikan konsistensi ukuran gambar
3. Gunakan margin yang cukup untuk readability

### SEO Friendly
1. Pastikan produk memiliki title dan description yang baik
2. Gunakan alt text untuk gambar produk
3. Pertimbangkan loading lazy untuk gambar

## Dukungan dan Pemeliharaan

### Update Rutin
- Monitor kompatibilitas dengan WordPress dan WooCommerce terbaru
- Update Owl Carousel jika ada versi baru
- Test dengan rilis Gutenberg terbaru

### Monitoring Performa
- Pantau waktu loading halaman dengan carousel
- Periksa penggunaan memory dengan set produk besar
- Optimasi query untuk performa yang lebih baik

## Dokumentasi Terkait

- [Product Carousel Technical Documentation](../components/product-carousel-gutenberg-block.md)
- [WooCommerce Product Card Styling](../features/woocommerce-product-card-border.md)
- [Panduan Integrasi Tema Blocksy](../blocksy-custom-element-development-guide.md)

## Kontak Support

Jika mengalami masalah atau butuh bantuan:
1. Periksa dokumentasi teknis terlebih dahulu
2. Aktifkan debug mode untuk melihat error detail
3. Hubungi tim development dengan informasi error yang lengkap
