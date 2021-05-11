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
?>
<html>
    <head>
        <script src="search-input.js" async></script>
        <link rel="stylesheet" href="search-input.css"></link>
    </head>
    <body>
        <h1>search dropdown</h1>

        <search-input action="query.php" placeholder="Search...">
            <search-loading>
                <div style="display: inline-block; vertical-align: bottom;">
                    <svg width="22px" height="22px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                        <circle cx="50" cy="50" fill="none" stroke="#888888" stroke-width="8" r="32" stroke-dasharray="150 50">
                            <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1">
                            </animateTransform>
                        </circle>
                    </svg>
                </div>
            </search-loading>
        </search-input>
    </body>
</html>
