search-input
============

This web component provides a basic
[incremental search](https://en.wikipedia.org/wiki/Incremental_search) input.

Usage
-----

Example:

```html
<search-input action="query.php?q=" placeholder="Search...">
    <search-loading>
        <img src="loading.gif" />
    </search-loading>
</search-input>
```

The search-input web component will query the server by using the URL specified in the
action paramater.  The final URL is constructed by simply appending the query string
to the URL specified in action.

The search-loading web component if present as a child of the search-input will be set
to opacity: 0, and briefly switched back to opacity: 1 before a request is started and
while a request is in flight.

The server should respond with the contents of the search dropdown, formatted as an
unordered list with list items, for example:

```html
<ul>
    <li>
        <span class="badge">#ff8c00</span>
        <span><a href="https://example.com">darkorange</a></span>
    </li>
</ul>
```

Styling
-------

This web component doesn't use a Shadow DOM, so should be easy to style.  You can omit the
included search-input.css to completely override the styling to your needs.

The structure of the component after loading and with some results should look like this:

```html
<search-input action="" placeholder="">
    <input type="text" placeholder="" value="" />
    <search-loading> ... </search-loading>
    <search-results>
        <ul>
            <li>Result #1</li>
            <li>Result #2</li>
            ...
        </ul>
    </search-results>
</search-input>
```

Open-source, not open-contribution
==================================

This project is open source but closed to code contributions.

Feel to talk to me on IRC or open a GitHub issue, but PRs are not likely to be merged.

License
-------

Copyright 2021 Kuno Woudt <kuno@frob.nl>

This program is free software: you can redistribute it and/or modify
it under the terms of copyleft-next 0.3.1. See [LICENSE.md].

SPDX-License-Identifier: copyleft-next-0.3.1
