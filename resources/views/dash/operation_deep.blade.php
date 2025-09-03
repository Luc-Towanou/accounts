<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Opérations — Gestion de Comptes</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <!-- Flatpickr -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    body { background: #f8fafc; }
    .card-soft { box-shadow: 0 10px 24px rgba(0,0,0,.06), 0 2px 8px rgba(0,0,0,.04); border-radius: 1rem; }
    .kpi { min-height: 112px; }
    .badge-soft-green{background:#e8f7ef;color:#198754;border:1px solid #c7eedb}
    .badge-soft-red{background:#fdecec;color:#dc3545;border:1px solid #f9c7cd}
    .tree-item{cursor:pointer}
    .tree-item .chev{transition:transform .2s ease}
    .tree-item[aria-expanded="true"] .chev{transform:rotate(90deg)}
    .shepherd-element .shepherd-button{border-radius:.75rem}
    .sparkline { display: inline-block; width: 80px; height: 20px; }
    .table th { font-weight: 600; }
    .amount-positive { color: #198754; }
    .amount-negative { color: #dc3545; }
    .filter-section { background-color: #f8f9fa; border-radius: 0.5rem; padding: 1rem; }
    .table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.05) !important; }
    .action-buttons .btn { opacity: 0.6; transition: opacity 0.2s; }
    .action-buttons .btn:hover { opacity: 1; }
    .dropdown-menu { z-index: 1030; }
    .dt-length label { margin-bottom: 0; }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container-xxl">
      <a class="navbar-brand fw-semibold" href="#"><i class="bi bi-wallet2 me-2"></i>Gestion de Comptes</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav" aria-controls="topnav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="topnav">
        <form class="ms-lg-4 my-2 my-lg-0 flex-grow-1" role="search">
          <div class="input-group">
            <span class="input-group-text bg-body"><i class="bi bi-search"></i></span>
            <input class="form-control" type="search" placeholder="Rechercher (opérations, tableaux, variables)… / Search" aria-label="Search">
          </div>
        </form>
        <ul class="navbar-nav ms-lg-3 align-items-lg-center">
          <li class="nav-item me-2">
            <button id="btnStartGuide" class="btn btn-primary rounded-pill"><i class="bi bi-magic me-1"></i> Guide de démarrage</button>
          </li>
          <li class="nav-item me-2">
            <a class="btn btn-outline-secondary rounded-pill" href="#"><i class="bi bi-bell me-1"></i> Notifications</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
              <span class="avatar rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-inline-flex justify-content-center align-items-center" style="width:2rem;height:2rem">LT</span>
              <span>Luc</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#">Profil</a></li>
              <li><a class="dropdown-item" href="#">Paramètres</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#">Déconnexion</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- PAGE -->
  <div class="container-xxl py-4">
    <div class="row g-4">
      <!-- SIDEBAR SIMPLIFIÉE (menus généraux seulement) -->
      <aside class="col-lg-3">
        <div class="card card-soft">
          <div class="card-body">
            <h6 class="text-uppercase text-muted small mb-3">Menu principal</h6>
            <div class="list-group list-group-flush">
              <a href="dashboard.html" class="list-group-item list-group-item-action" id="menuDashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
              <a href="operations.html" class="list-group-item list-group-item-action active" id="menuOperations"><i class="bi bi-list-check me-2"></i>Opérations</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuClients"><i class="bi bi-people me-2"></i>Clients</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuRegles"><i class="bi bi-function me-2"></i>Règles de calcul</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuRapports"><i class="bi bi-bar-chart-line me-2"></i>Rapports</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuParametres"><i class="bi bi-gear me-2"></i>Paramètres</a>
            </div>
            <hr>
            <div class="d-grid gap-2">
              <button class="btn btn-outline-primary" id="createTableauBtn"><i class="bi bi-plus-circle me-1"></i>Créer un tableau</button>
              <button class="btn btn-outline-secondary" id="createVariableBtn"><i class="bi bi-diagram-3 me-1"></i>Créer une variable</button>
              <button class="btn btn-outline-success" id="createOperationBtn" data-bs-toggle="modal" data-bs-target="#operationModal"><i class="bi bi-plus-square me-1"></i>Nouvelle opération</button>
            </div>
          </div>
        </div>

        <div class="card card-soft mt-4" id="alertsCard">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h6 class="mb-0">Alertes & dépassements</h6>
              <i class="bi bi-exclamation-triangle text-danger"></i>
            </div>
            <div id="alertList" class="small"></div>
          </div>
        </div>
      </aside>

      <!-- CONTENU PRINCIPAL -->
      <section class="col-lg-9">
        <!-- Ligne 1: KPIs -->
        <div class="row g-3 align-items-stretch mb-4">
          <div class="col-md-3">
            <div class="card card-soft kpi"><div class="card-body">
              <div class="text-muted small">Total Dépenses</div>
              <div class="fs-4 fw-bold amount-negative" id="kpiDepenses">—</div>
              <div class="small text-secondary">Sur la période</div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card card-soft kpi"><div class="card-body">
              <div class="text-muted small">Total Recettes</div>
              <div class="fs-4 fw-bold amount-positive" id="kpiRecettes">—</div>
              <div class="small text-secondary">Sur la période</div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card card-soft kpi"><div class="card-body">
              <div class="text-muted small">Solde</div>
              <div class="fs-4 fw-bold" id="kpiSolde">—</div>
              <div class="small" id="kpiSoldeNote"> </div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card card-soft kpi"><div class="card-body">
              <div class="text-muted small">Opérations</div>
              <div class="fs-4 fw-bold" id="kpiOps">—</div>
              <div class="small text-secondary">Sur la période</div>
            </div></div>
          </div>
        </div>

        <!-- Ligne 2: Filtres et actions -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card card-soft">
              <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                  <label class="text-muted small me-2">Période :</label>
                  <input type="text" class="form-control date-range" style="max-width: 220px;" id="dateRange" placeholder="Sélectionner une période">
                  
                  <select class="form-select" style="max-width: 180px;" id="filterTableau">
                    <option value="">Tous les tableaux</option>
                  </select>
                  
                  <select class="form-select" style="max-width: 180px;" id="filterVariable">
                    <option value="">Toutes les variables</option>
                  </select>
                  
                  <select class="form-select" style="max-width: 180px;" id="filterType">
                    <option value="">Tous types</option>
                    <option value="depense">Dépense</option>
                    <option value="recette">Recette</option>
                  </select>
                  
                  <select class="form-select" style="max-width: 180px;" id="filterStatut">
                    <option value="">Tous statuts</option>
                    <option value="valide">Validé</option>
                    <option value="en_attente">En attente</option>
                    <option value="rapproche">Rapproché</option>
                  </select>
                  
                  <button class="btn btn-outline-secondary" id="applyFilters"><i class="bi bi-funnel me-1"></i>Appliquer</button>
                  <button class="btn btn-outline-secondary" id="resetFilters"><i class="bi bi-arrow-repeat me-1"></i>Réinitialiser</button>
                </div>
                
                <div class="d-flex flex-wrap gap-2">
                  <button class="btn btn-outline-primary btn-sm" id="btnExportCSV"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Exporter CSV</button>
                  <button class="btn btn-outline-primary btn-sm" id="btnExportExcel"><i class="bi bi-file-earmark-excel me-1"></i>Exporter Excel</button>
                  <button class="btn btn-outline-primary btn-sm" id="btnImport"><i class="bi bi-upload me-1"></i>Importer</button>
                  <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-three-dots"></i> Actions groupées
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="#" id="bulkValidate"><i class="bi bi-check-circle me-2"></i>Valider</a></li>
                      <li><a class="dropdown-item" href="#" id="bulkReconcile"><i class="bi bi-link me-2"></i>Rapprocher</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item text-danger" href="#" id="bulkDelete"><i class="bi bi-trash me-2"></i>Supprimer</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ligne 3: Tableau des opérations -->
        <div class="row">
          <div class="col-12">
            <div class="card card-soft">
              <div class="card-body">
                <table id="operationsTable" class="table table-hover" style="width:100%">
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="selectAll"></th>
                      <th>Date</th>
                      <th>Référence</th>
                      <th>Tableau</th>
                      <th>Variable</th>
                      <th>Sous-variable</th>
                      <th>Description</th>
                      <th class="text-end">Montant</th>
                      <th>Type</th>
                      <th>Client</th>
                      <th>Pièce</th>
                      <th>Tags</th>
                      <th>Statut</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Modal pour créer/modifier une opération -->
  <div class="modal fade" id="operationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="operationModalTitle">Nouvelle opération</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="operationForm">
            <input type="hidden" id="operationId">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="operationDate" class="form-label">Date</label>
                <input type="date" class="form-control" id="operationDate" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="operationAmount" class="form-label">Montant</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" id="operationAmount" required>
                  <span class="input-group-text">XOF</span>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="operationType" class="form-label">Type</label>
                <select class="form-select" id="operationType" required>
                  <option value="depense">Dépense</option>
                  <option value="recette">Recette</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="operationClient" class="form-label">Client/Payeur</label>
                <input type="text" class="form-control" id="operationClient">
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="operationTableau" class="form-label">Tableau</label>
                <select class="form-select" id="operationTableau" required>
                  <option value="">Sélectionner un tableau</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="operationVariable" class="form-label">Variable</label>
                <select class="form-select" id="operationVariable" required>
                  <option value="">Sélectionner une variable</option>
                </select>
              </div>
              <div class="col-md-4 mb-3">
                <label for="operationSousVariable" class="form-label">Sous-variable</label>
                <select class="form-select" id="operationSousVariable">
                  <option value="">Sélectionner une sous-variable</option>
                </select>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="operationDescription" class="form-label">Description</label>
              <textarea class="form-control" id="operationDescription" rows="2"></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="operationTags" class="form-label">Tags (séparés par des virgules)</label>
                <input type="text" class="form-control" id="operationTags" placeholder="ex: urgent, mensuel, pro">
              </div>
              <div class="col-md-6 mb-3">
                <label for="operationStatus" class="form-label">Statut</label>
                <select class="form-select" id="operationStatus" required>
                  <option value="en_attente">En attente</option>
                  <option value="valide">Validé</option>
                  <option value="rapproche">Rapproché</option>
                </select>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="operationReceipt" class="form-label">Pièce jointe</label>
              <input type="file" class="form-control" id="operationReceipt">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-primary" id="saveOperation">Enregistrer</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

  <script>
    // Données mock pour la démonstration
    const MOCK_OPERATIONS = [
      { id: 1, date: '2025-08-27', reference: 'OP-001', tableau: 'Famille', variable: 'Alimentation', sous_variable: 'Déjeuner', description: 'Courses Carrefour', montant: 12500, type: 'depense', client: 'Luc', piece: true, tags: ['courses', 'hebdo'], statut: 'valide' },
      { id: 2, date: '2025-08-26', reference: 'OP-002', tableau: 'Famille', variable: 'Transport', sous_variable: 'Taxi', description: 'Taxi travail', montant: 3500, type: 'depense', client: 'Luc', piece: false, tags: ['transport'], statut: 'valide' },
      { id: 3, date: '2025-08-25', reference: 'OP-003', tableau: 'Logement', variable: 'Loyer', sous_variable: '', description: 'Loyer août', montant: 75000, type: 'depense', client: 'Bailleur', piece: true, tags: ['loyer', 'mensuel'], statut: 'rapproche' },
      { id: 4, date: '2025-08-24', reference: 'OP-004', tableau: 'Perso', variable: 'Loisirs', sous_variable: '', description: 'Cinéma', montant: 5000, type: 'depense', client: 'Luc', piece: true, tags: ['loisir'], statut: 'en_attente' },
      { id: 5, date: '2025-08-23', reference: 'OP-005', tableau: 'Revenus', variable: 'Salaire', sous_variable: '', description: 'Salaire août', montant: 350000, type: 'recette', client: 'Entreprise', piece: false, tags: ['salaire'], statut: 'valide' },
      { id: 6, date: '2025-08-22', reference: 'OP-006', tableau: 'Famille', variable: 'Alimentation', sous_variable: 'Dîner', description: 'Restaurant', montant: 12000, type: 'depense', client: 'Luc', piece: true, tags: ['restaurant'], statut: 'valide' },
      { id: 7, date: '2025-08-21', reference: 'OP-007', tableau: 'Transport', variable: 'Carburant', sous_variable: '', description: 'Essence', montant: 15000, type: 'depense', client: 'Luc', piece: true, tags: ['voiture'], statut: 'valide' },
      { id: 8, date: '2025-08-20', reference: 'OP-008', tableau: 'Revenus', variable: 'Freelance', sous_variable: '', description: 'Projet client X', montant: 120000, type: 'recette', client: 'Client X', piece: false, tags: ['freelance'], statut: 'en_attente' }
    ];

    const MOCK_TABLEAUX = [
      { id: 1, name: 'Famille', variables: [
        { id: 11, name: 'Alimentation', sous_variables: [
          { id: 111, name: 'Déjeuner' },
          { id: 112, name: 'Dîner' }
        ]},
        { id: 12, name: 'Transport', sous_variables: [
          { id: 121, name: 'Bus' },
          { id: 122, name: 'Taxi' }
        ]}
      ]},
      { id: 2, name: 'Logement', variables: [
        { id: 21, name: 'Loyer', sous_variables: [] },
        { id: 22, name: 'Électricité', sous_variables: [] }
      ]},
      { id: 3, name: 'Perso', variables: [
        { id: 31, name: 'Internet', sous_variables: [] },
        { id: 32, name: 'Loisirs', sous_variables: [] }
      ]},
      { id: 4, name: 'Revenus', variables: [
        { id: 41, name: 'Salaire', sous_variables: [] },
        { id: 42, name: 'Freelance', sous_variables: [] }
      ]},
      { id: 5, name: 'Transport', variables: [
        { id: 51, name: 'Carburant', sous_variables: [] },
        { id: 52, name: 'Entretien', sous_variables: [] }
      ]}
    ];

    // Formatter monétaire
    const fmtMoney = (n) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(n);

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
      // Initialiser le sélecteur de date
      flatpickr("#dateRange", {
        mode: "range",
        locale: "fr",
        dateFormat: "d/m/Y",
        allowInput: true
      });

      // Remplir les filtres
      populateFilters();
      
      // Initialiser les KPIs
      updateKPIs();
      
      // Initialiser DataTable
      initDataTable();
      
      // Événements
      document.getElementById('applyFilters').addEventListener('click', applyFilters);
      document.getElementById('resetFilters').addEventListener('click', resetFilters);
      document.getElementById('createOperationBtn').addEventListener('click', resetOperationForm);
      document.getElementById('saveOperation').addEventListener('click', saveOperation);
      document.getElementById('operationTableau').addEventListener('change', updateVariablesDropdown);
      document.getElementById('operationVariable').addEventListener('change', updateSousVariablesDropdown);
      document.getElementById('selectAll').addEventListener('change', toggleSelectAll);
      
      // Exports
      document.getElementById('btnExportCSV').addEventListener('click', () => exportData('csv'));
      document.getElementById('btnExportExcel').addEventListener('click', () => exportData('excel'));
    });

    // Fonctions
    function populateFilters() {
      const tableauFilter = document.getElementById('filterTableau');
      const operationTableau = document.getElementById('operationTableau');
      
      MOCK_TABLEAUX.forEach(tableau => {
        tableauFilter.innerHTML += `<option value="${tableau.name}">${tableau.name}</option>`; //ici 
        operationTableau.innerHTML += `<option value="${tableau.id}">${tableau.name}</option>`;
      });
    }

    function updateKPIs() {
      const depenses = MOCK_OPERATIONS.filter(op => op.type === 'depense').reduce((sum, op) => sum + op.montant, 0);
      const recettes = MOCK_OPERATIONS.filter(op => op.type === 'recette').reduce((sum, op) => sum + op.montant, 0);
      const solde = recettes - depenses;
      
      document.getElementById('kpiDepenses').textContent = fmtMoney(depenses);
      document.getElementById('kpiRecettes').textContent = fmtMoney(recettes);
      document.getElementById('kpiSolde').textContent = fmtMoney(solde);
      document.getElementById('kpiOps').textContent = MOCK_OPERATIONS.length;
      
      const note = document.getElementById('kpiSoldeNote');
      note.textContent = solde >= 0 ? '✅ Excédent' : '⚠ Déficit';
      note.className = 'small ' + (solde >= 0 ? 'text-success' : 'text-danger');
    }

    function initDataTable() {
      $('#operationsTable').DataTable({
        data: MOCK_OPERATIONS,
        responsive: true,
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        columns: [
          { 
            data: null,
            render: function(data, type, row) {
              return `<input type="checkbox" class="row-select" data-id="${row.id}">`;
            },
            orderable: false
          },
          { data: 'date' },
          { data: 'reference' },
          { data: 'tableau' },
          { data: 'variable' },
          { data: 'sous_variable' },
          { data: 'description' },
          { 
            data: 'montant',
            render: function(data, type, row) {
              const cls = row.type === 'depense' ? 'amount-negative' : 'amount-positive';
              return `<span class="${cls}">${fmtMoney(data)}</span>`;
            }
          },
          { 
            data: 'type',
            render: function(data) {
              const badgeClass = data === 'depense' ? 'badge bg-danger' : 'badge bg-success';
              const text = data === 'depense' ? 'Dépense' : 'Recette';
              return `<span class="${badgeClass}">${text}</span>`;
            }
          },
          { data: 'client' },
          { 
            data: 'piece',
            render: function(data) {
              return data ? '<i class="bi bi-paperclip"></i>' : '';
            }
          },
          { 
            data: 'tags',
            render: function(data) {
              return data.map(tag => `<span class="badge bg-secondary me-1">${tag}</span>`).join('');
            }
          },
          { 
            data: 'statut',
            render: function(data) {
              let badgeClass = 'bg-secondary';
              if (data === 'valide') badgeClass = 'bg-success';
              if (data === 'rapproche') badgeClass = 'bg-info';
              
              let text = 'En attente';
              if (data === 'valide') text = 'Validé';
              if (data === 'rapproche') text = 'Rapproché';
              
              return `<span class="badge ${badgeClass}">${text}</span>`;
            }
          },
          {
            data: null,
            render: function(data, type, row) {
              return `
                <div class="action-buttons">
                  <button class="btn btn-sm btn-outline-primary edit-op" data-id="${row.id}"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-sm btn-outline-danger delete-op" data-id="${row.id}"><i class="bi bi-trash"></i></button>
                </div>
              `;
            },
            orderable: false
          }
        ],
        order: [[1, 'desc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        pageLength: 10,
        drawCallback: function() {
          // Ajouter les événements après le rendu du tableau
          $('.edit-op').on('click', function() {
            const id = $(this).data('id');
            editOperation(id);
          });
          
          $('.delete-op').on('click', function() {
            const id = $(this).data('id');
            deleteOperation(id);
          });
          
          $('.row-select').on('change', function() {
            updateSelectAllState();
          });
        }
      });
    }

    function applyFilters() {
      // Implémentation simplifiée pour la démo
      alert('Filtres appliqués! En production, cela rechargerait le tableau avec les filtres.');
    }

    function resetFilters() {
      document.getElementById('dateRange').value = '';
      document.getElementById('filterTableau').value = '';
      document.getElementById('filterVariable').value = '';
      document.getElementById('filterType').value = '';
      document.getElementById('filterStatut').value = '';
      
      // Réinitialiser aussi le DataTable
      $('#operationsTable').DataTable().search('').draw();
    }

    function resetOperationForm() {
      document.getElementById('operationModalTitle').textContent = 'Nouvelle opération';
      document.getElementById('operationForm').reset();
      document.getElementById('operationId').value = '';
      
      // Réinitialiser les sélecteurs
      document.getElementById('operationVariable').innerHTML = '<option value="">Sélectionner une variable</option>';
      document.getElementById('operationSousVariable').innerHTML = '<option value="">Sélectionner une sous-variable</option>';
    }

    function editOperation(id) {
      const operation = MOCK_OPERATIONS.find(op => op.id === id);
      if (!operation) return;
      
      document.getElementById('operationModalTitle').textContent = 'Modifier opération';
      document.getElementById('operationId').value = operation.id;
      document.getElementById('operationDate').value = operation.date;
      document.getElementById('operationAmount').value = operation.montant;
      document.getElementById('operationType').value = operation.type;
      document.getElementById('operationClient').value = operation.client;
      document.getElementById('operationDescription').value = operation.description;
      document.getElementById('operationTags').value = operation.tags.join(', ');
      document.getElementById('operationStatus').value = operation.statut;
      
      // Trouver le tableau correspondant
      const tableau = MOCK_TABLEAUX.find(t => t.name === operation.tableau);
      if (tableau) {
        document.getElementById('operationTableau').value = tableau.id;
        updateVariablesDropdown();
        
        // Trouver la variable correspondante
        const variable = tableau.variables.find(v => v.name === operation.variable);
        if (variable) {
          setTimeout(() => {
            document.getElementById('operationVariable').value = variable.id;
            updateSousVariablesDropdown();
            
            // Trouver la sous-variable correspondante
            if (operation.sous_variable) {
              const sousVariable = variable.sous_variables.find(s => s.name === operation.sous_variable);
              if (sousVariable) {
                document.getElementById('operationSousVariable').value = sousVariable.id;
              }
            }
          }, 100);
        }
      }
      
      // Ouvrir le modal
      const modal = new bootstrap.Modal(document.getElementById('operationModal'));
      modal.show();
    }

    function updateVariablesDropdown() {
      const tableauId = document.getElementById('operationTableau').value;
      const variableSelect = document.getElementById('operationVariable');
      
      variableSelect.innerHTML = '<option value="">Sélectionner une variable</option>';
      
      if (!tableauId) return;
      
      const tableau = MOCK_TABLEAUX.find(t => t.id == tableauId);
      if (tableau) {
        tableau.variables.forEach(variable => {
          variableSelect.innerHTML += `<option value="${variable.id}">${variable.name}</option>`;
        });
      }
    }

    function updateSousVariablesDropdown() {
      const tableauId = document.getElementById('operationTableau').value;
      const variableId = document.getElementById('operationVariable').value;
      const sousVariableSelect = document.getElementById('operationSousVariable');
      
      sousVariableSelect.innerHTML = '<option value="">Sélectionner une sous-variable</option>';
      
      if (!tableauId || !variableId) return;
      
      const tableau = MOCK_TABLEAUX.find(t => t.id == tableauId);
      if (tableau) {
        const variable = tableau.variables.find(v => v.id == variableId);
        if (variable && variable.sous_variables.length > 0) {
          variable.sous_variables.forEach(sousVariable => {
            sousVariableSelect.innerHTML += `<option value="${sousVariable.id}">${sousVariable.name}</option>`;
          });
        }
      }
    }

    function saveOperation() {
      // Récupérer les valeurs du formulaire
      const formData = {
        id: document.getElementById('operationId').value,
        date: document.getElementById('operationDate').value,
        montant: parseFloat(document.getElementById('operationAmount').value),
        type: document.getElementById('operationType').value,
        client: document.getElementById('operationClient').value,
        description: document.getElementById('operationDescription').value,
        tags: document.getElementById('operationTags').value.split(',').map(tag => tag.trim()),
        statut: document.getElementById('operationStatus').value,
        tableauId: document.getElementById('operationTableau').value,
        variableId: document.getElementById('operationVariable').value,
        sousVariableId: document.getElementById('operationSousVariable').value
      };
      
      // Validation basique
      if (!formData.date || !formData.montant || !formData.tableauId || !formData.variableId) {
        alert('Veuillez remplir tous les champs obligatoires.');
        return;
      }
      
      // Trouver les noms à partir des IDs
      const tableau = MOCK_TABLEAUX.find(t => t.id == formData.tableauId);
      const variable = tableau.variables.find(v => v.id == formData.variableId);
      let sousVariable = null;
      
      if (formData.sousVariableId) {
        sousVariable = variable.sous_variables.find(s => s.id == formData.sousVariableId);
      }
      
      // En production, on enverrait ces données au serveur
      console.log('Données à sauvegarder:', formData);
      
      // Fermer le modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('operationModal'));
      modal.hide();
      
      // Afficher un message de succès
      alert('Opération enregistrée avec succès!');
    }

    function deleteOperation(id) {
      if (confirm('Êtes-vous sûr de vouloir supprimer cette opération ?')) {
        // En production, on enverrait une requête DELETE au serveur
        console.log('Suppression de l\'opération:', id);
        alert('Opération supprimée! (simulation)');
      }
    }

    function toggleSelectAll() {
      const isChecked = document.getElementById('selectAll').checked;
      document.querySelectorAll('.row-select').forEach(checkbox => {
        checkbox.checked = isChecked;
      });
    }

    function updateSelectAllState() {
      const checkboxes = document.querySelectorAll('.row-select');
      const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(checkbox => checkbox.checked);
      document.getElementById('selectAll').checked = allChecked;
    }

    function exportData(format) {
      alert(`Export ${format.toUpperCase()} en cours... (simulation)`);
      // En production, cela déclencherait le téléchargement du fichier
    }
  </script>
</body>
</html>