/**
 *   This file is part of search-input, an incremental search web component.
 *   Copyright (C) 2021  Kuno Woudt <kuno@frob.nl>
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of copyleft-next 0.3.1.  See copyleft-next-0.3.1.txt.
 *
 *   SPDX-License-Identifier: copyleft-next-0.3.1
 */

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
                zIndex: '200',
            });

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
            if (this.loading_indicator) {
                this.loading_indicator.style.opacity = 0;
            }

            return;
        }

        this.current_query = query;
        this.request_in_flight = query;

        this.abort_query?.();

        if (query === '') {
            this.current_query = null;
            this.request_in_flight = null;
            this.close_dropdown();

            if (this.loading_indicator) {
                this.loading_indicator.style.opacity = 0;
            }

            return;
        }

        const controller = new AbortController();
        this.abort_query = () => controller.abort();

        this.close_dropdown();

        const url = this.getAttribute('action') + query;
        const body = await request(url, { signal: controller.signal });

        if (this.request_in_flight !== query) {
            // some other query has been issued after this one, so this
            // response is now out-of-date.
            return;
        }

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

    input_keyup() {
        if (this.loading_indicator) {
            this.loading_indicator.style.opacity = 1;
        }

        this.debounced_query();
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
        this.search_input.setAttribute('type', 'text');
        this.search_input.setAttribute('placeholder', this.getAttribute('placeholder'));
        this.prepend(this.search_input);

        this.style.position = 'relative';
        this.style.boxSizing = 'border-box';
        this.debounced_query = debounce(this.query.bind(this), 500);

        this.unsubscribes = [
            listen(this.search_input, 'keyup', this.input_keyup.bind(this)),
            listen(this.search_input, 'keydown', this.input_keydown.bind(this)),
        ];
    }

    disconnectedCallback() {
        this.unsubscribes.map((unsub) => unsub());
    }
}

customElements.define('search-input', SearchInput);
