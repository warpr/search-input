<?php

require_once __DIR__ . '/colors.php';

?>
<html>
    <head>
        <script src="https://unpkg.com/htmx.org@1.3.3"></script>

        <script>
            function listen(element, event, callback) {
                element.addEventListener(event, callback);
                return () => element.removeEventListener(event, callback);
            }

            class DropOld extends HTMLElement {
                unsubscribes;
                search_input;

                closeDropdown() {
                    document.getElementById('search-results').style.display = 'none';
                }

                openDropdown() {
                    const results = document.getElementById('search-results');
                    if (results.style.display !== 'block') {
                        results.style.display = 'block';
                    }
                }

                inputKeyDown(event) {
                    if (event.ctrlKey || event.isComposing || event.metaKey || event.shiftKey) {
                        return;
                    }

                    if (event.key == 'ArrowDown' || event.key == 'Enter') {
                        event.preventDefault();
                        this.querySelector('li a')?.focus();
                    }

                    if (event.key == 'Escape') {
                        this.closeDropdown();
                    }
                }

                dropdownKeyDown(event) {
                    if (event.ctrlKey || event.isComposing || event.metaKey || event.shiftKey) {
                        return;
                    }

                    if (event.key == 'Escape') {
                        event.preventDefault();
                        this.search_input.focus();
                        this.closeDropdown();
                    }

                    if (event.key == 'ArrowDown') {
                        event.preventDefault();
                        document.activeElement.closest('li').nextElementSibling?.querySelector('a')?.focus();
                    }
                    if (event.key == 'ArrowUp') {
                        event.preventDefault();
                        document.activeElement.closest('li').previousElementSibling?.querySelector('a')?.focus();
                    }
                }

                connectedCallback() {
                    this.search_input = document.querySelector(this.attributes.for.value);
                    if (!this.search_input) {
                        throw new Error('Cannot find input element ' + this.attributes.for.value
                            + ', please set a valid CSS selector in the for attribute (e.g. '
                            + '<drop-old for="#input-id-goes-here">)');
                    }

                    this.unsubscribes = [
                        listen(this.search_input, 'keydown', this.inputKeyDown.bind(this)),
                        listen(this, 'keydown', this.dropdownKeyDown.bind(this)),
                    ];
                }

                disconnectedCallback() {
                    this.unsubscribes.map((unsub) => unsub());
                }
            }

            customElements.define('drop-old', DropOld);
        </script>

        <style>
            section {
                padding: 2em;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            .dropdown-group {
                position: relative;
            }

            drop-old {
                position: absolute;
                top: 25px;
                left: 0px;
                display: block;
                max-width: 30em;
                border: 1px solid #888;
                box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.5);
            }

            li {
                padding: 0.5em;
                display: flex;
                width: 100%;
                justify-content: space-between;
                flex-flow: row wrap;
            }

            ul { list-style-type: none; }
            li:nth-of-type(2n+1) { background: #eee; }
            li:nth-of-type(2n+2) { background: #ddd; }
            li span.result { flex: 1 0 auto; }
            li span { padding: 0 1em; display: block; }

            li:focus-within { background: #bbb; }
        </style>
    </head>
    <body>
        <section>
        <h1>search dropdown</h1>

        <hr />

        <br />

        <div class="dropdown-group">
            <input id='search-input' type="text" name="q" placeholder="Search..."
                hx-get="query.php"
                hx-trigger="keyup changed delay:500ms"
                hx-target="#search-results"
                hx-swap="outerHTML"
                hx-indicator="#loading" />

            <div id="loading" class="loading-wrapper htmx-indicator" style="display: inline-block; vertical-align: bottom;">
                <svg width="22px" height="22px" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                    <circle cx="50" cy="50" fill="none" stroke="#888888" stroke-width="8" r="32" stroke-dasharray="150 50">
                        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1">
                        </animateTransform>
                    </circle>
                </svg>
            </div>

            <drop-old for='#search-input' id="search-results" style="display: none">
            </drop-old>
        </div>
        </section>
    </body>
</html>
