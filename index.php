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
            class DropDown extends HTMLElement {
                unsubscribes;
                search_input;

                inputKeyDown(event) {
                    if (event.ctrlKey || event.isComposing || event.metaKey || event.shiftKey) {
                        return;
                    }

                    if (event.key == 'ArrowDown' || event.key == 'Enter') {
                        event.preventDefault();
                        this.querySelector('li a')?.focus();
                    }

                    if (event.key == 'Escape') {
                        console.log('close drop-down');
                    }
                }

                dropdownKeyDown(event) {
                    if (event.ctrlKey || event.isComposing || event.metaKey || event.shiftKey) {
                        return;
                    }

                    if (event.key == 'Escape') {
                        event.preventDefault();
                        this.search_input.focus();
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
                            + '<drop-down for="#input-id-goes-here">)');
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

            customElements.define('drop-down', DropDown);
        </script>

        <style>
            body {
                background: #000;
                color: #aaa;
                font-family: Helvetica, Arial, sans-serif;
            }

            a:visited { color: #aaa; text-decoration: none; }
            a:link { color: #aaa; text-decoration: none; }
            a { color: #aaa; text-decoration: none; }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            section { padding: 2em; }

            .badge {
                padding: 0 1em;
                border-radius: 8px;
                height: 16px;
            }

            ul { list-style-type: none; }
            li:nth-of-type(2n+1) { background: #121212; }
            li:nth-of-type(2n+2) { background: #232323; }
            li span.result { flex: 1 0 auto; }
            li span { padding: 0 1em; display: block; }
            li {
                padding: 0.5em;
                display: flex;
                width: 100%;
                justify-content: space-between;
                flex-flow: row wrap;
            }

            li:focus-within {
                background: gray;
            }

            li:focus-within span.result a { color: white; }

            .loading-wrapper {
                display: inline-block;
                vertical-align: bottom;
            }

            drop-down {
                display: block;
                max-width: 30em;
                border: 1px solid #888;
            }

            /* loading indicator */
            #loading {
                width: 24px;
                height: 24px;
                display: flex;
                justify-content: center;
                align-items: flex-end;
                opacity: 0;
                transition: opacity 300ms ease-in;
            }
            #loading.htmx-request {
                opacity: 1;
            }

            #loading > div {
                width: 20%;
                height: 50%;
                border: solid 1px black;
                background-color: dodgerblue;
            }

            .pulse1 { animation: pulse 1s 0s ease infinite; }
            .pulse2 { animation: pulse 1s 0.15s ease infinite; }
            .pulse3 { animation: pulse 1s 0.30s ease infinite; }
            .pulse4 { animation: pulse 1s 0.45s ease infinite; }

            @keyframes pulse {
                0% { height: 50%; }
                5% { height: 80%; }
                50% { height: 45% }
                100% { height: 50%; }
            }
        </style>
    </head>
    <body>
        <section>
        <h1>search dropdown</h1>

        <br />

        <input id='search-input' type="text" name="q" placeholder="Search..."
            hx-get="query.php"
            hx-trigger="keyup changed delay:500ms"
            hx-target="#search-results"
            hx-indicator="#loading" />

        <div class="loading-wrapper">
            <div id="loading">
                <div class='pulse1'></div>
                <div class='pulse2'></div>
                <div class='pulse3'></div>
                <div class='pulse4'></div>
            </div>
        </div>

        <br />
        <br />

        <drop-down for='#search-input' id="search-results">
<ul>
<?php
foreach (get_colors('orange') as $key => $val) {
    render_color($key, $val);
}
?>
</ul>
        </drop-down>
        </section>
    </body>
</html>
