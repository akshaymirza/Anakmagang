<?php
/**
 * partials/footer.php
 * Reusable footer component for Kedayweb Platform
 * Berdasarkan desain footer verification.php
 */
$footer_root = file_exists('verification.php') ? '' : '../';
?>
<!-- Footer -->
<footer class="bg-surface-container-high border-t border-outline-variant w-full py-xl mt-auto shrink-0 z-10">
    <div class="flex flex-col md:flex-row justify-between items-center px-gutter w-full max-w-container-max mx-auto gap-md">
        <div class="font-label-md font-black text-on-surface flex items-center gap-2">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;">school</span>
            Kedayweb Platform
        </div>
        <nav class="flex flex-wrap justify-center gap-md font-body-sm text-body-sm">
            <a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Kebijakan Privasi</a>
            <a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Syarat & Ketentuan</a>
            <a class="text-primary font-semibold hover:underline transition-all" href="<?php echo $footer_root; ?>verification.php">Verifikasi Sertifikat</a>
        </nav>
        <div class="font-body-sm text-on-surface-variant">© <?php echo date('Y'); ?> Kedayweb Platform. Hak cipta dilindungi.</div>
    </div>
</footer>
