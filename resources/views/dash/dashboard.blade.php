<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — Gestion de Comptes</title>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Tailwind config (facultatif pour couleurs personnalisées)
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc', 400: '#818cf8',
              500: '#6366f1', 600: '#4f46e5', 700: '#4338ca', 800: '#3730a3', 900: '#312e81'
            }
          },
          boxShadow: {
            soft: '0 10px 20px rgba(0,0,0,0.05), 0 6px 6px rgba(0,0,0,0.05)'
          },
          borderRadius: {
            '2xl': '1rem'
          }
        }
      }
    }
  </script>
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    /* Scrollbar discrète */
    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 8px; }
    ::-webkit-scrollbar-track { background: transparent; }
  </style>
</head>
<body class="bg-gray-50 text-gray-800">
  <!-- Layout Wrapper -->
  <div class="min-h-screen flex flex-col">
    <!-- Top Navbar -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-gray-200">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="h-16 flex items-center justify-between">
          <!-- Left: Brand + Mobile Sidebar Toggle -->
          <div class="flex items-center gap-3">
            <button id="btnOpenSidebar" class="lg:hidden inline-flex items-center justify-center p-2 rounded-xl border border-gray-200 hover:bg-gray-100" aria-label="Ouvrir la navigation">
              <i data-lucide="panel-left-open" class="w-5 h-5"></i>
            </button>
            <a href="#" class="flex items-center gap-2 font-semibold text-primary-700">
              <i data-lucide="wallet" class="w-6 h-6"></i>
              <span>Gestion de Comptes</span>
            </a>
          </div>

          <!-- Center: Search -->
          <div class="hidden md:block flex-1 mx-6">
            <div class="relative max-w-xl mx-auto">
              <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
              <input type="text" placeholder="Rechercher (opérations, tableaux, variables) / Search"
                     class="w-full pl-10 pr-4 py-2 rounded-2xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary-200 focus:border-primary-400" />
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center gap-2">
            <button class="relative p-2 rounded-xl border border-gray-200 hover:bg-gray-100" aria-label="Notifications">
              <i data-lucide="bell" class="w-5 h-5"></i>
              <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] px-1.5 rounded-full">3</span>
            </button>
            <div class="h-6 w-px bg-gray-200 mx-1"></div>
            <button class="flex items-center gap-2 px-2 py-1.5 rounded-xl border border-gray-200 hover:bg-gray-100">
              <div class="size-8 rounded-full bg-primary-100 grid place-items-center">
                <span class="text-primary-700 font-semibold text-sm">LT</span>
              </div>
              <span class="hidden sm:block text-sm">Luc</span>
              <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400"></i>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="flex-1 flex">
      <!-- Sidebar -->
      <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-80 translate-x-[-100%] lg:translate-x-0 transition-transform duration-200 bg-white border-r border-gray-200 overflow-y-auto">
        <div class="h-16 px-4 flex items-center justify-between lg:hidden border-b border-gray-200">
          <span class="font-semibold">Navigation</span>
          <button id="btnCloseSidebar" class="p-2 rounded-xl border border-gray-200">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>
        <nav class="px-4 py-4 space-y-6">
          <!-- Primary Menu -->
          <div>
            <h3 class="px-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Menu principal</h3>
            <ul class="mt-2 space-y-1">
              <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl bg-primary-50 text-primary-700 border border-primary-100">
                  <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                  <span>Dashboard</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-200">
                  <i data-lucide="list-plus" class="w-5 h-5"></i>
                  <span>Opérations</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-200">
                  <i data-lucide="users" class="w-5 h-5"></i>
                  <span>Clients</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-200">
                  <i data-lucide="sigma" class="w-5 h-5"></i>
                  <span>Règles de calcul</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-200">
                  <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                  <span>Rapports</span>
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-200">
                  <i data-lucide="settings" class="w-5 h-5"></i>
                  <span>Paramètres</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- Structure Tree with KPIs -->
          <div>
            <h3 class="px-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Structure (Tableaux → Variables → Sous-variables)</h3>
            <div id="tree" class="mt-2 space-y-1"></div>
            <div class="mt-3 text-xs text-gray-500 px-2">Astuce: cliquez sur les chevrons pour déplier/replier.</div>
          </div>
        </nav>
      </aside>

      <!-- Main -->
      <main class="flex-1 min-w-0">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
          <!-- Page Title -->
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-xl sm:text-2xl font-bold">Tableau de bord — Mois courant</h1>
              <p class="text-sm text-gray-500">Résumé exécutif • Executive summary</p>
            </div>
            <div class="flex items-center gap-2">
              <input type="month" id="monthPicker" class="border border-gray-200 rounded-xl px-3 py-2 text-sm" />
              <button id="btnCustomRange" class="px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-100 text-sm">Plage personnalisée</button>
            </div>
          </div>

          <!-- KPIs Cards -->
          <section class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Budget prévu</span>
                <i data-lucide="target" class="w-5 h-5 text-primary-600"></i>
              </div>
              <div class="mt-2 text-2xl font-bold" id="kpiBudgetPrevus">—</div>
              <div class="text-xs text-gray-500" id="kpiBudgetPrevusNote">Prévisions totales</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Dépenses réelles</span>
                <i data-lucide="credit-card" class="w-5 h-5 text-primary-600"></i>
              </div>
              <div class="mt-2 text-2xl font-bold" id="kpiDepensesReelles">—</div>
              <div class="text-xs text-gray-500" id="kpiDepensesReellesNote">Somme enregistrée</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Solde (Prévu - Réel)</span>
                <i data-lucide="piggy-bank" class="w-5 h-5 text-primary-600"></i>
              </div>
              <div class="mt-2 text-2xl font-bold" id="kpiSolde">—</div>
              <div class="text-xs" id="kpiSoldeNote"></div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Opérations</span>
                <i data-lucide="list-checks" class="w-5 h-5 text-primary-600"></i>
              </div>
              <div class="mt-2 text-2xl font-bold" id="kpiNbOperations">—</div>
              <div class="text-xs text-gray-500">Derniers 30 jours</div>
            </div>
          </section>

          <!-- Charts + Alerts -->
          <section class="mt-6 grid lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
              <div class="flex items-center justify-between">
                <h2 class="font-semibold">Répartition par tableau (Dépenses)</h2>
                <div class="text-xs text-gray-500">Donut chart</div>
              </div>
              <div class="mt-4">
                <canvas id="chartDonut" class="w-full" height="140"></canvas>
              </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
              <div class="flex items-center justify-between">
                <h2 class="font-semibold">Alertes & dépassements</h2>
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
              </div>
              <div id="alertList" class="mt-3 space-y-3 text-sm"></div>
            </div>
          </section>

          <section class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
            <div class="flex items-center justify-between">
              <h2 class="font-semibold">Évolution des dépenses (par semaine)</h2>
              <div class="text-xs text-gray-500">Bar chart</div>
            </div>
            <div class="mt-4">
              <canvas id="chartBars" class="w-full" height="120"></canvas>
            </div>
          </section>

          <!-- Recent operations -->
          <section class="mt-6 bg-white rounded-2xl border border-gray-200 shadow-soft p-4">
            <div class="flex items-center justify-between">
              <h2 class="font-semibold">Opérations récentes</h2>
              <button class="px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-100 text-sm flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> Nouvelle opération
              </button>
            </div>
            <div class="mt-4 overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead>
                  <tr class="text-left text-gray-500">
                    <th class="py-2 pr-4">Date</th>
                    <th class="py-2 pr-4">Tableau</th>
                    <th class="py-2 pr-4">Variable</th>
                    <th class="py-2 pr-4">Sous-variable</th>
                    <th class="py-2 pr-4">Montant</th>
                    <th class="py-2 pr-4">Client</th>
                  </tr>
                </thead>
                <tbody id="recentOpsTbody" class="divide-y divide-gray-100"></tbody>
              </table>
            </div>
          </section>
        </div>
      </main>
    </div>
  </div>

  <!-- Custom Range Modal (simplifié) -->
  <div id="modalRange" class="hidden fixed inset-0 z-50">
    <div class="absolute inset-0 bg-black/30"></div>
    <div class="relative mx-auto mt-24 max-w-md bg-white rounded-2xl p-4 shadow-soft">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Plage personnalisée</h3>
        <button id="btnCloseRange" class="p-2 rounded-xl border border-gray-200">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div class="mt-3 grid grid-cols-2 gap-3">
        <div>
          <label class="text-xs text-gray-500">Date début</label>
          <input type="date" id="rangeStart" class="w-full border border-gray-200 rounded-xl px-3 py-2" />
        </div>
        <div>
          <label class="text-xs text-gray-500">Date fin</label>
          <input type="date" id="rangeEnd" class="w-full border border-gray-200 rounded-xl px-3 py-2" />
        </div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button id="btnApplyRange" class="px-4 py-2 rounded-xl bg-primary-600 text-white">Appliquer</button>
      </div>
    </div>
  </div>

  <script>
    /* ---------------------- MOCK DATA (statistiques) ---------------------- */
    const MOCK = {
      month: '2025-08',
      tableaux: [
        {
          id: 1, name: 'Famille', prevu: 500, reel: 420,
          variables: [
            {
              id: 11, name: 'Alimentation', prevu: 300, reel: 250,
              sous: [
                { id: 111, name: 'Déjeuner', prevu: 100, reel: 90 },
                { id: 112, name: 'Dîner', prevu: 200, reel: 160 },
              ]
            },
            {
              id: 12, name: 'Transport', prevu: 200, reel: 170,
              sous: [
                { id: 121, name: 'Bus', prevu: 100, reel: 80 },
                { id: 122, name: 'Taxi', prevu: 100, reel: 90 },
              ]
            }
          ]
        },
        {
          id: 2, name: 'Logement', prevu: 300, reel: 300,
          variables: [
            { id: 21, name: 'Loyer', prevu: 250, reel: 250, sous: [] },
            { id: 22, name: 'Électricité', prevu: 50, reel: 50, sous: [] },
          ]
        },
        {
          id: 3, name: 'Perso', prevu: 200, reel: 120,
          variables: [
            { id: 31, name: 'Internet', prevu: 30, reel: 30, sous: [] },
            { id: 32, name: 'Loisirs', prevu: 170, reel: 90, sous: [] },
          ]
        }
      ],
      operations: [
        { date: '2025-08-27', tableau: 'Famille', variable: 'Alimentation', sous: 'Déjeuner', montant: 12.50, client: 'Payeur' },
        { date: '2025-08-26', tableau: 'Famille', variable: 'Transport', sous: 'Taxi', montant: 8.00, client: 'Payeur' },
        { date: '2025-08-24', tableau: 'Logement', variable: 'Loyer', sous: '-', montant: 250.00, client: 'Bailleur' },
        { date: '2025-08-22', tableau: 'Perso', variable: 'Loisirs', sous: '-', montant: 45.00, client: 'Payeur' },
        { date: '2025-08-20', tableau: 'Famille', variable: 'Alimentation', sous: 'Dîner', montant: 20.00, client: 'Payeur' },
      ]
    };

    /* ---------------------- UTILITAIRES ---------------------- */
    const fmtMoney = (n) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(n);
    const sum = (arr, path) => arr.reduce((acc, o) => acc + (path.split('.').reduce((v, k) => v?.[k], o) ?? 0), 0);

    /* ---------------------- INITIALISATION UI ---------------------- */
    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();

      // Sidebar open/close (mobile)
      const sidebar = document.getElementById('sidebar');
      document.getElementById('btnOpenSidebar')?.addEventListener('click', () => sidebar.style.transform = 'translateX(0)');
      document.getElementById('btnCloseSidebar')?.addEventListener('click', () => sidebar.style.transform = 'translateX(-100%)');

      // Range modal
      const modal = document.getElementById('modalRange');
      document.getElementById('btnCustomRange').addEventListener('click', () => modal.classList.remove('hidden'));
      document.getElementById('btnCloseRange').addEventListener('click', () => modal.classList.add('hidden'));
      document.getElementById('btnApplyRange').addEventListener('click', () => {
        // Ici tu brancheras l'appel API pour filtrer les stats
        modal.classList.add('hidden');
      });

      // Inject KPIs
      const totalPrevu = sum(MOCK.tableaux, 'prevu');
      const totalReel  = sum(MOCK.tableaux, 'reel');
      const nbOps      = MOCK.operations.length;
      document.getElementById('kpiBudgetPrevus').textContent = fmtMoney(totalPrevu);
      document.getElementById('kpiDepensesReelles').textContent = fmtMoney(totalReel);
      const solde = totalPrevu - totalReel;
      const soldeEl = document.getElementById('kpiSolde');
      soldeEl.textContent = fmtMoney(solde);
      const soldeNote = document.getElementById('kpiSoldeNote');
      if (solde >= 0) {
        soldeNote.textContent = '✅ Sous le budget';
        soldeEl.classList.add('text-green-600');
      } else {
        soldeNote.textContent = '⚠️ Dépassement';
        soldeEl.classList.add('text-red-600');
      }
      document.getElementById('kpiNbOperations').textContent = nbOps.toString();

      // Alerts (dépassements)
      const alertBox = document.getElementById('alertList');
      const addAlert = (label, prevu, reel) => {
        const diff = reel - prevu;
        const over = diff > 0;
        const el = document.createElement('div');
        el.className = `rounded-xl border p-3 ${over ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50'}`;
        el.innerHTML = `
          <div class="flex items-start justify-between">
            <div>
              <div class="font-medium">${label}</div>
              <div class="text-xs text-gray-600">Prévu: ${fmtMoney(prevu)} • Réel: ${fmtMoney(reel)}</div>
            </div>
            <span class="text-xs ${over ? 'text-red-600' : 'text-emerald-600'}">${over ? '+' : ''}${fmtMoney(diff)}</span>
          </div>`;
        alertBox.appendChild(el);
      };
      // Parcours tous les tableaux + variables pour générer des alertes
      MOCK.tableaux.forEach(t => {
        addAlert(`Tableau — ${t.name}`, t.prevu, t.reel);
        t.variables.forEach(v => addAlert(`Variable — ${v.name}`, v.prevu, v.reel));
      });

      // Recent operations table
      const tbody = document.getElementById('recentOpsTbody');
      MOCK.operations.forEach(op => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="py-2 pr-4">${op.date}</td>
          <td class="py-2 pr-4">${op.tableau}</td>
          <td class="py-2 pr-4">${op.variable}</td>
          <td class="py-2 pr-4">${op.sous}</td>
          <td class="py-2 pr-4 font-medium">${fmtMoney(op.montant)}</td>
          <td class="py-2 pr-4">${op.client}</td>
        `;
        tbody.appendChild(tr);
      });

      // Sidebar Tree (Structure with indicators)
      const treeRoot = document.getElementById('tree');
      const makeBadge = (prevu, reel) => {
        const over = reel > prevu;
        return `<span class="ml-auto text-[10px] px-1.5 py-0.5 rounded-lg ${over ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'}">${fmtMoney(reel)} / ${fmtMoney(prevu)}</span>`
      };
      const renderTree = () => {
        treeRoot.innerHTML = '';
        MOCK.tableaux.forEach(t => {
          const tWrap = document.createElement('div');
          tWrap.className = 'rounded-xl border border-gray-200';
          const tHeader = document.createElement('button');
          tHeader.className = 'w-full flex items-center gap-2 px-3 py-2 hover:bg-gray-50';
          tHeader.innerHTML = `
            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-500 transition-transform"></i>
            <i data-lucide="folder" class="w-4 h-4 text-primary-600"></i>
            <span class="font-medium">${t.name}</span>
            ${makeBadge(t.prevu, t.reel)}
          `;
          const tBody = document.createElement('div');
          tBody.className = 'hidden pl-6 pb-2';

          t.variables.forEach(v => {
            const vWrap = document.createElement('div');
            const vHeader = document.createElement('button');
            vHeader.className = 'w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 rounded-lg';
            vHeader.innerHTML = `
              <i data-lucide="chevron-right" class="w-4 h-4 text-gray-500 transition-transform"></i>
              <i data-lucide="layers" class="w-4 h-4 text-primary-600"></i>
              <span>${v.name}</span>
              ${makeBadge(v.prevu, v.reel)}
            `;
            const vBody = document.createElement('div');
            vBody.className = 'hidden pl-6';

            if (v.sous?.length) {
              v.sous.forEach(s => {
                const sRow = document.createElement('div');
                sRow.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-50';
                sRow.innerHTML = `
                  <i data-lucide="dot" class="w-4 h-4 text-gray-400"></i>
                  <span>${s.name}</span>
                  ${makeBadge(s.prevu, s.reel)}
                `;
                vBody.appendChild(sRow);
              });
            } else {
              const empty = document.createElement('div');
              empty.className = 'text-xs text-gray-400 px-3 py-1.5';
              empty.textContent = 'Aucune sous-variable';
              vBody.appendChild(empty);
            }

            // Toggle variable
            vHeader.addEventListener('click', () => {
              const icon = vHeader.querySelector('[data-lucide="chevron-right"]');
              vBody.classList.toggle('hidden');
              icon.style.transform = vBody.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
            });

            vWrap.appendChild(vHeader);
            vWrap.appendChild(vBody);
            tBody.appendChild(vWrap);
          });

          // Toggle tableau
          tHeader.addEventListener('click', () => {
            const icon = tHeader.querySelector('[data-lucide="chevron-right"]');
            tBody.classList.toggle('hidden');
            icon.style.transform = tBody.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
          });

          tWrap.appendChild(tHeader);
          tWrap.appendChild(tBody);
          treeRoot.appendChild(tWrap);
        });
        lucide.createIcons(); // re-render icons after dynamic HTML
      };
      renderTree();

      // Charts
      const ctxDonut = document.getElementById('chartDonut');
      const donutLabels = MOCK.tableaux.map(t => t.name);
      const donutData = MOCK.tableaux.map(t => t.reel);
      new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
          labels: donutLabels,
          datasets: [{ data: donutData }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${fmtMoney(ctx.parsed)}` } }
          },
          cutout: '60%'
        }
      });

      const ctxBars = document.getElementById('chartBars');
      // Exemple simple: 4 semaines
      const barLabels = ['S1', 'S2', 'S3', 'S4'];
      const barData = [120, 180, 150, 200];
      new Chart(ctxBars, {
        type: 'bar',
        data: {
          labels: barLabels,
          datasets: [{ label: 'Dépenses', data: barData }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: (ctx) => `${fmtMoney(ctx.parsed.y)}` } }
          },
          scales: {
            y: { ticks: { callback: (v) => fmtMoney(v) } }
          }
        }
      });
    });
  </script>
</body>
</html>
