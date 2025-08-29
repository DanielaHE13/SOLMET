

<head>
  <meta charset="UTF-8">
  <title>No autorizado - Solmet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
  <style>
    body {
      background: linear-gradient(to top, var(--bs-success) 0%, #94bc9e 50%, #f3fbf6 100%);
      font-family: 'Mukta', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }

    .card {
      background-color: #ffffffcc;
      border-radius: 20px;
      padding: 30px;
      text-align: center;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      max-width: 650px;
      width: 90%;
      backdrop-filter: blur(6px);
    }

    h1 {
      color: var(--bs-success);
      font-weight: bold;
    }

    p {
      font-size: 1.1rem;
      color: #13663a;
    }

    .btn-green {
      background: var(--bs-success);
      color: #fff;
      border-radius: 12px;
      font-weight: bold;
      padding: 12px 24px;
      text-decoration: none;
      transition: all .3s ease;
    }

    .btn-green:hover {
      background: #0f5132;
      color: #fff;
      transform: scale(1.05);
    }
  </style>
</head>


  <div class="card">
    <div class="d-flex justify-content-center">
      <!-- Animación Lottie -->
      <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
      <dotlottie-player
        src="https://lottie.host/3a48f122-58ee-4d24-bafc-63259afed4df/3Mnv6QJqob.lottie"
        background="transparent" speed="1"
        style="width: 260px; height: 260px"
        loop autoplay>
      </dotlottie-player>
    </div>

    <h1>⚠️ Acceso no autorizado</h1>
    <p>Tu rol <strong><?= htmlspecialchars($_SESSION['rol'] ?? 'Anónimo') ?></strong> no tiene permisos para ingresar a esta sección.</p>

    <a href="/index.php?pid=<?= base64_encode('PRESENTACION/Autenticar.php') ?>" class="btn btn-green mt-3">
      Volver al inicio de sesión
    </a>

  </div>


