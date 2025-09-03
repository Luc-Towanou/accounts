@extends('dash.layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Résumé KPI cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6>Total Dépenses</h6>
                    <h4 id="totalDepenses">0 XOF</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6>Total Recettes</h6>
                    <h4 id="totalRecettes">0 XOF</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6>Solde</h6>
                    <h4 id="solde">0 XOF</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6>Nombre d’opérations</h6>
                    <h4 id="nbOperations">0</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Répartition par Nature</h6>
                    <canvas id="natureChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Montant par Mois</h6>
                    <canvas id="monthlyChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres dynamiques -->
    <div class="row mb-3">
        <div class="col-md-2">
            <input type="date" class="form-control" id="filterStartDate" placeholder="Date début">
        </div>
        <div class="col-md-2">
            <input type="date" class="form-control" id="filterEndDate" placeholder="Date fin">
        </div>
        <div class="col-md-2">
            <select id="filterNature" class="form-select">
                <option value="">Toutes les natures</option>
                <option value="depense">Dépense</option>
                <option value="recette">Recette</option>
            </select>
        </div>
        <div class="col-md-2">
            <select id="filterStatut" class="form-select">
                <option value="">Tous statuts</option>
                <option value="valide">Validé</option>
                <option value="en_attente">En attente</option>
            </select>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" id="btnCreateOperation" data-bs-toggle="modal" data-bs-target="#modalOperation">
                <i class="bi bi-plus-lg"></i> Nouvelle opération
            </button>
        </div>
    </div>

    <!-- Tableau -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover" id="operationsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Tableau</th>
                                <th>Variable</th>
                                <th>Sous-variable</th>
                                <th>Description</th>
                                <th class="text-end">Montant</th>
                                <th>Nature</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="operationsBody">
                            <!-- Contenu chargé via JS / AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Création / Édition -->
<div class="modal fade" id="modalOperation" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="formOperation">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle opération</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="operationId" name="operation_id">
                <div class="mb-2">
                    <label>Date</label>
                    <input type="date" class="form-control" name="date" required>
                </div>
                <div class="mb-2">
                    <label>Montant</label>
                    <input type="number" step="0.01" class="form-control" name="montant" required>
                </div>
                <div class="mb-2">
                    <label>Tableau</label>
                    <select class="form-select" name="variable_id" id="selectVariable" required></select>
                </div>
                <div class="mb-2">
                    <label>Sous-variable</label>
                    <select class="form-select" name="sous_variable_id" id="selectSousVariable"></select>
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <input type="text" class="form-control" name="description">
                </div>
                <div class="mb-2">
                    <label>Nature</label>
                    <select class="form-select" name="nature" required>
                        <option value="depense">Dépense</option>
                        <option value="recette">Recette</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Statut</label>
                    <select class="form-select" name="statut_objet" required>
                        <option value="en_attente">En attente</option>
                        <option value="valide">Validé</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    let operations = []; // Données mock / à remplacer par AJAX depuis controller
    let totalDepenses = 0, totalRecettes = 0;

    const natureChartCtx = document.getElementById('natureChart').getContext('2d');
    const monthlyChartCtx = document.getElementById('monthlyChart').getContext('2d');

    let natureChart = new Chart(natureChartCtx, {
        type: 'doughnut',
        data: { labels: ['Dépenses','Recettes'], datasets: [{ data: [0,0], backgroundColor: ['#dc3545','#198754']}] } ,
        options: { responsive:true }
    });

    let monthlyChart = new Chart(monthlyChartCtx, {
        type: 'bar',
        data: { labels: [], datasets: [{ label:'Montant', data: [], backgroundColor:'#0d6efd' }] },
        options: { responsive:true }
    });

    function updateDashboard(data){
        operations = data;

        totalDepenses = operations.filter(o=>o.nature==='depense').reduce((sum,o)=>sum+parseFloat(o.montant),0);
        totalRecettes = operations.filter(o=>o.nature==='recette').reduce((sum,o)=>sum+parseFloat(o.montant),0);

        document.getElementById('totalDepenses').textContent = totalDepenses.toLocaleString('fr-FR')+' XOF';
        document.getElementById('totalRecettes').textContent = totalRecettes.toLocaleString('fr-FR')+' XOF';
        document.getElementById('solde').textContent = (totalRecettes-totalDepenses).toLocaleString('fr-FR')+' XOF';
        document.getElementById('nbOperations').textContent = operations.length;

        // Update table
        const tbody = document.getElementById('operationsBody');
        tbody.innerHTML = '';
        operations.forEach(op=>{
            tbody.innerHTML += `<tr>
                <td>${op.date}</td>
                <td>${op.tableau}</td>
                <td>${op.variable}</td>
                <td>${op.sous_variable||''}</td>
                <td>${op.description}</td>
                <td class="text-end">${parseFloat(op.montant).toLocaleString('fr-FR')}</td>
                <td>${op.nature}</td>
                <td>${op.statut_objet}</td>
                <td>
                    <button class="btn btn-sm btn-warning editOp" data-id="${op.id}">✏️</button>
                    <button class="btn btn-sm btn-danger deleteOp" data-id="${op.id}">🗑️</button>
                </td>
            </tr>`;
        });

        // Update charts
        const depenses = operations.filter(o=>o.nature==='depense').length;
        const recettes = operations.filter(o=>o.nature==='recette').length;
        natureChart.data.datasets[0].data = [depenses,recettes];
        natureChart.update();

        const months = [...new Set(operations.map(o=>o.date.substr(0,7)))].sort();
        monthlyChart.data.labels = months;
        monthlyChart.data.datasets[0].data = months.map(m=>operations.filter(o=>o.date.startsWith(m)).reduce((sum,o)=>sum+parseFloat(o.montant),0));
        monthlyChart.update();
    }

    // TODO: remplacer par fetch / AJAX
    const mockData = [
        {id:1,date:'2025-08-01',montant:5000,description:'Achat essence',nature:'depense',statut_objet:'valide',tableau:'Transport',variable:'Carburant',sous_variable:'Bus'},
        {id:2,date:'2025-08-02',montant:15000,description:'Salaire',nature:'recette',statut_objet:'valide',tableau:'Revenus',variable:'Salaire',sous_variable:''}
    ];
    updateDashboard(mockData);

});
</script>
@endsection
