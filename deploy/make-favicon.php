<?php
/**
 * Generate public/favicon.ico dari logo Oceanindo.
 *
 * Jalankan dari root project:  php deploy/make-favicon.php
 *
 * ICO yang dihasilkan berisi dua gambar PNG (32px & 64px) supaya tajam di
 * layar biasa maupun hidpi. Format "PNG di dalam ICO" didukung semua browser
 * modern, dan karena filenya ada di public/favicon.ico browser otomatis
 * menemukannya tanpa perlu tag <link> di Blade.
 */

$source = __DIR__ . '/../public/logo-oceanindo.png';
$target = __DIR__ . '/../public/favicon.ico';
$sizes  = [32, 64];

$src = imagecreatefrompng($source);
if (! $src) {
    fwrite(STDERR, "Gagal membaca {$source}\n");
    exit(1);
}

$srcW = imagesx($src);
$srcH = imagesy($src);

$pngs = [];

foreach ($sizes as $size) {
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

    // Muat logo ke dalam kotak sambil menjaga rasio aspek, lalu pusatkan.
    $scale = min($size / $srcW, $size / $srcH);
    $dstW  = max(1, (int) round($srcW * $scale));
    $dstH  = max(1, (int) round($srcH * $scale));
    $dstX  = (int) (($size - $dstW) / 2);
    $dstY  = (int) (($size - $dstH) / 2);

    imagealphablending($canvas, true);
    imagecopyresampled($canvas, $src, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
    imagealphablending($canvas, false);

    ob_start();
    imagepng($canvas, null, 9);
    $pngs[$size] = ob_get_clean();

    imagedestroy($canvas);
}

imagedestroy($src);

// ICONDIR: reserved, type (1 = icon), jumlah gambar.
$ico = pack('vvv', 0, 1, count($pngs));

// Data gambar diletakkan setelah seluruh ICONDIRENTRY (16 byte per entri).
$offset = 6 + (16 * count($pngs));

foreach ($pngs as $size => $png) {
    $ico .= pack(
        'CCCCvvVV',
        $size === 256 ? 0 : $size,  // lebar (0 berarti 256)
        $size === 256 ? 0 : $size,  // tinggi
        0,                          // jumlah warna palet (0 = truecolor)
        0,                          // reserved
        1,                          // color planes
        32,                         // bit per pixel
        strlen($png),
        $offset
    );
    $offset += strlen($png);
}

$ico .= implode('', $pngs);

file_put_contents($target, $ico);

printf("favicon.ico dibuat: %d byte (%s)\n", strlen($ico), implode('px, ', $sizes) . 'px');
