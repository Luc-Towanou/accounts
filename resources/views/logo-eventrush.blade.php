<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logo App Événements</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e91e63 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            margin: 20px;
        }
        
        header {
            background: #E91E63;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .logo-section {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            align-items: center;
            padding: 40px 20px;
        }
        
        .logo-container {
            text-align: center;
            margin: 20px;
        }
        
        .logo {
            width: 200px;
            height: 200px;
            display: inline-block;
            position: relative;
        }
        
        .logo-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(233, 30, 99, 0.1);
            border-radius: 50%;
        }
        
        .heart {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 60%;
        }
        
        .circle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            border: 1px solid rgba(233, 30, 99, 0.3);
        }
        
        .circle-1 {
            width: 90%;
            height: 90%;
        }
        
        .circle-2 {
            width: 75%;
            height: 75%;
            border-width: 1.5px;
            border-color: rgba(233, 30, 99, 0.5);
        }
        
        .star {
            position: absolute;
            color: #FFC107;
        }
        
        .star-1 {
            top: 15%;
            right: 20%;
            font-size: 20px;
        }
        
        .star-2 {
            bottom: 15%;
            left: 20%;
            font-size: 20px;
        }
        
        .star-3 {
            top: 30%;
            left: 15%;
            font-size: 16px;
        }
        
        .star-4 {
            bottom: 30%;
            right: 15%;
            font-size: 16px;
        }
        
        .code-section {
            background: #f8f9fa;
            padding: 30px;
            border-top: 1px solid #e9ecef;
            font-family: 'Courier New', monospace;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        h2 {
            font-size: 1.8rem;
            margin: 30px 0 15px;
            color: #E91E63;
        }
        
        p {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        pre {
            background: #2d2d2d;
            color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .instructions {
            padding: 30px;
        }
        
        .variations {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin: 20px 0;
        }
        
        .variation {
            text-align: center;
            margin: 15px;
        }
        
        .color-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            background: #2d2d2d;
            color: white;
        }
        
        @media (max-width: 768px) {
            .logo-section {
                flex-direction: column;
            }
            
            .variations {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Logo App Événements</h1>
            <p>Votre application de gestion d'événements</p>
        </header>
{{--         
        <div class="logo-section">
            <div class="logo-container">
                <div class="logo"> --}}
        <div>
            <div>
                <div>
                    <!-- Cœur SVG -->
                    {{-- <svg class="heart" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <path d="M50 88.5L43.5 82.5C36.5 76 17 59 17 40.5C17 27.5 27.5 17 40.5 17C45.5 17 50.5 19 54 22.5L50 26.5L46 22.5C49.5 19 54.5 17 59.5 17C72.5 17 83 27.5 83 40.5C83 59 63.5 76 56.5 82.5L50 88.5Z" fill="#E91E63"/>
                        <path d="M59.5 13C54.5 13 50 15 46.5 18.5L50 22L53.5 18.5C50 15 45.5 13 40.5 13C25 13 13 25 13 40.5C13 61 35 80 50 92C65 80 87 61 87 40.5C87 25 75 13 59.5 13Z" stroke="#E91E63" stroke-width="2" fill="none"/>
                    </svg> --}}
                    {{-- <svg width="200" height="200" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <path d="M100 20 C60 20, 30 50, 30 100 C30 150, 60 180, 100 180 C140 180, 170 150, 170 100 C170 50, 140 20, 100 20 Z" fill="none" stroke="#e91e63" stroke-width="10"/>
                        <path d="M70 50 C85 40, 115 40, 130 50 C145 60, 150 85, 140 100 C130 115, 100 125, 85 120 C70 115, 60 100, 65 85 C70 70, 90 60, 100 65 C110 70, 120 85, 115 100 C110 115, 90 125, 75 120 C60 115, 50 100, 55 85 C60 70, 80 60, 90 65 C100 70, 110 85, 105 100 C100 115, 80 125, 65 120 C50 115, 40 100, 45 85 C50 70, 70 60, 80 65 C90 70, 100 85, 95 100 C90 115, 70 125, 55 120 C40 115, 30 100, 35 85 C40 70, 60 60, 70 65 C80 70, 90 85, 85 100 C80 115, 60 125, 45 120 C30 115, 20 100, 25 85 C30 70, 50 60, 60 65 C70 70, 75 85, 70 100" fill="none" stroke="#e91e63" stroke-width="8" stroke-linecap="round"/>
                        <text x="100" y="170" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#e91e63" font-weight="bold">EventRush</text>
                    </svg> --}}
                    
                    <svg xmlns="http://www.w3.org/2000/svg" width="512" height="512" viewBox="0 0 512 512" role="img" aria-labelledby="title desc">
  <title id="title">EventRush — emblème dégradé avec icône central composite</title>
  <desc id="desc">Cercle à dégradé rose-violet-bleu, icône central combinant foule stylisée, pictogramme d'événement et ticket avec éclair en #FF6F00, et mot-symbole EventRush en dessous.</desc>

  <defs>
    <!-- Couleurs principales -->
    <style>
      :root {
        --icon: #FF6F00;     /* Couleur des icônes demandée */
        --text: #4B006E;     /* Violet foncé du mot-symbole */
      }
    </style>

    <!-- Dégradé de fond de l'emblème -->
    <radialGradient id="bgGrad" cx="50%" cy="40%" r="70%">
      <stop offset="0%"  stop-color="#FF4FA3"/>
      <stop offset="55%" stop-color="#A23BFF"/>
      <stop offset="100%" stop-color="#3BB4FF"/>
    </radialGradient>

    <!-- Symbole ticket avec encoches -->
    <symbol id="icon-ticket" viewBox="0 0 100 64">
      <!-- Corps du ticket -->
      <path d="M8 8 h64 a8 8 0 0 1 8 8 v6
               a10 10 0 0 0 0 20 v6 a8 8 0 0 1-8 8 H8
               a8 8 0 0 1-8-8 v-6 a10 10 0 0 0 0-20 v-6
               a8 8 0 0 1 8-8 z" fill="var(--icon)"/>
      <!-- Éclair au centre -->
      <path d="M56 12 L34 36 h14 l-8 16 26-26 H48 z"
            fill="#FFFFFF" opacity="0.95"/>
    </symbol>

    <!-- Symbole calendrier/événement avec étoile -->
    <symbol id="icon-event" viewBox="0 0 96 96">
      <!-- Calendrier -->
      <rect x="8" y="18" width="80" height="70" rx="10" fill="var(--icon)"/>
      <rect x="8" y="18" width="80" height="18" rx="8" fill="#FFFFFF" opacity="0.1"/>
      <!-- Anneaux -->
      <rect x="22" y="12" width="10" height="16" rx="4" fill="var(--icon)"/>
      <rect x="64" y="12" width="10" height="16" rx="4" fill="var(--icon)"/>
      <!-- Étoile au centre -->
      <path d="M48 39 l7 13 15 2 -11 10 3 15 -14-7 -14 7 3-15 -11-10 15-2 z"
            fill="#FFFFFF" opacity="0.95"/>
    </symbol>
  </defs>

  <!-- Emblème circulaire -->
  <g id="emblem" transform="translate(256,216)">
    <circle r="180" fill="url(#bgGrad)"/>

    <!-- Icône central composite -->
    <g id="composite" fill="var(--icon)" stroke="none">
      <!-- Foule stylisée (figure centrale + deux petites) -->
      <!-- Têtes -->
      <circle cx="0" cy="-30" r="22" fill="var(--icon)"/>
      <circle cx="-58" cy="6" r="14" fill="var(--icon)"/>
      <circle cx="58"  cy="6" r="14" fill="var(--icon)"/>

      <!-- Corps central (torse) -->
      <path d="M-30,0
               C-30,-18 -14,-32 0,-32
               C14,-32 30,-18 30,0
               L30,48
               C30,62 18,74 4,74
               L-4,74
               C-18,74 -30,62 -30,48
               Z" fill="var(--icon)"/>

      <!-- Bras levés en V (central) -->
      <path d="M-6,-12 C-34,-28 -66,-36 -90,-30
               L-98,-44 C-70,-56 -34,-48 -2,-22 Z" fill="var(--icon)"/>
      <path d="M6,-12 C34,-28 66,-36 90,-30
               L98,-44 C70,-56 34,-48 2,-22 Z" fill="var(--icon)"/>

      <!-- Petit bras gauche levé -->
      <path d="M-62,18 c-16,-18 -30,-22 -44,-24 l-6,-10 c20,-6 36,0 52,18 z" fill="var(--icon)"/>

      <!-- Petit bras droit tenant un ticket -->
      <path d="M62,18 c16,-18 30,-22 44,-24 l6,-10 c-20,-6 -36,0 -52,18 z" fill="var(--icon)"/>

      <!-- Ticket incliné au-dessus de la petite figure droite -->
      <g transform="translate(108,-10) rotate(-18)">
        <use href="#icon-ticket" width="70" height="45" x="-35" y="-22.5"/>
      </g>

      <!-- Pictogramme événement au-dessus de la tête centrale -->
      <g transform="translate(0,-98) scale(0.6)">
        <use href="#icon-event" x="-48" y="-48" width="96" height="96"/>
      </g>
    </g>
  </g>

  <!-- Mot-symbole -->
  <g id="wordmark" transform="translate(256,446)">
    <text x="0" y="0" text-anchor="middle"
          fill="var(--text)"
          font-size="64"
          font-weight="700"
          font-family="Pacifico, 'Brush Script MT', 'Segoe Script', cursive">
      EventRush
    </text>
  </g>
</svg>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <!-- Dégradé -->
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#7F00FF;stop-opacity:1" />
      <stop offset="50%" style="stop-color:#E100FF;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#FF6F00;stop-opacity:1" />
    </linearGradient>
  </defs>
  
  <!-- Cercle principal -->
  <circle cx="100" cy="100" r="80" fill="url(#grad)" />
  
  <!-- Pin map stylisé -->
  <path d="M100 45 C130 45 145 70 145 95 C145 125 100 165 100 165 C100 165 55 125 55 95 C55 70 70 45 100 45 Z" 
        fill="white"/>
  <circle cx="100" cy="95" r="15" fill="url(#grad)" />
  
  <!-- Éclats autour (confettis / feu d’artifice) -->
  <line x1="100" y1="15" x2="100" y2="30" stroke="white" stroke-width="4" stroke-linecap="round"/>
  <line x1="170" y1="100" x2="155" y2="100" stroke="white" stroke-width="4" stroke-linecap="round"/>
  <line x1="100" y1="185" x2="100" y2="170" stroke="white" stroke-width="4" stroke-linecap="round"/>
  <line x1="30" y1="100" x2="45" y2="100" stroke="white" stroke-width="4" stroke-linecap="round"/>
  
  <!-- Confettis -->
  <circle cx="150" cy="60" r="6" fill="white"/>
  <circle cx="60" cy="150" r="6" fill="white"/>
  <circle cx="65" cy="55" r="4" fill="white"/>
  <circle cx="145" cy="145" r="4" fill="white"/>
</svg>

                        <svg width="512" height="512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Logo portefeuille finance avec sourire-flèche">
                            <!-- Palette -->
                            <!-- Bleu clair (fond): #E8F4FF | Bleu foncé (contours): #0F3555 -->
                            <!-- Vert (portefeuille): #35C38B | Jaune (feuille): #FFC44D | Violet (feuille): #7C5CFF -->

                            <!-- Fond arrondi -->
                            <rect x="16" y="16" width="480" height="480" rx="96" fill="#E8F4FF"/>

                            <!-- Ombre légère du portefeuille -->
                            <ellipse cx="256" cy="392" rx="160" ry="26" fill="#0F3555" opacity="0.06"/>

                            <!-- Poche intérieure (lèvre supérieure du portefeuille) -->
                            <rect x="108" y="180" width="296" height="42" rx="18" fill="#0F3555" opacity="0.14"/>

                            <!-- Feuilles à l'intérieur -->
                            <!-- Feuille jaune -->
                            <rect x="148" y="130" width="132" height="88" rx="12" fill="#FFC44D" stroke="#0F3555" stroke-width="12"/>
                            <!-- Feuille violette -->
                            <rect x="252" y="150" width="120" height="78" rx="12" fill="#7C5CFF" stroke="#0F3555" stroke-width="12"/>

                            <!-- Corps du portefeuille -->
                            <rect x="100" y="200" width="312" height="184" rx="28" fill="#35C38B" stroke="#0F3555" stroke-width="16"/>

                            <!-- Rabat/fermoir -->
                            <g>
                                <rect x="348" y="244" width="84" height="96" rx="20" fill="#35C38B" stroke="#0F3555" stroke-width="16"/>
                                <circle cx="388" cy="292" r="14" fill="#FFC44D" stroke="#0F3555" stroke-width="12"/>
                            </g>

                            <!-- Yeux -->
                            <circle cx="196" cy="288" r="10" fill="#0F3555"/>
                            <circle cx="276" cy="288" r="10" fill="#0F3555"/>

                            <!-- Sourire qui se prolonge en flèche (chemin continu) -->
                            <!-- Le sourire forme un arc, puis se relève et devient une flèche ascendante -->
                            <path d="M170 322
                                    C 200 352, 244 352, 274 322
                                    S 330 262, 360 238
                                    L 410 188"
                                    fill="none" stroke="#0F3555" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/>

                            <!-- Pointe de flèche (séparée mais visuellement continue) -->
                            <path d="M410 188 L 392 196 M410 188 L 402 206" stroke="#0F3555" stroke-width="16" stroke-linecap="round"/>

                            <!-- Liseré haut (ouverture du portefeuille) -->
                            <path d="M100 220 H412" stroke="#0F3555" stroke-width="16" stroke-linecap="round"/>

                            </svg>

                            <svg width="512" height="512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Logo EventRush avec icône de localisation centrale">
  <defs>
    <!-- Dégradé violet-rose-orange avec dominance rose -->
    <linearGradient id="bg-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#7F00FF" />
      <stop offset="40%" stop-color="#E100FF" />
      <stop offset="70%" stop-color="#FF6F00" />
    </linearGradient>
    
    <!-- Masque pour les coins arrondis -->
    <mask id="rounded-corners">
      <rect width="512" height="512" rx="80" fill="white"/>
    </mask>
    
    <!-- Style pour les icônes -->
    <style>
      .icon { fill: #FF6F00; }
    </style>
    
    <!-- Icône de localisation (grande, au centre) -->
    <g id="location-icon">
      <circle cx="256" cy="256" r="80" class="icon"/>
      <circle cx="256" cy="256" r="50" fill="white"/>
      <circle cx="256" cy="256" r="20" class="icon"/>
    </g>
    
    <!-- Icône d'événement (petite, à gauche) -->
    <g id="event-icon">
      <rect x="80" y="120" width="70" height="70" rx="15" class="icon"/>
      <line x1="80" y1="150" x2="150" y2="150" stroke="white" stroke-width="8"/>
      <rect x="95" y="170" width="40" height="30" rx="5" fill="white"/>
    </g>
    
    <!-- Icône de ticket (petite, à droite) -->
    <g id="ticket-icon">
      <path d="M380,120 L380,190 L420,190 L420,120 Z" class="icon" rx="10"/>
      <path d="M380,140 L420,140" stroke="white" stroke-width="4"/>
      <path d="M380,160 L420,160" stroke="white" stroke-width="4"/>
      <circle cx="400" cy="155" r="8" fill="white"/>
    </g>
  </defs>
  
  <!-- Fond avec coins arrondis -->
  <rect width="512" height="512" rx="80" fill="url(#bg-gradient)" mask="url(#rounded-corners)"/>
  
  <!-- Icône de localisation centrale -->
  <use href="#location-icon"/>
  
  <!-- Icône d'événement à gauche -->
  <use href="#event-icon"/>
  
  <!-- Icône de ticket à droite -->
  <use href="#ticket-icon"/>
  
  <!-- Texte EventRush -->
  <text x="256" y="420" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="42" font-weight="bold">
    EventRush
  </text>
</svg>


                    
                    {{-- <!-- Étoiles --> --}}
                    {{-- <div class="star star-1">★</div>
                    <div class="star star-2">★</div>
                    <div class="star star-3">☆</div>
                    <div class="star star-4">☆</div> --}}
                </div>
                <h2>Logo Principal</h2>
            </div>
        </div>
        
        <div class="instructions">
            <h2>Comment utiliser ce logo dans une vue Blade</h2>
            <p>Pour utiliser ce logo dans votre application Laravel, suivez ces étapes :</p>
            
            <h3>1. Créez un fichier SVG</h3>
            <p>Enregistrez le code SVG du cœur dans un fichier <code>heart-logo.svg</code> dans le dossier <code>public/images/</code> de votre application Laravel.</p>
            
            <h3>2. Intégrez le logo dans votre vue Blade</h3>
            <p>Utilisez le code suivant pour afficher le logo :</p>
            
            <pre><code>&lt;div class="logo" style="width: 200px; height: 200px; position: relative;"&gt;
    &lt;div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(233, 30, 99, 0.1); border-radius: 50%;"&gt;&lt;/div&gt;
    &lt;div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; height: 90%; border-radius: 50%; border: 1px solid rgba(233, 30, 99, 0.3);"&gt;&lt;/div&gt;
    &lt;div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 75%; height: 75%; border-radius: 50%; border: 1.5px solid rgba(233, 30, 99, 0.5);"&gt;&lt;/div&gt;
    
    &lt;img src="{{ asset('images/heart-logo.svg') }}" alt="Logo App Événements" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 60%; height: 60%;"&gt;
    
    &lt;div style="position: absolute; top: 15%; right: 20%; color: #FFC107; font-size: 20px;"&gt;★&lt;/div&gt;
    &lt;div style="position: absolute; bottom: 15%; left: 20%; color: #FFC107; font-size: 20px;"&gt;★&lt;/div&gt;
    &lt;div style="position: absolute; top: 30%; left: 15%; color: #FFC107; font-size: 16px;"&gt;☆&lt;/div&gt;
    &lt;div style="position: absolute; bottom: 30%; right: 15%; color: #FFC107; font-size: 16px;"&gt;☆&lt;/div&gt;
&lt;/div&gt;</code></pre>
            
            <h3>3. Utilisation avec différentes tailles et couleurs</h3>
            <p>Vous pouvez facilement modifier la taille et les couleurs du logo en ajustant les valeurs CSS.</p>
            
            <div class="variations">
                <div class="variation">
                    <div class="color-box" style="background-color: #2196F3;">
                        Bleu
                    </div>
                    <p>#2196F3</p>
                </div>
                
                <div class="variation">
                    <div class="color-box" style="background-color: #4CAF50;">
                        Vert
                    </div>
                    <p>#4CAF50</p>
                </div>
                
                <div class="variation">
                    <div class="color-box" style="background-color: #9C27B0;">
                        Violet
                    </div>
                    <p>#9C27B0</p>
                </div>
                
                <div class="variation">
                    <div class="color-box" style="background-color: #FF9800;">
                        Orange
                    </div>
                    <p>#FF9800</p>
                </div>
            </div>
        </div>
        
        <div class="code-section">
            <h2>Code SVG autonome</h2>
            <p>Voici le code SVG complet pour le cœur, que vous pouvez utiliser dans d'autres technologies :</p>
            
            <pre><code>&lt;svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"&gt;
  &lt;path d="M50 88.5L43.5 82.5C36.5 76 17 59 17 40.5C17 27.5 27.5 17 40.5 17C45.5 17 50.5 19 54 22.5L50 26.5L46 22.5C49.5 19 54.5 17 59.5 17C72.5 17 83 27.5 83 40.5C83 59 63.5 76 56.5 82.5L50 88.5Z" fill="#E91E63"/&gt;
  &lt;path d="M59.5 13C54.5 13 50 15 46.5 18.5L50 22L53.5 18.5C50 15 45.5 13 40.5 13C25 13 13 25 13 40.5C13 61 35 80 50 92C65 80 87 61 87 40.5C87 25 75 13 59.5 13Z" stroke="#E91E63" stroke-width="2" fill="none"/&gt;
&lt;/svg&gt;</code></pre>
        </div>
        
        <footer>
            <p>© 2023 App Événements - Tous droits réservés</p>
        </footer>
    </div>
</body>
</html>