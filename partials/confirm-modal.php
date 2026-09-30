<!-- partials/confirm-modal.php: Reusable Custom Confirmation Modal -->
<div id="custom-confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-200">
    <div class="bg-white rounded-2xl border border-outline-variant w-full max-w-sm p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-200" onclick="event.stopPropagation()">
        <!-- Icon Container -->
        <div id="confirm-modal-icon-wrap" class="w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mb-4">
            <span id="confirm-modal-icon" class="material-symbols-outlined text-[28px]">warning</span>
        </div>

        <!-- Title & Message -->
        <h3 id="confirm-modal-title" class="font-headline-md text-headline-md text-slate-900 font-bold mb-2">Konfirmasi Tindakan</h3>
        <p id="confirm-modal-message" class="font-body-sm text-body-sm text-slate-600 mb-6 leading-relaxed">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <button type="button" id="confirm-modal-cancel-btn" class="px-4 py-2 rounded-xl border border-outline-variant text-slate-700 font-semibold text-xs hover:bg-slate-100 transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" id="confirm-modal-submit-btn" class="px-4 py-2 rounded-xl bg-red-600 text-white font-semibold text-xs hover:bg-red-700 transition-all shadow-xs cursor-pointer">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
window.showConfirmModal = function (options = {}) {
    return new Promise((resolve) => {
        const modal = document.getElementById('custom-confirm-modal');
        const titleEl = document.getElementById('confirm-modal-title');
        const msgEl = document.getElementById('confirm-modal-message');
        const iconWrap = document.getElementById('confirm-modal-icon-wrap');
        const iconEl = document.getElementById('confirm-modal-icon');
        const confirmBtn = document.getElementById('confirm-modal-submit-btn');
        const cancelBtn = document.getElementById('confirm-modal-cancel-btn');

        if (!modal) {
            // Fallback if modal container is missing
            resolve(window.confirm(options.message || 'Konfirmasi tindakan?'));
            return;
        }

        const title = options.title || 'Konfirmasi Tindakan';
        const message = options.message || 'Apakah Anda yakin ingin melanjutkan?';
        const confirmText = options.confirmText || 'Ya, Lanjutkan';
        const cancelText = options.cancelText || 'Batal';
        const type = options.type || 'danger'; // 'danger' | 'warning' | 'info'

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;
        if (confirmBtn) confirmBtn.textContent = confirmText;
        if (cancelBtn) cancelBtn.textContent = cancelText;

        // Styling based on type
        if (type === 'danger') {
            if (iconWrap) iconWrap.className = 'w-12 h-12 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mb-4';
            if (iconEl) iconEl.textContent = 'warning';
            if (confirmBtn) confirmBtn.className = 'px-4 py-2 rounded-xl bg-red-600 text-white font-semibold text-xs hover:bg-red-700 transition-all shadow-xs cursor-pointer';
        } else if (type === 'warning') {
            if (iconWrap) iconWrap.className = 'w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4';
            if (iconEl) iconEl.textContent = 'help';
            if (confirmBtn) confirmBtn.className = 'px-4 py-2 rounded-xl bg-amber-600 text-white font-semibold text-xs hover:bg-amber-700 transition-all shadow-xs cursor-pointer';
        } else {
            if (iconWrap) iconWrap.className = 'w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4';
            if (iconEl) iconEl.textContent = 'info';
            if (confirmBtn) confirmBtn.className = 'px-4 py-2 rounded-xl bg-primary text-white font-semibold text-xs hover:bg-primary-container transition-all shadow-xs cursor-pointer';
        }

        const cleanup = (result) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            confirmBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
            modal.removeEventListener('click', onBackdrop);
            resolve(result);
        };

        const onConfirm = () => cleanup(true);
        const onCancel = () => cleanup(false);
        const onBackdrop = (e) => { if (e.target === modal) cleanup(false); };

        confirmBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);
        modal.addEventListener('click', onBackdrop);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    });
};
</script>
