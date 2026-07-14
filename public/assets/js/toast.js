/**
 * Lightweight toast notifications (top-right, stackable, auto-dismiss).
 * No dependencies. Include once via <script src=".../public/assets/js/toast.js"></script>.
 *
 * Usage:
 *   Toast.success("Student successfully marked as Graduated.");
 *   Toast.error("Something went wrong.");
 *   Toast.warning("Please check the form.");
 *   Toast.info("Processing your request...");
 *   Toast.success("Saved.", { duration: 6000 }); // custom duration in ms, 0 = no auto-dismiss
 */
(function (global) {
    'use strict';

    var DEFAULT_DURATION = 4000;
    var STYLE_ID = 'lt-toast-styles';
    var container = null;

    var ICONS = {
        success: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
        error: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        warning: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L22 20H2L12 3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="16.5" x2="12" y2="16.51"></line></svg>',
        info: '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="11" x2="12" y2="16"></line><line x1="12" y1="7.5" x2="12" y2="7.51"></line></svg>'
    };

    var CSS = [
        '.lt-toast-container{position:fixed;top:1rem;right:1rem;z-index:99999;display:flex;flex-direction:column;gap:.6rem;max-width:360px;width:calc(100% - 2rem)}',
        '.lt-toast{position:relative;overflow:hidden;pointer-events:auto;display:flex;align-items:flex-start;gap:.6rem;background:#fff;color:#2f2f33;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,.15);padding:.75rem .9rem 1rem;border-left:4px solid transparent;opacity:0;transform:translateX(120%);transition:transform .3s ease,opacity .3s ease}',
        '.lt-toast.lt-toast-show{opacity:1;transform:translateX(0)}',
        '.lt-toast.lt-toast-hide{opacity:0;transform:translateX(120%)}',
        '.lt-toast--success{border-left-color:#28a745}',
        '.lt-toast--error{border-left-color:#dc3545}',
        '.lt-toast--warning{border-left-color:#ffc107}',
        '.lt-toast--info{border-left-color:#0d6efd}',
        '.lt-toast__icon{flex:0 0 auto;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;margin-top:1px}',
        '.lt-toast--success .lt-toast__icon{background:#28a745}',
        '.lt-toast--error .lt-toast__icon{background:#dc3545}',
        '.lt-toast--warning .lt-toast__icon{background:#ffc107}',
        '.lt-toast--info .lt-toast__icon{background:#0d6efd}',
        '.lt-toast__body{flex:1 1 auto;font-size:.875rem;line-height:1.4;padding-top:1px;word-break:break-word}',
        '.lt-toast__close{flex:0 0 auto;background:none;border:none;cursor:pointer;font-size:1.1rem;line-height:1;color:#8a8a8f;padding:0 0 0 4px}',
        '.lt-toast__close:hover{color:#2f2f33}',
        '.lt-toast__progress{position:absolute;left:0;right:0;bottom:0;height:3px;overflow:hidden}',
        '.lt-toast__progress-bar{display:block;height:100%;width:100%;transform:scaleX(1);transform-origin:left}',
        '.lt-toast__progress-bar--run{animation:lt-toast-shrink linear forwards}',
        '.lt-toast--success .lt-toast__progress-bar{background:#28a745}',
        '.lt-toast--error .lt-toast__progress-bar{background:#dc3545}',
        '.lt-toast--warning .lt-toast__progress-bar{background:#ffc107}',
        '.lt-toast--info .lt-toast__progress-bar{background:#0d6efd}',
        '@keyframes lt-toast-shrink{from{transform:scaleX(1)}to{transform:scaleX(0)}}',
        '@media (prefers-color-scheme:dark){.lt-toast{background:#2b2b30;color:#e8e8ea;box-shadow:0 4px 14px rgba(0,0,0,.4)}.lt-toast__close{color:#a9a9b0}.lt-toast__close:hover{color:#fff}}',
        '@media (prefers-reduced-motion:reduce){.lt-toast{transition:none}.lt-toast__progress-bar--run{animation:none}}'
    ].join('');

    function ensureStyles() {
        if (document.getElementById(STYLE_ID)) return;
        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = CSS;
        document.head.appendChild(style);
    }

    function ensureContainer() {
        if (container && document.body.contains(container)) return container;
        container = document.createElement('div');
        container.className = 'lt-toast-container';
        container.setAttribute('aria-live', 'polite');
        container.setAttribute('aria-atomic', 'false');
        document.body.appendChild(container);
        return container;
    }

    function dismiss(toastEl) {
        if (!toastEl || !toastEl.parentNode || toastEl.dataset.dismissing) return;
        toastEl.dataset.dismissing = 'true';
        toastEl.classList.remove('lt-toast-show');
        toastEl.classList.add('lt-toast-hide');
        var removed = false;
        function remove() {
            if (removed) return;
            removed = true;
            if (toastEl.parentNode) toastEl.parentNode.removeChild(toastEl);
        }
        toastEl.addEventListener('transitionend', remove, { once: true });
        setTimeout(remove, 400); // fallback in case transitionend never fires
    }

    function show(type, message, options) {
        options = options || {};
        var duration = options.duration !== undefined ? options.duration : DEFAULT_DURATION;

        ensureStyles();
        var host = ensureContainer();

        var toastEl = document.createElement('div');
        toastEl.className = 'lt-toast lt-toast--' + type;
        toastEl.setAttribute('role', type === 'error' || type === 'warning' ? 'alert' : 'status');

        var icon = document.createElement('span');
        icon.className = 'lt-toast__icon';
        icon.innerHTML = ICONS[type] || ICONS.info;

        var body = document.createElement('span');
        body.className = 'lt-toast__body';
        body.textContent = message; // textContent only: never render messages as HTML

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'lt-toast__close';
        closeBtn.setAttribute('aria-label', 'Close');
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', function () { dismiss(toastEl); });

        toastEl.appendChild(icon);
        toastEl.appendChild(body);
        toastEl.appendChild(closeBtn);

        var bar = null;
        if (duration > 0) {
            var progress = document.createElement('span');
            progress.className = 'lt-toast__progress';
            bar = document.createElement('span');
            bar.className = 'lt-toast__progress-bar lt-toast__progress-bar--run';
            bar.style.animationDuration = duration + 'ms';
            progress.appendChild(bar);
            toastEl.appendChild(progress);
        }

        host.insertBefore(toastEl, host.firstChild);

        // Force a reflow before adding the "show" class so the slide-in transition runs.
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { toastEl.classList.add('lt-toast-show'); });
        });

        var timer = null;
        var remaining = duration;
        var startedAt = 0;

        function startTimer() {
            if (duration <= 0) return;
            startedAt = Date.now();
            timer = setTimeout(function () { dismiss(toastEl); }, remaining);
        }
        function pauseTimer() {
            if (!timer) return;
            clearTimeout(timer);
            timer = null;
            remaining -= Date.now() - startedAt;
            if (bar) bar.style.animationPlayState = 'paused';
        }
        function resumeTimer() {
            if (bar) bar.style.animationPlayState = 'running';
            startTimer();
        }

        if (duration > 0) {
            startTimer();
            toastEl.addEventListener('mouseenter', pauseTimer);
            toastEl.addEventListener('mouseleave', resumeTimer);
        }

        return toastEl;
    }

    var Toast = {
        success: function (message, options) { return show('success', message, options); },
        error: function (message, options) { return show('error', message, options); },
        warning: function (message, options) { return show('warning', message, options); },
        info: function (message, options) { return show('info', message, options); },
        show: show,
        dismissAll: function () {
            if (!container) return;
            Array.prototype.slice.call(container.children).forEach(dismiss);
        }
    };

    global.Toast = Toast;
    global.jsToast = Toast; // alias, for either naming convention
})(window);
