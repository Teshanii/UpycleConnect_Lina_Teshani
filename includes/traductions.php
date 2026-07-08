
<select id="select-langue" class="form-select form-select-sm me-3" style="width:auto;" onchange="changerLangue()"></select>

<script>

var langueActive = localStorage.getItem('langue') || 'fr';


function chargerLangues() {
    fetch('/api/langues')
        .then(r => r.json())
        .then(data => {
            var sel = document.getElementById('select-langue');
            sel.innerHTML = '';
            (data || []).forEach(function(l) {
                var selected = l.code === langueActive ? ' selected' : '';
                sel.innerHTML += '<option value="' + l.code + '" data-id="' + l.id + '"' + selected + '>' + l.code.toUpperCase() + '</option>';
            });
            traduirePage();
        });
}

function traduirePage() {
    if (langueActive === 'fr') return; 

    var option = document.querySelector('#select-langue option:checked');
    if (!option) return;
    var idLangue = option.dataset.id;

    fetch('/api/traductions?id_langue=' + idLangue)
        .then(r => r.json())
        .then(traductions => {
            (traductions || []).forEach(function(t) {
                document.querySelectorAll('[data-trad="' + t.cle + '"]').forEach(function(el) {
                    el.innerText = t.texte;
                });
            });
        });
}


function changerLangue() {
    langueActive = document.getElementById('select-langue').value;
    localStorage.setItem('langue', langueActive);
    location.reload();
}

chargerLangues();
</script>