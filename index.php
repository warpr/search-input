<?php

require_once __DIR__ . '/colors.php';

?>
<html>
    <head>
        <script src="https://unpkg.com/htmx.org@1.3.3"></script>

        <script>
            function debounce(callback, wait) {
                let timeout_id = null;
                return (...args) => {
                    clearTimeout(timeout_id);
                    timeout_id = setTimeout(() => callback.apply(null, args), wait);
                };
            }

            function listen(element, event, callback) {
                element.addEventListener(event, callback);
                return () => element.removeEventListener(event, callback);
            }

            function apply_css(element, styles) {
                for (const [key, val] of Object.entries(styles)) {
                    element.style[key] = val;
                }
            }

            function request(url, options, callback) {
                return fetch(url, options).then(response => {
                    if (!response.ok) {
                        throw new Error('Unexpected HTTP status', response.status);
                    }

                    return response.text();
                }).then(body => {
                    return body;
                }).catch(err => {
                    if (err.name === 'AbortError') {
                        // aborts are expected
                    } else {
                        console.log('[ERROR]', err);
                    }

                    return null;
                });
            }

            class SearchLoading extends HTMLElement {
                connectedCallback() {
                    const parent = this.closest('search-input');
                    this.style.opacity = 0;
                    if (parent) {
                        parent.loading_indicator = this;
                    }
                }
            }

            customElements.define('search-loading', SearchLoading);

            class SearchInput extends HTMLElement {
                unsubscribes;
                abort_query;
                loading_indicator;
                search_input;
                search_results;
                request_in_flight;
                current_query;

                close_dropdown() {
                    if (this.search_results) {
                        this.search_results.style.display = 'none';
                    }
                }

                open_dropdown() {
                    if (this.search_results.style.display !== 'block') {
                        this.search_results.style.display = 'block';
                    }
                }

                dropdown_results(body) {
                    if (!this.search_results) {
                        this.search_results = document.createElement('search-results');
                        apply_css(this.search_results, {
                            display: 'none',
                            position: 'absolute',
                            top: '25px',
                            left: '0px',
                            width: '30em',
                            border: '1px solid #888',
                            boxShadow: '5px 5px 10px rgba(0, 0, 0, 0.5)',
                            zIndex: '200',
                        });

                        this.search_results.style.display = 'none';
                        this.append(this.search_results);
                        this.unsubscribes.push(
                            listen(this.search_results, 'keydown', this.dropdown_keydown.bind(this))
                        );
                    }

                    this.search_results.innerHTML = body;
                    this.open_dropdown();
                }

                async query() {
                    const query = this.search_input.value;

                    if (query === this.current_query) {
                        console.log('[INFO] already requesting/requested', query);
                        return;
                    }

                    console.log('[INFO] request', query);
                    this.current_query = query;
                    this.request_in_flight = query;

                    this.abort_query?.();

                    if (query === '') {
                        this.current_query = null;
                        this.request_in_flight = null;
                        this.close_dropdown();
                        return;
                    }

                    const controller = new AbortController();
                    this.abort_query = () => controller.abort();

                    this.close_dropdown();

                    if (this.loading_indicator) {
                        this.loading_indicator.style.opacity = 1;
                    }

                    const url = 'query.php?q=' + query;
                    const body = await request(url, { signal: controller.signal });

                    if (this.request_in_flight !== query) {
                        console.log('[INFO] request aborted', query);

                        // some other query has been issued after this one, so this
                        // response is now out-of-date.
                        return;
                    }

                    console.log('[INFO] request concluded', query);

                    if (body === null || body === '') {
                        this.close_dropdown();
                    } else {
                        this.dropdown_results(body);
                    }

                    if (this.loading_indicator) {
                        this.loading_indicator.style.opacity = 0;
                    }

                    this.request_in_flight = null;
                }

                input_keydown() {
                    if (event.ctrlKey || event.isComposing || event.metaKey || event.shiftKey) {
                        return;
                    }

                    if (event.key == 'ArrowDown' || event.key == 'Enter') {
                        event.preventDefault();
                        if (!this.request_in_flight && this.current_query) {
                            // we're not currently requesting search results, but we have
                            // results for a previous query... so show those.
                            this.open_dropdown();
                        }

                        this.search_results.querySelector('li a')?.focus();
                    }

                    if (event.key == 'Escape') {
                        this.close_dropdown();
                    }
                }

                dropdown_keydown() {
                    if (event.ctrlKey || event.isComposing || event.metaKey || event.shiftKey) {
                        return;
                    }

                    if (event.key == 'Escape') {
                        event.preventDefault();
                        this.search_input.focus();
                        this.close_dropdown();
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
                    this.search_input = document.createElement('input');
                    this.search_input.setAttribute('placeholder', this.getAttribute('placeholder'));
                    this.prepend(this.search_input);

                    this.style.position = 'relative';

                    this.unsubscribes = [
                        listen(this.search_input, 'keyup', debounce(this.query.bind(this), 500)),
                        listen(this.search_input, 'keydown', this.input_keydown.bind(this)),
                    ];
                }

                disconnectedCallback() {
                    this.unsubscribes.map((unsub) => unsub());
                }
            }

            customElements.define('search-input', SearchInput);

            class DropDown extends HTMLElement {
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

            drop-down {
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

        <br />

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

        <br /><br /><hr /><br />

        <div class="dropdown-group">
            <input id='search-input' type="text" name="q" placeholder="Search..."
                hx-get="drop-down.php"
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

            <drop-down for='#search-input' id="search-results" style="display: none">
            </drop-down>
        </div>
        </section>
    </body>
</html>
