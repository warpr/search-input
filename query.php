<?php

require_once __DIR__ . '/colors.php';

$q = $_GET['q'];

?>
<ul>
<?php
foreach (get_colors($q) as $key => $val) {
    render_color($key, $val);
}
?>
</ul>
