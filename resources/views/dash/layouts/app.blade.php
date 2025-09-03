<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mon Dashboard</title>
  <!-- Tailwind CSS via CDN pour prototypage -->
  {{-- <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet"> --}}
    <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Shepherd.js (tour guidé) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@13.1.1/dist/css/shepherd.css">
  <!-- Shepherd JS -->
  <script src="https://cdn.jsdelivr.net/npm/shepherd.js/dist/js/shepherd.min.js"></script>
  <!-- Chart.js -->
  {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script> --}}
  
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
  <!-- Barre de navigation -->
  <nav class="bg-white shadow fixed w-full z-10">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
      <div class="flex items-center space-x-4">
        <img src="/logo.png" alt="Logo" class="h-8 w-8">
        <span class="text-xl font-semibold">FinanceApp</span>
      </div>
      <div class="flex items-center space-x-3">
        <select id="periodSelect" class="border border-gray-300 rounded px-2 py-1">
          <option>Jour</option>
          <option>Semaine</option>
          <option selected>Mois</option>
          <option>Personnalisé</option>
        </select>
        <button class="bg-blue-600 text-white px-3 py-1 rounded">+ Transaction</button>
        <button class="bg-green-600 text-white px-3 py-1 rounded">+ Objectif</button>
        <a href="#" class="text-gray-600 hover:text-black">Mon compte</a>
      </div>
    </div>
  </nav>

  <!-- Contenu -->
  <main class="pt-20 max-w-7xl mx-auto px-4">
    @yield('content')
  </main>
  
  <!-- JS personnalisé (optionnel si compilé via Laravel Mix/Vite) -->
  <script src="{{ asset('js/app.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
