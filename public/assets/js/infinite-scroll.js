/**
 * Appends the next page of table rows when the sentinel scrolls into view.
 * Markup: [data-infinite-root] > [data-infinite-scroll] > table + [data-infinite-sentinel]
 */
(function () {
    'use strict';

    function initRoot(root) {
        var body = root.querySelector('[data-infinite-body]');
        var sentinel = root.querySelector('[data-infinite-sentinel]');
        if (!body || !sentinel) {
            return;
        }

        var scrollRoot = root.querySelector('[data-infinite-scroll]');
        var status = root.querySelector('[data-infinite-status]');
        var loader = root.querySelector('[data-infinite-loader]');
        var end = root.querySelector('[data-infinite-end]');
        var nextUrl = sentinel.getAttribute('data-next-url') || '';
        var loading = false;

        function setCounts(shown, total) {
            if (!status) {
                return;
            }
            var shownEl = status.querySelector('[data-infinite-shown]');
            var totalEl = status.querySelector('[data-infinite-total]');
            if (shownEl) {
                shownEl.textContent = String(shown);
            }
            if (totalEl) {
                totalEl.textContent = String(total);
            }
        }

        function finish() {
            nextUrl = '';
            sentinel.setAttribute('data-next-url', '');
            sentinel.hidden = true;
            if (loader) {
                loader.hidden = true;
            }
            if (end && (status && Number(status.getAttribute('data-total') || 0) > 0)) {
                end.hidden = false;
            }
        }

        function loadMore() {
            if (loading || !nextUrl) {
                return;
            }

            loading = true;
            if (loader) {
                loader.hidden = false;
            }

            var url = new URL(nextUrl, window.location.origin);
            url.searchParams.set('infinite', '1');

            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Could not load more rows');
                    }
                    return response.json();
                })
                .then(function (data) {
                    var empty = body.querySelector('[data-infinite-empty]');
                    if (empty) {
                        empty.remove();
                    }

                    if (data.html) {
                        body.insertAdjacentHTML('beforeend', data.html);
                    }

                    if (status) {
                        status.setAttribute('data-shown', String(data.shown || 0));
                        status.setAttribute('data-total', String(data.total || 0));
                    }
                    setCounts(data.shown || 0, data.total || 0);

                    nextUrl = data.next_url || '';
                    sentinel.setAttribute('data-next-url', nextUrl);

                    if (!data.has_more || !nextUrl) {
                        finish();
                    }
                })
                .catch(function () {
                    if (typeof window.showToast === 'function') {
                        window.showToast('Could not load more rows.', true);
                    }
                })
                .finally(function () {
                    loading = false;
                    if (loader && nextUrl) {
                        loader.hidden = true;
                    }
                });
        }

        if (!nextUrl) {
            finish();
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    loadMore();
                }
            });
        }, {
            root: scrollRoot || null,
            rootMargin: '200px 0px',
            threshold: 0,
        });

        observer.observe(sentinel);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-infinite-root]').forEach(initRoot);
    });
})();
