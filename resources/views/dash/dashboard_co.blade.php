@extends('dash.layouts.app')
{{-- Démo : jeu de transactions factices --}}
@php
    // si Carbon n'est pas déjà importé
    use Carbon\Carbon;

    $transactions = collect([
        (object)[
            'date'        => Carbon::parse('2025-08-29'),
            'category'    => 'Salaire',
            'description' => 'Versement salaire Août',
            'amount'      => 2000.00,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-28'),
            'category'    => 'Alimentation',
            'description' => 'Courses supermarché',
            'amount'      => -68.40,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-27'),
            'category'    => 'Transport',
            'description' => 'Ticket de métro',
            'amount'      => -2.20,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-26'),
            'category'    => 'Abonnement',
            'description' => 'Netflix',
            'amount'      => -12.99,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-25'),
            'category'    => 'Facture',
            'description' => 'EDF',
            'amount'      => -75.80,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-24'),
            'category'    => 'Divertissement',
            'description' => 'Cinéma',
            'amount'      => -13.50,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-23'),
            'category'    => 'Restaurant',
            'description' => 'Chez Luigi',
            'amount'      => -45.00,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-22'),
            'category'    => 'Santé',
            'description' => 'Pharmacie',
            'amount'      => -20.75,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-21'),
            'category'    => 'Remboursement',
            'description' => 'Cadeau reçu',
            'amount'      => 150.00,
        ],
        (object)[
            'date'        => Carbon::parse('2025-08-20'),
            'category'    => 'Virement',
            'description' => 'Épargne mensuelle',
            'amount'      => 500.00,
        ],
    ]);
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">

  {{-- Sidebar --}}
  <aside class="col-span-3 bg-white rounded-lg shadow p-4 sticky top-24">
    <h2 class="font-semibold mb-4">Filtres</h2>
    <ul class="space-y-2">
      <li><a href="#" class="block px-3 py-1 rounded hover:bg-gray-100">Tous les comptes</a></li>
      <li><a href="#" class="block px-3 py-1 rounded hover:bg-gray-100">Cartes</a></li>
      <li><a href="#" class="block px-3 py-1 rounded hover:bg-gray-100">Comptes épargne</a></li>
      <li><a href="#" class="block px-3 py-1 rounded hover:bg-gray-100">Crédits</a></li>
    </ul>
  </aside>

  {{-- Contenu principal --}}
  <section class="col-span-9 space-y-6">

    <!-- Row 1 : KPI synthétiques -->
    <div class="grid grid-cols-4 gap-4">
      @php
        $cards = [
          ['title'=>'Solde total','value'=>'4 320 €','delta'=>'+2,4%','color'=>'green'],
          ['title'=>'Revenus du mois','value'=>'2 150 €','delta'=>'+10%','color'=>'green'],
          ['title'=>'Dépenses du mois','value'=>'1 120 €','delta'=>'-5%','color'=>'red'],
          ['title'=>'Épargne mensuelle','value'=>'500 €','delta'=>'+8%','color'=>'green'],
        ];
      @endphp

      @foreach($cards as $c)
      <div class="bg-white p-4 rounded-lg shadow flex justify-between items-center">
        <div>
          <h3 class="text-gray-600">{{ $c['title'] }}</h3>
          <p class="text-2xl font-semibold">{{ $c['value'] }}</p>
        </div>
        <div class="text-{{ $c['color'] }}-500 font-medium">
          {{ $c['delta'] }}
        </div>
      </div>
      @endforeach
    </div>

    <!-- Row 2 : Graphiques principaux -->
    <div class="grid grid-cols-2 gap-4">
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="mb-2 font-semibold">Évolution du solde</h4>
        <canvas id="balanceChart"></canvas>
      </div>
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="mb-2 font-semibold">Dépenses par catégorie</h4>
        <canvas id="expenseChart"></canvas>
      </div>
    </div>

    <!-- Row 3 : Tableau des transactions -->
    
    <div class="bg-white p-4 rounded-lg shadow">
      <h4 class="mb-4 font-semibold">Transactions récentes</h4>
      <div class="overflow-x-auto">
        <table class="min-w-full text-left">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2">Date</th>
              <th class="px-3 py-2">Catégorie</th>
              <th class="px-3 py-2">Description</th>
              <th class="px-3 py-2">Montant</th>
              <th class="px-3 py-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transactions as $tx)
            <tr class="border-b">
              <td class="px-3 py-2">{{ $tx->date->format('d/m/Y') }}</td>
              <td class="px-3 py-2">{{ $tx->category }}</td>
              <td class="px-3 py-2">{{ $tx->description }}</td>
              <td class="px-3 py-2 {{ $tx->amount < 0 ? 'text-red-500' : 'text-green-600' }}">
                {{ number_format($tx->amount, 2, ',', ' ') }} €
              </td>
              <td class="px-3 py-2 space-x-2">
                <button class="text-blue-500 hover:underline">Éditer</button>
                <button class="text-red-500 hover:underline">Suppr.</button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Row 4 : Objectifs & Alertes -->
    <div class="grid grid-cols-2 gap-4">
      {{-- Objectifs --}}
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="mb-4 font-semibold">Objectifs</h4>
        <div class="space-y-4">
          @php
            $goals = [
              ['label'=>'Vacances','current'=>800,'target'=>1000],
              ['label'=>'Remboursement crédit','current'=>150,'target'=>500],
            ];
          @endphp

          @foreach($goals as $g)
          @php $pct = round($g['current'] / $g['target'] * 100) @endphp
          <div>
            <div class="flex justify-between mb-1">
              <span>{{ $g['label'] }}</span>
              <span>{{ $pct }}%</span>
            </div>
            <div class="w-full bg-gray-200 h-2 rounded">
              <div class="bg-blue-600 h-2 rounded" style="width: {{ $pct }}%"></div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
      {{-- Alertes --}}
      <div class="bg-white p-4 rounded-lg shadow">
        <h4 class="mb-4 font-semibold">Alertes</h4>
        <ul class="space-y-2">
          <li class="flex items-center space-x-2">
            <span class="text-yellow-500">●</span>
            <span>Dépassement de budget sur « Alimentation »</span>
          </li>
          <li class="flex items-center space-x-2">
            <span class="text-blue-500">●</span>
            <span>Facture EDF à régler dans 3 jours</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Row 5 : Widgets personnalisables -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="flex justify-between items-center mb-4">
        <h4 class="font-semibold">Vos widgets</h4>
        <button id="addWidgetBtn" class="px-3 py-1 bg-indigo-600 text-white rounded">+ Ajouter un widget</button>
      </div>
      <div id="widgetsContainer" class="grid grid-cols-3 gap-4">
        <div class="bg-gray-50 p-4 rounded-lg">Prévision trésorerie 30 j.</div>
        <div class="bg-gray-50 p-4 rounded-lg">Flux par fournisseur</div>
        <div class="bg-gray-50 p-4 rounded-lg">Stats épargne</div>
      </div>

      <!-- Modal de sélection -->
      <div id="widgetModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg w-2/5">
          <h5 class="font-semibold mb-4">Ajouter un widget</h5>
          <ul class="space-y-2 mb-4">
            @php
              $allWidgets = [
                'Prévision trésorerie 30 j.',
                'Flux de dépenses',
                'Statistiques épargne',
                'Conseils personnalisés'
              ];
            @endphp
            @foreach($allWidgets as $w)
            <li>
              <label class="flex items-center">
                <input type="checkbox" class="mr-2" data-widget="{{ $w }}">
                <span>{{ $w }}</span>
              </label>
            </li>
            @endforeach
          </ul>
          <div class="flex justify-end space-x-2">
            <button id="cancelWidget" class="px-3 py-1 bg-gray-300 rounded">Annuler</button>
            <button id="saveWidget" class="px-3 py-1 bg-indigo-600 text-white rounded">Enregistrer</button>
          </div>
        </div>
      </div>
    </div>

  </section>
</div>

{{-- Scripts Chart.js & interactions --}}
<script>
  // Modal widgets
  const modal = document.getElementById('widgetModal');
  document.getElementById('addWidgetBtn').onclick = () => modal.classList.remove('hidden');
  document.getElementById('cancelWidget').onclick = () => modal.classList.add('hidden');

  document.getElementById('saveWidget').onclick = () => {
    document.querySelectorAll('#widgetModal input[type=checkbox]').forEach(cb => {
      if(cb.checked){
        const name = cb.dataset.widget;
        const el = document.createElement('div');
        el.className = 'bg-gray-50 p-4 rounded-lg';
        el.textContent = name;
        document.getElementById('widgetsContainer').append(el);
      }
    });
    modal.classList.add('hidden');
  };

  // Sample data pour les graphiques
  const labels = Array.from({length: 10}, (_,i)=> `J-${9-i}`);
  const balanceData = [3200, 3350, 3420, 3280, 4100, 4300, 4200, 4400, 4320, 4500];
  const expenseData = [200, 150, 300, 250, 100, 400, 180, 230, 210, 190];

  // Chart balance
  new Chart(document.getElementById('balanceChart'), {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Solde (€)',
        data: balanceData,
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59,130,246,0.2)',
        tension: 0.3
      }]
    },
    options: { responsive: true, plugins:{ legend:{display:false}} }
  });

  // Chart dépenses
  new Chart(document.getElementById('expenseChart'), {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Dépenses (€)',
        data: expenseData,
        backgroundColor: '#EF4444'
      }]
    },
    options: { responsive: true, plugins:{ legend:{display:false}} }
  });
</script>
@endsection
