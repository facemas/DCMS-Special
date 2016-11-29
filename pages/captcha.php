<?php

include_once '../sys/inc/start.php';

$x = 100; // ширина капчи
$y = 40; // высота

/**
 * Добавление нелинейных искажений к изображению, для усложнения распознавания изображения с проверочным кодом
 * @param gd2 $img
 * @return gd2
 */
function MultiWave($img) {
    $width = imagesx($img);
    $height = imagesy($img);

    $img2 = imagecreatetruecolor($width, $height);

    $rand1 = mt_rand(700000, 1000000) / 15000000;
    $rand2 = mt_rand(700000, 1000000) / 15000000;
    $rand3 = mt_rand(700000, 1000000) / 15000000;
    $rand4 = mt_rand(700000, 1000000) / 15000000;
    // фазы
    $rand5 = mt_rand(0, 3141592) / 1000000;
    $rand6 = mt_rand(0, 3141592) / 1000000;
    $rand7 = mt_rand(0, 3141592) / 1000000;
    $rand8 = mt_rand(0, 3141592) / 1000000;
    // амплитуды
    $rand9 = mt_rand(400, 600) / 100;
    $rand10 = mt_rand(400, 600) / 100;

    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            // координаты пикселя-первообраза.
            $sx = $x + (sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6)) * $rand9;
            $sy = $y + (sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8)) * $rand10;
            // первообраз за пределами изображения
            if ($sx < 0 || $sy < 0 || $sx >= $width - 1 || $sy >= $height - 1) {
                $color = 255;
                $color_x = 255;
                $color_y = 255;
                $color_xy = 255;
            } else { // цвета основного пикселя и его 3-х соседей для лучшего антиалиасинга
                $color = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
                $color_x = (imagecolorat($img, $sx + 1, $sy) >> 16) & 0xFF;
                $color_y = (imagecolorat($img, $sx, $sy + 1) >> 16) & 0xFF;
                $color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
            }
            // сглаживаем только точки, цвета соседей которых отличается
            if ($color == $color_x && $color == $color_y && $color == $color_xy) {
                $newcolor = $color;
            } else {
                $frsx = $sx - floor($sx); //отклонение координат первообраза от целого
                $frsy = $sy - floor($sy);
                $frsx1 = 1 - $frsx;
                $frsy1 = 1 - $frsy;
                // вычисление цвета нового пикселя как пропорции от цвета основного пикселя и его соседей
                $newcolor = floor($color * $frsx1 * $frsy1 + $color_x * $frsx * $frsy1 + $color_y * $frsx1 * $frsy + $color_xy * $frsx * $frsy);
            }
            imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
        }
    }

    return $img2;
}

$png = false;
if (imagetypes() & IMG_PNG) {
    $png = true;
}
$gif = false;
if (imagetypes() & IMG_GIF) {
    $gif = true;
}
$jpg = false;
if (imagetypes() & IMG_JPG) {
    $jpg = true;
}

if (empty($_GET['captcha_session'])) {
    exit;
}
if (!$code = captcha::getCode($_GET['captcha_session'])) {
    exit;
}

$img = imagecreatetruecolor($x, $y);
imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));


for ($i = 0; $i < 5; $i++) {
    $n = $code {$i};
    if ($png) {
        $num[$n] = imagecreatefrompng(H . '/sys/images/captcha/' . $n . '.png');
    } elseif ($gif) {
        $num[$n] = imagecreatefromgif(H . '/sys/images/captcha/' . $n . '.gif');
    } elseif ($jpg) {
        $num[$n] = imagecreatefromjpeg(H . '/sys/images/captcha/' . $n . '.jpg');
    }
    imagecopy($img, $num[$n], $i * 10 + mt_rand(18,21), mt_rand(7,9), 0, 0, 15, 20);
}

// искажаем картинку
$img = MultiWave($img);

if (function_exists('imagefilter')) {
    // придаем картинке легкий цветовой оттенок, если поддерживаются фильтры
    imagefilter($img, IMG_FILTER_COLORIZE, mt_rand(40, 90), mt_rand(40, 90), mt_rand(40, 90));
}

@ob_end_clean();
if ($jpg) {
    header("Content-type: image/jpeg");
    imagejpeg($img, null, 80);
} elseif ($png) {
    header("Content-type: image/png");
    imagepng($img);
} elseif ($gif) {
    header("Content-type: image/gif");
    imagegif($img);
}