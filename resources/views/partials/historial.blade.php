<script>
    (function () {
        if (!window.history || typeof history.replaceState !== 'function') {
            return;
        }

        history.replaceState({ esfap: Date.now() }, '', location.href);

        function esInterna(url) {
            return url.origin === location.origin
                && url.protocol !== 'file:'
                && url.href !== location.href;
        }

        function navegar(url) {
            if (!window.history.replaceState) {
                location.assign(url.href);
                return;
            }
            location.replace(url.href);
        }

        document.addEventListener('click', function (e) {
            var enlace = e.target && e.target.closest ? e.target.closest('a[href]') : null;
            if (!enlace || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                return;
            }
            if (enlace.target === '_blank' || enlace.hasAttribute('download')) {
                return;
            }

            var href = enlace.getAttribute('href') || '';
            if (!href || href.charAt(0) === '#' || href.slice(0, 11).toLowerCase() === 'javascript:') {
                return;
            }

            var url;
            try {
                url = new URL(href, location.href);
            } catch (err) {
                return;
            }

            if (!esInterna(url)) {
                return;
            }

            e.preventDefault();
            navegar(url);
        }, true);

        function bloquear() {
            history.go(1);
        }

        function navigatedPorAtras() {
            if (!window.performance || typeof performance.getEntriesByType !== 'function') {
                return false;
            }
            var entradas = performance.getEntriesByType('navigation');
            return !!entradas.length && entradas[0].type === 'back_forward';
        }

        window.addEventListener('popstate', bloquear);
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                bloquear();
            }
        });

        if (navigatedPorAtras()) {
            document.documentElement.style.visibility = 'hidden';
            setTimeout(function () {
                document.documentElement.style.visibility = '';
            }, 1500);
            bloquear();
        }
    })();
</script>
