            <!-- Page Footer -->
            <footer style="margin-top: 60px; padding-top: 20px; border-top: 1px solid var(--border-color); text-align: center; font-size: 13px; color: var(--text-muted);">
                <p>&copy; <?= date('Y') ?> Natal Solidário. Todos os direitos reservados. Feito com amor para a comunidade.</p>
            </footer>
        </main>
    </div>

    <!-- Core Scripts -->
    <script src="assets/js/main.js"></script>
    
    <!-- Dynamic SVG charts logic loaded only on dashboard page -->
    <?php if (($_GET['route'] ?? 'dashboard') === 'dashboard'): ?>
        <script src="assets/js/charts.js"></script>
    <?php endif; ?>
</body>
</html>
