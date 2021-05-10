<?php

require_once __DIR__ . '/colors.php';

function render_results($q) {
    ?>
    <drop-down for='#search-input' id="search-results" style="display: block">
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
    </drop-down>
    <?php
}

function render_dropdown_closed() {
    ?>
    <drop-down for='#search-input' id="search-results" style="display: none">
    </drop-down>
    <?php
}

$q = $_GET['q'];

// sleep(2);

if (empty($q)) {
    render_dropdown_closed();
} else {
    render_results($q);
}
