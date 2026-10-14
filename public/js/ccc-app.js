// Shared behaviour for every signed-in page of the inventory system.
(function () {
    'use strict';

    // Mobile navigation drawer
    function initNavigation() {
        var body = document.body;
        var toggle = document.querySelector('[data-nav-toggle]');
        var sidebar = document.getElementById('ccc-sidebar');
        var backdrop = document.querySelector('[data-nav-backdrop]');

        if (!toggle || !sidebar) {
            return;
        }

        function setOpen(open) {
            body.classList.toggle('ccc-nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

            if (backdrop) {
                backdrop.hidden = !open;
            }

            if (open) {
                var firstLink = sidebar.querySelector('a, button');
                if (firstLink) {
                    firstLink.focus();
                }
            }
        }

        toggle.addEventListener('click', function () {
            setOpen(!body.classList.contains('ccc-nav-open'));
        });

        document.querySelectorAll('[data-nav-close]').forEach(function (element) {
            element.addEventListener('click', function () {
                setOpen(false);
                toggle.focus();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && body.classList.contains('ccc-nav-open')) {
                setOpen(false);
                toggle.focus();
            }
        });

        window.matchMedia('(min-width: 1025px)').addEventListener('change', function (event) {
            if (event.matches) {
                setOpen(false);
            }
        });
    }

    // Ask for confirmation before deleting or receiving stock
    function initConfirmations() {
        document.addEventListener('submit', function (event) {
            var message = event.target.getAttribute('data-confirm');

            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    }

    // Close buttons on flash messages
    function initAlerts() {
        document.querySelectorAll('[data-dismiss-alert]').forEach(function (button) {
            button.addEventListener('click', function () {
                var alert = button.closest('.ccc-alert');
                if (alert) {
                    alert.remove();
                }
            });
        });
    }

    // Chart tooltips on hover and keyboard focus.
    // Every value is also listed in each chart's "View data table".
    function initChartTooltips() {
        var targets = document.querySelectorAll('[data-tip-value]');

        if (!targets.length) {
            return;
        }

        var tooltip = document.createElement('div');
        var valueText = document.createElement('strong');
        var labelText = document.createElement('span');

        tooltip.className = 'ccc-chart-tooltip';
        tooltip.hidden = true;
        tooltip.appendChild(valueText);
        tooltip.appendChild(labelText);
        document.body.appendChild(tooltip);

        function show(target) {
            var anchor = target.querySelector('[data-tip-anchor]') || target;
            var rect = anchor.getBoundingClientRect();

            valueText.textContent = target.getAttribute('data-tip-value');
            labelText.textContent = target.getAttribute('data-tip-label');
            tooltip.hidden = false;
            tooltip.style.left = (rect.left + rect.width / 2 + window.scrollX) + 'px';
            tooltip.style.top = (rect.top + window.scrollY) + 'px';
        }

        function hide() {
            tooltip.hidden = true;
        }

        targets.forEach(function (target) {
            target.addEventListener('pointerenter', function () { show(target); });
            target.addEventListener('pointerleave', hide);
            target.addEventListener('focus', function () { show(target); });
            target.addEventListener('blur', hide);
        });

        window.addEventListener('scroll', hide, { passive: true });
    }

    // Notification bell dropdown in the top bar
    function initBell() {
        var bell = document.querySelector('[data-bell]');

        if (!bell) {
            return;
        }

        var toggle = bell.querySelector('[data-bell-toggle]');
        var panel = document.getElementById(toggle.getAttribute('aria-controls'));

        function setOpen(open) {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        toggle.addEventListener('click', function () {
            setOpen(panel.hidden);
        });

        document.addEventListener('click', function (event) {
            if (!panel.hidden && !bell.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !panel.hidden) {
                setOpen(false);
                toggle.focus();
            }
        });
    }

    // Print buttons on report pages
    function initPrint() {
        document.querySelectorAll('[data-print]').forEach(function (button) {
            button.addEventListener('click', function () {
                window.print();
            });
        });
    }

    // Export links show "Preparing…" while the file downloads,
    // and ignore repeated clicks so the file is not downloaded twice
    function initLoadingLinks() {
        document.querySelectorAll('[data-loading-text]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                if (link.classList.contains('is-loading')) {
                    event.preventDefault();
                    return;
                }

                var label = link.querySelector('[data-label]') || link;
                var original = label.textContent;

                link.classList.add('is-loading');
                link.setAttribute('aria-busy', 'true');
                label.textContent = link.getAttribute('data-loading-text');

                window.setTimeout(function () {
                    link.classList.remove('is-loading');
                    link.removeAttribute('aria-busy');
                    label.textContent = original;
                }, 3000);
            });
        });
    }

    initNavigation();
    initConfirmations();
    initAlerts();
    initChartTooltips();
    initBell();
    initPrint();
    initLoadingLinks();
})();
