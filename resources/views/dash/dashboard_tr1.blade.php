<!doctype html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — Gestion de Comptes</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Shepherd.js (tour guidé) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@13.1.1/dist/css/shepherd.css">
  <!-- Shepherd JS -->
  <script src="https://cdn.jsdelivr.net/npm/shepherd.js/dist/js/shepherd.min.js"></script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  
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
            <!-- Ici s’affichera le guide -->
                {{-- <div id="guideContainer" class="container my-5" style="display:none;">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <h4 id="guideTitle"></h4>
                            <p id="guideText"></p>
                            <div class="d-flex justify-content-between">
                                <button id="prevStep" class="btn btn-outline-secondary">⬅ Précédent</button>
                                <button id="nextStep" class="btn btn-outline-primary">Suivant ➡</button>
                            </div>
                        </div>
                    </div>
                </div> --}}
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
              <a href="#" class="list-group-item list-group-item-action active" id="menuDashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuOperations"><i class="bi bi-list-check me-2"></i>Opérations</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuClients"><i class="bi bi-people me-2"></i>Clients</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuRegles"><i class="bi bi-function me-2"></i>Règles de calcul</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuRapports"><i class="bi bi-bar-chart-line me-2"></i>Rapports</a>
              <a href="#" class="list-group-item list-group-item-action" id="menuParametres"><i class="bi bi-gear me-2"></i>Paramètres</a>
            </div>
            <hr>
            <div class="d-grid gap-2">
              <button class="btn btn-outline-primary" id="createTableauBtn"><i class="bi bi-plus-circle me-1"></i>Créer un tableau</button>
              <button class="btn btn-outline-secondary" id="createVariableBtn"><i class="bi bi-diagram-3 me-1"></i>Créer une variable</button>
              <button class="btn btn-outline-success" id="createOperationBtn"><i class="bi bi-plus-square me-1"></i>Nouvelle opération</button>
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
        <!-- Ligne 1: KPIs + contrôles période -->
        <div class="row g-3 align-items-stretch">
          <div class="col-md-3">
            <div class="card card-soft kpi" id="kpiBudgetCard"><div class="card-body">
              <div class="text-muted small">Budget prévu</div>
              <div class="fs-4 fw-bold" id="kpiBudget">—</div>
              <div class="small text-secondary">Prévisions totales</div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card card-soft kpi" id="kpiReelCard"><div class="card-body">
              <div class="text-muted small">Dépenses réelles</div>
              <div class="fs-4 fw-bold" id="kpiDepenses">—</div>
              <div class="small text-secondary">Somme enregistrée</div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card card-soft kpi" id="kpiSoldeCard"><div class="card-body">
              <div class="text-muted small">Solde</div>
              <div class="fs-4 fw-bold" id="kpiSolde">—</div>
              <div class="small" id="kpiSoldeNote"> </div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card card-soft kpi" id="kpiOpsCard"><div class="card-body">
              <div class="text-muted small">Opérations</div>
              <div class="fs-4 fw-bold" id="kpiOps">—</div>
              <div class="small text-secondary">30 derniers jours</div>
            </div></div>
          </div>

          <div class="col-12" id="rangeControls">
            <div class="card card-soft">
              <div class="card-body d-flex flex-wrap gap-2 align-items-center">
                <label class="text-muted small me-2">Période :</label>
                <input type="month" class="form-control" style="max-width: 220px;" id="monthPicker">
                <div class="vr mx-2 d-none d-md-block"></div>
                <input type="date" class="form-control" style="max-width: 220px;" id="dateStart">
                <span class="mx-1">→</span>
                <input type="date" class="form-control" style="max-width: 220px;" id="dateEnd">
                <button class="btn btn-outline-secondary ms-auto" id="applyRange"><i class="bi bi-funnel me-1"></i>Appliquer</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Ligne 2: Donut + STRUCTURE (arborescence) -->
        <div class="row g-3 mt-1">
          <div class="col-lg-6">
            <div class="card card-soft" id="donutCard">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Répartition par tableau (dépenses)</h6>
                <span class="text-muted small">Donut</span>
              </div>
              <div class="card-body"><canvas id="chartDonut" height="170"></canvas></div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card card-soft" id="structureCard">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Structure du mois (Tableaux → Variables → Sous-variables)</h6>
                <span class="text-muted small">Cliquer pour déplier</span>
              </div>
              <div class="card-body" id="treeRoot">
                <!-- Arborescence injectée par JS -->
              </div>
            </div>
          </div>
        </div>

        <!-- Ligne 3: courbes/Barres (en bas de la section graphes, comme demandé) -->
        <div class="row g-3 mt-1">
          <div class="col-12">
            <div class="card card-soft" id="barsCard">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Évolution des dépenses (hebdo)</h6>
                <span class="text-muted small">Barres</span>
              </div>
              <div class="card-body"><canvas id="chartBars" height="160"></canvas></div>
            </div>
          </div>
        </div>

        <!-- Ligne 4: Opérations récentes -->
        <div class="row g-3 mt-1">
          <div class="col-12">
            <div class="card card-soft" id="opsCard">
              <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Opérations récentes</h6>
                <button class="btn btn-success btn-sm" id="btnNewOp"><i class="bi bi-plus-lg me-1"></i>Nouvelle opération</button>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0" id="operationsTable">
                  <thead class="table-light">
                    <tr>
                      <th>Date</th><th>Tableau</th><th>Variable</th><th>Sous-variable</th><th class="text-end">Montant</th><th>Client</th>
                    </tr>
                  </thead>
                  <tbody id="recentOpsTbody"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- SHEPHERD & BOOTSTRAP SCRIPTS -->
  {{-- <script src="https://cdn.jsdelivr.net/npm/shepherd.js@13.1.1/dist/js/shepherd.min.js"></script> --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // ---------------------- MOCK DATA ----------------------
    const MOCK = {
      month: '2025-08',
      tableaux: [
        { id: 1, name: 'Famille', prevu: 500, reel: 420, variables: [
          { id: 11, name: 'Alimentation', prevu: 300, reel: 250, sous: [
            { id: 111, name: 'Déjeuner', prevu: 100, reel: 90 },
            { id: 112, name: 'Dîner', prevu: 200, reel: 160 },
          ]},
          { id: 12, name: 'Transport', prevu: 200, reel: 170, sous: [
            { id: 121, name: 'Bus', prevu: 100, reel: 80 },
            { id: 122, name: 'Taxi', prevu: 100, reel: 90 },
          ]}
        ]},
        { id: 2, name: 'Logement', prevu: 300, reel: 300, variables: [
          { id: 21, name: 'Loyer', prevu: 250, reel: 250, sous: [] },
          { id: 22, name: 'Électricité', prevu: 50, reel: 50, sous: [] },
        ]},
        { id: 3, name: 'Perso', prevu: 200, reel: 120, variables: [
          { id: 31, name: 'Internet', prevu: 30, reel: 30, sous: [] },
          { id: 32, name: 'Loisirs', prevu: 170, reel: 90, sous: [] },
        ]}
      ],
      operations: [
        { date: '2025-08-27', tableau: 'Famille', variable: 'Alimentation', sous: 'Déjeuner', montant: 12.50, client: 'Payeur' },
        { date: '2025-08-26', tableau: 'Famille', variable: 'Transport', sous: 'Taxi', montant: 8.00, client: 'Payeur' },
        { date: '2025-08-24', tableau: 'Logement', variable: 'Loyer', sous: '-', montant: 250.00, client: 'Bailleur' },
        { date: '2025-08-22', tableau: 'Perso', variable: 'Loisirs', sous: '-', montant: 45.00, client: 'Payeur' },
        { date: '2025-08-20', tableau: 'Famille', variable: 'Alimentation', sous: 'Dîner', montant: 20.00, client: 'Payeur' },
      ]
    };

    // ---------------------- HELPERS ----------------------
    const fmtMoney = (n) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(n);

    // ---------------------- RENDER UI ----------------------
    function renderKPIs(){
      const totalPrevu = MOCK.tableaux.reduce((a,t)=>a+t.prevu,0);
      const totalReel  = MOCK.tableaux.reduce((a,t)=>a+t.reel,0);
      const solde = totalPrevu-totalReel;
      document.getElementById('kpiBudget').textContent = fmtMoney(totalPrevu);
      document.getElementById('kpiDepenses').textContent = fmtMoney(totalReel);
      document.getElementById('kpiSolde').textContent = fmtMoney(solde);
      const note = document.getElementById('kpiSoldeNote');
      note.textContent = solde >= 0 ? '✅ Sous le budget' : '⚠️ Dépassement';
      note.className = 'small ' + (solde>=0 ? 'text-success' : 'text-danger');
      document.getElementById('kpiOps').textContent = String(MOCK.operations.length);
    }

    function renderAlerts(){
      const box = document.getElementById('alertList');
      box.innerHTML = '';
      const alertItem = (label, prevu, reel) => {
        const over = reel>prevu; const diff = Math.abs(reel-prevu);
        const badgeCls = over? 'badge-soft-red' : 'badge-soft-green';
        const icon = over? 'bi-emoji-frown' : 'bi-emoji-smile';
        const row = document.createElement('div');
        row.className = 'd-flex justify-content-between align-items-center border rounded-3 p-2 mb-2';
        row.innerHTML = `<div><i class="bi ${icon} me-1"></i><strong>${label}</strong><div class="text-muted small">Prévu: ${fmtMoney(prevu)} • Réel: ${fmtMoney(reel)}</div></div><span class="badge ${badgeCls}">${over? '+':''}${fmtMoney(reel-prevu)}</span>`;
        box.appendChild(row);
      };
      MOCK.tableaux.forEach(t=>{ alertItem('Tableau — '+t.name, t.prevu, t.reel); t.variables.forEach(v=>alertItem('Variable — '+v.name, v.prevu, v.reel)); });
    }

    function renderTree(){
      const root = document.getElementById('treeRoot');
      root.innerHTML = '';
      const makeBadge = (prevu, reel) => `<span class="badge rounded-pill ms-2 ${reel>prevu?'text-bg-danger':'text-bg-success'}">${fmtMoney(reel)} / ${fmtMoney(prevu)}</span>`;
      MOCK.tableaux.forEach((t, ti)=>{
        const card = document.createElement('div');
        card.className = 'mb-2';
        const head = document.createElement('div');
        head.className = 'tree-item d-flex align-items-center p-2 border rounded-3';
        head.setAttribute('role','button'); head.setAttribute('aria-expanded','false'); head.id = `tree-t-${ti}`;
        head.innerHTML = `<i class="bi bi-caret-right-fill chev me-2 text-muted"></i><i class="bi bi-folder-fill text-primary me-2"></i><span class="fw-semibold">${t.name}</span>${makeBadge(t.prevu, t.reel)}`;
        const body = document.createElement('div'); body.className='ps-4 pt-2 d-none';
        t.variables.forEach((v, vi)=>{
          const vHead = document.createElement('div');
          vHead.className = 'tree-item d-flex align-items-center p-2 rounded-3';
          vHead.setAttribute('role','button'); vHead.setAttribute('aria-expanded','false'); vHead.id = `tree-v-${ti}-${vi}`;
          vHead.innerHTML = `<i class="bi bi-caret-right-fill chev me-2 text-muted"></i><i class="bi bi-diagram-3-fill text-primary me-2"></i><span>${v.name}</span>${makeBadge(v.prevu, v.reel)}`;
          const vBody = document.createElement('div'); vBody.className='ps-4 pt-1 d-none';
          if(v.sous?.length){
            v.sous.forEach(s=>{
              const sRow = document.createElement('div');
              sRow.className='d-flex align-items-center p-2 rounded-3';
              sRow.innerHTML = `<i class="bi bi-dot text-muted me-2"></i><span>${s.name}</span>${makeBadge(s.prevu, s.reel)}`;
              vBody.appendChild(sRow);
            });
          }else{
            const empty = document.createElement('div'); empty.className='text-muted small ps-4'; empty.textContent='Aucune sous-variable'; vBody.appendChild(empty);
          }
          vHead.addEventListener('click',()=>{ vBody.classList.toggle('d-none'); vHead.setAttribute('aria-expanded', vBody.classList.contains('d-none')?'false':'true'); }); 
          body.appendChild(vHead); body.appendChild(vBody);
        });
        head.addEventListener('click',()=>{ body.classList.toggle('d-none'); head.setAttribute('aria-expanded', body.classList.contains('d-none')?'false':'true'); });
        card.appendChild(head); card.appendChild(body); root.appendChild(card);
      });
    }

    function renderCharts(){
      const donutLabels = MOCK.tableaux.map(t=>t.name);
      const donutData = MOCK.tableaux.map(t=>t.reel);
      new Chart(document.getElementById('chartDonut'), { type:'doughnut', data:{ labels:donutLabels, datasets:[{ data:donutData }] }, options:{ cutout:'60%', plugins:{ legend:{ position:'bottom' }, tooltip:{ callbacks:{ label:(c)=>`${c.label}: ${fmtMoney(c.parsed)}` }}}}});
      new Chart(document.getElementById('chartBars'), { type:'bar', data:{ labels:['S1','S2','S3','S4'], datasets:[{ label:'Dépenses', data:[120,180,150,200] }] }, options:{ plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(c)=>fmtMoney(c.parsed.y) } } }, scales:{ y:{ ticks:{ callback:(v)=>fmtMoney(v) } } } }});
    }

    function renderOps(){
      const tb = document.getElementById('recentOpsTbody'); tb.innerHTML='';
      MOCK.operations.forEach(op=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${op.date}</td><td>${op.tableau}</td><td>${op.variable}</td><td>${op.sous}</td><td class='text-end fw-semibold'>${fmtMoney(op.montant)}</td><td>${op.client}</td>`;
        tb.appendChild(tr);
      });
    }

    // ---------------------- ONBOARDING (Shepherd) > 10 étapes ----------------------
    function startGuide(){
       // Vérifier que Shepherd est bien chargé
        if (typeof Shepherd === 'undefined') {
            console.error('Shepherd.js n\'est pas chargé');
            return;
        }
                    
      const tour = new Shepherd.Tour({
        defaultStepOptions: { cancelIcon: { enabled: true }, scrollTo: { behavior: 'smooth', block: 'center' }, classes: 'shadow rounded-4', arrow: true } 
      });

      tour.addStep({ id:'welcome', title:'Bienvenue 👋', text:'Découvrons votre dashboard en 12 étapes. / Let\'s explore your dashboard in 12 steps.', buttons:[{ text:'Suivant ➜', action:tour.next }], modalOverlayOpeningPadding:4 });

      tour.addStep({ id:'kpiBudget', attachTo:{ element:'#kpiBudgetCard', on:'bottom' }, title:'Budget prévu', text:'Somme de toutes les prévisions du mois. / Sum of all planned budgets.', buttons:[{ text:'Suivant', action:tour.next }] });

      tour.addStep({ id:'kpiReel', attachTo:{ element:'#kpiReelCard', on:'bottom' }, title:'Dépenses réelles', text:'Total des opérations enregistrées. / Total actual spend.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'kpiSolde', attachTo:{ element:'#kpiSoldeCard', on:'bottom' }, title:'Solde', text:'Prévu – Réel. Vert si vous êtes sous le budget. / Planned – Actual.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'kpiOps', attachTo:{ element:'#kpiOpsCard', on:'bottom' }, title:'Nombre d\'opérations', text:'Compteur des opérations sur la période. / Operations count.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'periode', attachTo:{ element:'#rangeControls', on:'top' }, title:'Choisir une période', text:'Filtre par mois ou plage personnalisée. / Filter by month or custom range.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'donut', attachTo:{ element:'#donutCard', on:'top' }, title:'Répartition par tableau', text:'Ce donut montre quelles catégories consomment le budget. / Donut shows spend split.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'structure', attachTo:{ element:'#structureCard', on:'top' }, title:'Structure du mois', text:'Ici la hiérarchie <b>Tableaux → Variables → Sous-variables</b> avec indicateurs <i>(réel/prévu)</i>. / Hierarchy with badges.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'bars', attachTo:{ element:'#barsCard', on:'top' }, title:'Évolution des dépenses', text:'Vue temporelle (hebdo). / Weekly evolution chart.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'createTableau', attachTo:{ element:'#createTableauBtn', on:'right' }, title:'Créer un tableau', text:'Un <b>tableau</b> doit être <u>divisé en variables</u>. / A board must be split into variables.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'createVariable', attachTo:{ element:'#createVariableBtn', on:'right' }, title:'Créer une variable', text:'Une <b>variable</b> peut être <u>subdivisée en sous-variables</u> <em>ou</em> reliée directement à des opérations. / Variable can have sub-variables or direct ops.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'createOp', attachTo:{ element:'#createOperationBtn', on:'right' }, title:'Nouvelle opération', text:'Une opération est <b>liée</b> à une <u>variable</u> <em>ou</em> à une <u>sous-variable</u>. / Operation links to variable or sub-variable.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'opsTable', attachTo:{ element:'#opsCard', on:'top' }, title:'Opérations récentes', text:'Historique des dernières opérations. / Recent operations log.', buttons:[{ text:'Suivant', action:tour.next },{ text:'Retour', action:tour.back }] });

      tour.addStep({ id:'alerts', attachTo:{ element:'#alertsCard', on:'right' }, title:'Alertes budgétaires', text:'Dépassements (rouge) ou OK (vert) par tableau/variable. / Budget alerts.', buttons:[{ text:'Terminer ✅', action:tour.complete },{ text:'Retour', action:tour.back }] });

      tour.start();
    }

    // ---------------------- INIT ----------------------
    document.addEventListener('DOMContentLoaded', () => {
      renderKPIs();
      renderAlerts();
      renderTree();
      renderCharts();
      renderOps();

      document.getElementById('applyRange').addEventListener('click', ()=>{/* plug API later */});
      document.getElementById('btnNewOp').addEventListener('click', ()=>{/* open modal later */});

    //   document.getElementById('btnStartGuide').addEventListener('click', (e)=>{ e.preventDefault(); startGuide(); });
     // Correction : Ajoutez l'écouteur d'événement correctement
    const guideButton = document.getElementById('btnStartGuide');
    if (guideButton) {
        guideButton.addEventListener('click', function(e) {
            e.preventDefault();
            startGuide();
        });
    } else {
        console.error('Bouton guide non trouvé');
    }

    });
  </script>
</body>
</html>
