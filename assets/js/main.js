/**
 * Natal Solidário - Core Javascript interactions
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Dynamic fields toggle on Product creation/editing
    const typeSelect = document.getElementById('tipo_produto_select');
    if (typeSelect) {
        function toggleProductFields() {
            const selectedType = typeSelect.value;
            
            // Hide all dynamic parts
            document.querySelectorAll('.dynamic-section').forEach(el => {
                el.style.display = 'none';
            });
            
            // Disable inputs inside hidden sections to prevent them from sending values
            document.querySelectorAll('.dynamic-section input, .dynamic-section select').forEach(el => {
                el.disabled = true;
            });
            
            // Show selected type section
            if (selectedType) {
                const targetSection = document.getElementById('fields_' + selectedType);
                if (targetSection) {
                    targetSection.style.display = 'block';
                    
                    // Enable inputs inside active section
                    targetSection.querySelectorAll('input, select').forEach(el => {
                        el.disabled = false;
                    });
                }
            }
        }
        
        typeSelect.addEventListener('change', toggleProductFields);
        toggleProductFields(); // Initialize on load
    }

    // 2. Alert message auto-fade out
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // 3. Deletion confirmation dialogs
    const confirmDeleteLinks = document.querySelectorAll('.confirm-delete');
    confirmDeleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('data-message') || 'Deseja realmente excluir este registro permanentemente?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 4. Responsive mobile sidebar hamburger toggle (if present)
    const toggleSidebarBtn = document.getElementById('toggle_sidebar_btn');
    const sidebar = document.querySelector('.sidebar');
    if (toggleSidebarBtn && sidebar) {
        toggleSidebarBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
});
