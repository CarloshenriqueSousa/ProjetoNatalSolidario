// Controle dinâmico de campos do formulário de Estoque/Produtos
document.addEventListener('DOMContentLoaded', () => {
    const selectCategoria = document.getElementById('categoria');
    if (!selectCategoria) return;

    const groupRoupa = document.getElementById('group_roupa');
    const groupBrinquedo = document.getElementById('group_brinquedo');
    const groupAlimento = document.getElementById('group_alimento');
    const groupHigiene = document.getElementById('group_higiene');

    const toggleGroups = () => {
        const cat = selectCategoria.value;
        if (groupRoupa) groupRoupa.style.display = (cat === 'roupa') ? 'block' : 'none';
        if (groupBrinquedo) groupBrinquedo.style.display = (cat === 'brinquedo') ? 'block' : 'none';
        if (groupAlimento) groupAlimento.style.display = (cat === 'alimento') ? 'block' : 'none';
        if (groupHigiene) groupHigiene.style.display = (cat === 'higiene') ? 'block' : 'none';
    };

    selectCategoria.addEventListener('change', toggleGroups);
    toggleGroups();
});
