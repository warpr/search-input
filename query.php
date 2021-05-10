<?php

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

