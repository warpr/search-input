<?php

require_once __DIR__ . '/colors.php';

function render_results($q) {
    ?>
    <drop-old for='#search-input' id="search-results" style="display: block">
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
    </drop-old>
    <?php
}

function render_dropdown_closed() {
    ?>
    <drop-old for='#search-input' id="search-results" style="display: none">
    </drop-old>
    <?php
}

$q = $_GET['q'];

// sleep(2);

if (empty($q)) {
    render_dropdown_closed();
} else {
    render_results($q);
}

