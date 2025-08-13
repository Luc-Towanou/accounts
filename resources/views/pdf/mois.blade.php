<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Mois Comptable - {{ $mois->nom }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif; /* Compatible PDF */
            font-size: 12px;
            color: #333;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        h2 {
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 5px;
            color: #444;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #999;
            padding: 6px;
        }
        table th {
            background: #f2f2f2;
        }
        .sub-variable {
            padding-left: 20px;
            font-style: italic;
            background: #fafafa;
        }
        .total-row {
            background: #e8f4ea;
            font-weight: bold;
        }
        .small {
            font-size: 10px;
            color: #666;
        }
        .text-red {
            color: red;
            font-weight: bold;
        }
        .text-green {
            color: green;
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <h1>{{ $mois->nom }} - {{ $mois->annee }}</h1>
        <p class="small">Export généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        $totalDepensesMois = 0;
        $totalGainsMois = 0; // à adapter si tu as une méthode pour les gains
    @endphp

    <!-- Tableaux -->
    @foreach($mois->tableaux as $tableau)
        <h2>{{ $tableau->nom }}</h2>

        <table>
            <thead>
                <tr>
                    <th>Variable</th>
                    <th>Budget prévu (€)</th>
                    <th>Dépense réelle (€)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPrevu = 0;
                    $totalReel = 0;
                @endphp

                @foreach($tableau->variables as $var)
                    @php
                        $totalPrevu += $var->budget_prevu;
                        $totalReel += $var->depense_reelle;
                        $totalDepensesMois += $var->depense_reelle;
                    @endphp
                    <tr>
                        <td>{{ $var->nom }}</td>
                        <td class="{{ $var->budget_prevu > $var->depense_reelle ? 'text-green' : '' }}">
                            {{ number_format($var->budget_prevu, 2, ',', ' ') }}
                        </td>
                        <td class="{{ $var->depense_reelle > $var->budget_prevu ? 'text-red' : '' }}">
                            {{ number_format($var->depense_reelle, 2, ',', ' ') }}
                        </td>
                    </tr>

                    @foreach($var->sousVariables as $sous)
                        @php
                            $totalPrevu += $sous->budget_prevu;
                            $totalReel += $sous->depense_reelle;
                            $totalDepensesMois += $sous->depense_reelle;
                        @endphp
                        <tr class="sub-variable">
                            <td>— {{ $sous->nom }}</td>
                            <td class="{{ $sous->budget_prevu > $sous->depense_reelle ? 'text-green' : '' }}">
                                {{ number_format($sous->budget_prevu, 2, ',', ' ') }}
                            </td>
                            <td class="{{ $sous->depense_reelle > $sous->budget_prevu ? 'text-red' : '' }}">
                                {{ number_format($sous->depense_reelle, 2, ',', ' ') }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach

                <!-- Ligne total tableau -->
                <tr class="total-row">
                    <td>Total {{ $tableau->nom }}</td>
                    <td>{{ number_format($totalPrevu, 2, ',', ' ') }}</td>
                    <td>{{ number_format($totalReel, 2, ',', ' ') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <!-- Footer avec totaux mois -->
    @php
        // Exemple fictif de gains
        $totalGainsMois = $mois->gains_reels ?? 0; 
        $resultatNet = $totalGainsMois - $totalDepensesMois;
    @endphp
    <div class="footer">
        <h1>{{ $mois->nom }} - {{ $mois->annee }}</h1>
        <p>Total dépenses réelles : <strong>{{ number_format($totalDepensesMois, 2, ',', ' ') }} €</strong></p>
        <p>Total gains réels : <strong>{{ number_format($totalGainsMois, 2, ',', ' ') }} €</strong></p>
        <p>Résultat net :
            <strong class="{{ $resultatNet >= 0 ? 'text-green' : 'text-red' }}">
                {{ number_format($resultatNet, 2, ',', ' ') }} €
            </strong>
        </p>
    </div>

</body>
</html>
