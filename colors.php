<?php

function contains($haystack, $needle)
{
    return strpos($haystack, $needle) !== false;
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
}

function get_colors($search = null) {
    $colorsJson = file_get_contents("node_modules/css-color-names/css-color-names.json");
    $colors = json_decode($colorsJson, true);
    asort($colors);

    if (empty($search)) {
        return $colors;
    }

    $ret = [];
    foreach ($colors as $name => $hex) {
        if (contains($name, $search) || contains($hex, $search)) {
            $ret[$name] = $hex;
        }
    }

    return $ret;
}

function html2rgb($color)
{
    if ($color[0] == '#') {
        $color = substr($color, 1);
    }

    if (strlen($color) == 6) {
        list($r, $g, $b) = [$color[0].$color[1], $color[2].$color[3], $color[4].$color[5]];
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = [$color[0].$color[0], $color[1].$color[1], $color[2].$color[2]];
    }  else {
        return false;
    }

    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);

    return [$r, $g, $b];
}

function render_color($name, $hex) {
    $fg = 'white';
    if (array_sum(html2rgb($hex)) > (0x80 * 3)) {
        $fg = 'black';
    }
    ?>
    <li>
    <span class="badge" style="background: <?= $hex ?>; color: <?= $fg ?>">color</span>
    <span class="result"><a href="/select/<?= $hex ?>"><?= $name ?></a></span>
    <span class="subdued"><?= $hex ?></span>
    </li>
    <?php
}
