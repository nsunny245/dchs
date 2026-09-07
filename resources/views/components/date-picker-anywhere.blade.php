<script>
    (() => {
        if (window.__dgcDatePickerAnywhere) return;
        window.__dgcDatePickerAnywhere = true;

        document.addEventListener('click', (event) => {
            const input = event.target.closest('input[type="date"]');
            if (!input || input.disabled || input.readOnly || typeof input.showPicker !== 'function') return;

            try {
                input.showPicker();
            } catch (_) {
                // Browsers without programmatic picker support keep native behavior.
            }
        });
    })();
</script>
