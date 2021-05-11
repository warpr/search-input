<?php
/**
 *   This file is part of lûd, an opinionated browser based media player.
 *   Copyright (C) 2020  Kuno Woudt <kuno@frob.nl>
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of copyleft-next 0.3.1.  See copyleft-next-0.3.1.txt.
 *
 *   SPDX-License-Identifier: copyleft-next-0.3.1
 */

require_once __DIR__ . '/colors.php';

function render_results($q) {
    ?>
    <ul>
        <?php
        $items = get_colors($q);
        if (empty($items)) {
           echo '<li>No results</li>';
        } else {
            foreach ($items as $key => $val) {
                render_color($key, $val);
            }
        }
        ?>
    </ul>
    <?php
}

$q = $_GET['q'];

// sleep(2);

if (empty($q)) {
    echo '<ul></ul>';
} else {
    render_results($q);
}

