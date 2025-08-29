<!-- ===================== INICIO • LANDING SOLMET ===================== -->
<style>
  /* ======= Paleta y utilidades locales ======= */
  :root {
    --solmet-green: #13663a;
    --solmet-green-100: #f3fbf6;
    --solmet-green-200: #94bc9e;
  }

  /* Navbar: fondo claro sutil con borde inferior */
  .navbar-solmet {
    background-color: var(--solmet-green-100);
    border-bottom: 1px solid #198754;
    /* bs-success */
  }

  @media (max-width: 768px) {

    .hero-bubble-1,
    .hero-bubble-2 {
      display: none;
    }
  }

  /* Centrado del título en navbar (solo >= lg para evitar solaparse en móvil) */
  @media (min-width: 992px) {
    .navbar-title-center {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    }
  }

  /* HERO con degradado vertical */
  .hero {
    background: linear-gradient(to top, #198754 0%, var(--solmet-green-200) 50%, var(--solmet-green-100) 100%);
    padding: 80px 0;
    position: relative;
    overflow: hidden;
  }

  /* figuras decorativas */
  .hero-bubble-1 {
    position: absolute;
    top: 10%;
    left: 5%;
    width: 300px;
    height: 300px;
    background: #7ed6a6;
    border-radius: 50%;
    opacity: 0.9;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    z-index: 0;
  }

  .hero-bubble-2 {
    position: absolute;
    top: 60%;
    left: 70%;
    width: 400px;
    height: 400px;
    background: var(--solmet-green);
    border-radius: 50%;
    opacity: 0.3;
    z-index: 0;
  }

  /* panel del hero */
  .hero-panel {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  }

  /* Cards módulos */
  .module-card {
    border: 0;
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.06);
    height: 100%;
  }

  .module-card img {
    height: 200px;
    object-fit: cover;
  }

  /* Footer */
  .footer-solmet {
    background-color: var(--solmet-green);
    color: #fff;
  }
</style>


<!-- ===================== NAVBAR ===================== -->
<nav class="navbar navbar-expand-lg navbar-light navbar-solmet" role="navigation" aria-label="Barra de navegación principal">
  <div class="container-fluid">
    <!-- Logo / marca -->
    <a class="navbar-brand d-flex align-items-center text-success" href="index.php" aria-label="Inicio">
      <img src="IMG/logosinfondo.png" alt="Logo Solmet, gestión de producción" width="200" height="auto" class="me-2" loading="lazy">
    </a>
    <!-- Título centrado (visible en >= lg) -->
    <div class="navbar-title-center d-none d-lg-block">
      <span class="fw-bold h5 m-0 text-success">SISTEMA DE GESTIÓN DE PRODUCCIÓN</span>
    </div>

    <!-- Toggler móvil -->
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Mostrar menú">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menú derecho -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
        <li class="nav-item">
          <a class="btn btn-success text-white px-3 rounded-pill"
            href="?pid=<?= base64_encode('PRESENTACION/Autenticar.php'); ?>#registro">
            Registrarse
          </a>
        </li>
        <li class="nav-item">
          <a class="btn btn-outline-success px-3 rounded-pill"
            href="?pid=<?= base64_encode('PRESENTACION/Autenticar.php'); ?>">
            Iniciar sesión
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- ===================== HERO ===================== -->
<section id="inicio" class="hero" aria-label="Presentación del sistema">
  <!-- Elementos decorativos -->
  <div class="hero-bubble-1" aria-hidden="true">
    <img src="IMG/ingesinfondo.png" alt="Ilustración producción" style="width: 80%; height: auto;" loading="lazy">
  </div>
  <div class="hero-bubble-2" aria-hidden="true"></div>

  <!-- Contenido -->
  <div class="container position-relative" style="z-index: 1;">
    <div class="row align-items-center">
      <!-- Columna vacía para balance en pantallas medianas y grandes -->
      <div class="col-md-5 d-none d-md-block"></div>

      <div class="col-12 col-md-7 hero-panel p-4">
        <h1 class="fw-bold text-success fs-2 mb-2">Optimiza tu producción con Solmet</h1>
        <p class="fs-5 mb-3">Gestiona moldes, máquinas y órdenes de producción en un solo lugar. 🚀</p>
        <a href="?pid=<?= base64_encode('PRESENTACION/Autenticar.php'); ?>"
          class="btn btn-success px-4 py-2 rounded-pill">
          Comenzar ahora
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ===================== MÓDULOS ===================== -->
<section id="modulos" class="container-fluid py-5" style="background-color: #f0faf5;" aria-labelledby="titulo-modulos">
  <div class="container">
    <h2 id="titulo-modulos" class="fw-bold mb-5 text-center text-success">Módulos principales</h2>

    <div class="row justify-content-center">
      <!-- Catálogo digital -->
      <div class="col-md-4 mb-4">
        <article class="card module-card">
          <img src="IMG/catalogo.png" class="card-img-top" alt="Catálogo digital de productos y moldes" loading="lazy">
          <div class="card-body text-center">
            <h3 class="card-title h5 fw-semibold text-success">Catálogo Digital</h3>
            <a href="canvasite" class="btn btn-success rounded-pill px-4">
              <i class="fa-solid fa-book-open me-2" aria-hidden="true"></i>
              <span>Ver Catálogo</span>
            </a>
          </div>
        </article>
      </div>

      <!-- Sedes -->
      <div class="col-md-4 mb-4">
        <article class="card module-card">
          <img src="IMG/ubicacion.png" class="card-img-top" alt="Mapa de ubicación de nuestras sedes" loading="lazy">
          <div class="card-body text-center">
            <h3 class="card-title h5 fw-semibold text-success">Nuestras Sedes</h3>
            <a href="https://maps.app.goo.gl/jHgz1jLrc9nB8cX16" target="_blank" rel="noopener"
              class="btn btn-success rounded-pill px-4">
              <i class="fa-solid fa-location-dot me-2" aria-hidden="true"></i>
              <span>Encuéntranos aquí</span>
            </a>
          </div>
        </article>
      </div>

      <!-- Contáctanos -->
      <div class="col-md-4 mb-4">
        <article class="card module-card">
          <img src="IMG/contactanos.png" class="card-img-top" alt="Ilustración para contacto" loading="lazy">
          <div class="card-body text-center">
            <h3 class="card-title h5 fw-semibold text-success">Contáctanos</h3>
            <a href="mailto:solmetltda@gmail.com" class="btn btn-success rounded-pill px-4">
              <i class="fa-solid fa-envelope me-2" aria-hidden="true"></i>
              <span>Enviar correo</span>
            </a>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer class="footer-solmet text-center py-3" role="contentinfo">
  <div class="container">
    <small>&copy; 2025 Solmet™ • Producción</small>
  </div>
</footer>

<!-- ===================== FIN • LANDING SOLMET ===================== -->