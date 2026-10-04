<?php

return [
    /*
     * When disabled, links and images that point at this app come out as
     * relative paths (/docs/api) instead of full URLs
     * (https://example.com/docs/api). A URL on another host, such as an
     * asset served from a CDN through ASSET_URL, always stays absolute.
     */
    'absolute' => (bool) env('COMMONMARK_ROUTES_ABSOLUTE', true),
];
