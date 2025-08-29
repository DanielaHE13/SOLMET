<?php
// PRESENTACION/OrdenProduccion/crear.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';
require_once __DIR__ . '/../../logica/Molde.php';
require_once __DIR__ . '/../../logica/Maquina.php'; // por si la usas en otros puntos

/* ---- Autorización ---- */
$rol = $_SESSION['rol'] ?? null;
if (!in_array($rol, ['admin', 'operador'], true)) {
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Menú según rol (opcional aquí) ---- */
if ($rol === 'admin') include_once __DIR__ . '/../Admin/menuAdmin.php';
else                 include_once __DIR__ . '/../Operador/menuOperador.php';

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ---- Consecutivo sugerido YYYYMMDD + ### ---- */
$tz = new DateTimeZone('America/Bogota');
$prefijo = (new DateTime('now', $tz))->format('Ymd');

$cx = new Conexion();
$cx->abrir();
$cx->ejecutar("SELECT MAX(numero_op) FROM orden_produccion WHERE numero_op LIKE ?", [$prefijo . '%']);
$row = $cx->registro();
$cx->cerrar();

$maxHoy    = $row[0] ?? null;
$ultimo    = $maxHoy ? (int)substr((string)$maxHoy, 8) : 0;
$siguiente = $ultimo + 1;

$numeroOPRaw = $prefijo . str_pad((string)$siguiente, 3, '0', STR_PAD_LEFT);
$numeroOPUI  = substr($numeroOPRaw, 0, 4) . '-' . substr($numeroOPRaw, 4, 2) . '-' . substr($numeroOPRaw, 6, 2) . ' #' . substr($numeroOPRaw, 8);

/* ---- Moldes activos (id, nombre, colada_g) ---- */
$moldes = Molde::listarDisponibles();

/* ---- Endpoints (via router) ---- */
$API_MAQ  = 'PRESENTACION/OrdenProduccion/api/maquinas_por_molde.php';
$API_PROD = 'PRESENTACION/OrdenProduccion/api/productos_por_molde.php';

$URL_CREAR = '?pid=' . base64_encode('PRESENTACION/OrdenProduccion/crear.php');
$URL_VER   = '?pid=' . base64_encode('PRESENTACION/OrdenProduccion/ver.php');
$URL_PDF   = 'PRESENTACION/OrdenProduccion/pdf.php';

/* ---- CSP nonce opcional ---- */
$cspNonce = htmlspecialchars($_SESSION['csp_nonce'] ?? '');
?>
<div class="container-fluid px-2 px-md-3 my-2">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h4 class="mb-0 fw-bold text-success">Crear Orden de Producción</h4>
  </div>

  <form id="formOP" autocomplete="off">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="numero_op" id="numero_op" value="<?= htmlspecialchars((string)$numeroOPRaw) ?>">

    <div class="row g-3">
      <!-- ================= COLUMNA A ================= -->
      <div class="col-12 col-lg-8">
        <!-- 1. Datos básicos -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-sliders me-2"></i>1. Datos básicos</h5>
          </div>
          <div class="card-body">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">N° OP</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="fa-solid fa-hashtag text-success"></i></span>
                  <input type="text" class="form-control" value="<?= htmlspecialchars($numeroOPUI) ?>" readonly>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <label for="id_molde" class="form-label fw-semibold">Molde</label>
                <select class="form-select" id="id_molde" name="id_molde" required>
                  <option value="">Seleccione un molde...</option>
                  <?php foreach ($moldes as $m): ?>
                    <option value="<?= htmlspecialchars($m['id']) ?>"
                      data-colada="<?= htmlspecialchars((string)($m['colada_g'] ?? '')) ?>">
                      <?= htmlspecialchars($m['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-12 col-md-4">
                <label for="id_maquina" class="form-label fw-semibold">Máquina</label>
                <select class="form-select" id="id_maquina" name="id_maquina" required disabled>
                  <option value="">Seleccione un molde primero...</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Productos -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-boxes-stacked me-2"></i>3. Productos</h5>
            <button type="button" class="btn btn-sm btn-outline-success" id="btnAddProd">
              <i class="fa-solid fa-plus me-1"></i>Agregar
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle" id="tablaProductos">
                <thead class="table-light">
                  <tr>
                    <th style="width:12%">ID (auto)</th>
                    <th style="width:26%">Producto (por nombre)</th>
                    <th style="width:12%">Insertos</th>
                    <th style="width:14%">Peso (g)</th>
                    <th style="width:14%">CPM</th>
                    <th style="width:10%">Unid. est.</th>
                    <th style="width:12%">Peso prod. (kg)</th>
                    <th style="width:6%"></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <div class="small text-muted">Máx. 6 productos por orden.</div>
          </div>
        </div>

        <!-- 5. Observaciones -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-comment-dots me-2"></i>5. Observaciones</h5>
          </div>
          <div class="card-body">
            <textarea class="form-control" name="observaciones" id="observaciones" rows="3" placeholder="Observaciones (opcional)"></textarea>
          </div>
        </div>
      </div>

      <!-- ================= COLUMNA B ================= -->
      <div class="col-12 col-lg-4">
        <!-- 2. Pesos -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-weight-hanging me-2"></i>2. Pesos</h5>
          </div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small">Kilos a producir (kg)</label>
                <input
                  type="number"
                  step="0.001"
                  min="0.001"
                  class="form-control"
                  id="kg_a_producir"
                  name="kg_a_producir"
                  placeholder="0.000"
                  required>
              </div>

              <div class="col-6">
                <label class="form-label small">Peso colada (g)</label>
                <input type="number" step="0.01" class="form-control" id="peso_colada_g" name="peso_colada_g" readonly>
              </div>
              <div class="col-6">
                <label class="form-label small">Peso total piezas (g)</label>
                <input type="number" step="0.01" class="form-control" id="peso_total_piezas_g" readonly>
              </div>
              <div class="col-6">
                <label class="form-label small">Peso cierre (g)</label>
                <input type="number" step="0.01" class="form-control" id="peso_cierre_g" name="peso_cierre_g" readonly>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Materia Prima -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-flask me-2"></i>4. Materia prima</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table align-middle" id="tablaMP">
                <thead class="table-light">
                  <tr>
                    <th style="width:22%">Tipo</th>
                    <th>Referencia</th>
                    <th style="width:18%">Kg</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="row-mp" data-tipo="original">
                    <td class="fw-semibold text-success">Original</td>
                    <td>
                      <select class="form-select mp-codigo" data-tipo="original">
                        <option value="">Cargando referencias...</option>
                      </select>
                    </td>
                    <td><input type="number" class="form-control mp-kgplan" min="0" step="0.001" placeholder="0.000"></td>
                  </tr>
                  <tr class="row-mp" data-tipo="peletizado">
                    <td class="fw-semibold text-success">Peletizado</td>
                    <td>
                      <select class="form-select mp-codigo" data-tipo="peletizado">
                        <option value="">Cargando referencias...</option>
                      </select>
                    </td>
                    <td><input type="number" class="form-control mp-kgplan" min="0" step="0.001" placeholder="0.000"></td>
                  </tr>
                  <tr class="row-mp" data-tipo="molido">
                    <td class="fw-semibold text-success">Molido</td>
                    <td>
                      <select class="form-select mp-codigo" data-tipo="molido">
                        <option value="">Cargando referencias...</option>
                      </select>
                    </td>
                    <td><input type="number" class="form-control mp-kgplan" min="0" step="0.001" placeholder="0.000"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Producción estimada -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-industry me-2"></i>Producción estimada</h5>
          </div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small">Tiros estimados</label>
                <input type="number" step="1" class="form-control" id="tiros_estimados" readonly>
              </div>
              <div class="col-6">
                <label class="form-label small">Peso total piezas (orden, kg)</label>
                <input type="number" step="0.001" class="form-control" id="peso_piezas_total_orden_kg" readonly>
              </div>
              <div class="col-12">
                <label class="form-label small">Peso total orden (piezas + colada, kg)</label>
                <input type="number" step="0.001" class="form-control" id="peso_total_orden_kg" readonly>
              </div>
            </div>
          </div>
        </div>

        <!-- Expectativa -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-scale-balanced me-2"></i>Expectativa</h5>
          </div>
          <div class="card-body">
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small">Peso teórico total (kg)</label>
                <input type="number" step="0.001" class="form-control" name="peso_teorico_total_kg" id="peso_teorico_total_kg" readonly>
              </div>
            </div>
          </div>
        </div>

        <!-- Duración -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-white">
            <h5 class="mb-0 text-success"><i class="fa-solid fa-clock me-2"></i>Duración de la producción</h5>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label fw-semibold small">Duración estimada (h)</label>
                <input type="number" step="0.01" class="form-control" id="duracion_min" name="duracion_min" readonly>
              </div>
            </div>
          </div>
        </div>

        <!-- Botonera -->
        <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
          <button type="button" class="btn btn-outline-success" id="btnGuardar">
            <i class="fa-regular fa-floppy-disk me-1"></i> Guardar OP
          </button>
        </div>

      </div>
    </div>
  </form>
</div>

<style>
  :root{
    --g50:#f3fbf7;
    --g100:#e7f6ee;
    --g600:#1ea257;
    --g700:#188249;
  }
  body{
    min-height:100vh;
    background:
      radial-gradient(1200px 200px at -20% -50%, #ffffff 0%, transparent 60%),
      linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%);
  }
  .card{ border-radius:16px; }
  .card-header{ border-top-left-radius:16px; border-top-right-radius:16px; }
  .btn-success{ background:var(--g600); border-color:var(--g600); }
  .btn-success:hover{ background:var(--g700); border-color:var(--g700); }
  .btn-outline-success:hover{ color:#fff; background:var(--g600); border-color:var(--g600); }
</style>

<script nonce="<?= $cspNonce ?>">
document.addEventListener('DOMContentLoaded', function() {
  // Endpoints
  const API_MAQ  = '<?= $API_MAQ ?>';
  const API_PROD = '<?= $API_PROD ?>';

  const URL_CREAR = '<?= $URL_CREAR ?>';
  const URL_VER   = '<?= $URL_VER   ?>';
  const URL_PDF   = '<?= $URL_PDF   ?>';

  // DOM
  const form = document.getElementById('formOP');
  const selMolde = document.getElementById('id_molde');
  const selMaquina = document.getElementById('id_maquina');

  const inKgProd     = document.getElementById('kg_a_producir');
  const inColadaG    = document.getElementById('peso_colada_g');
  const inTotPiezasG = document.getElementById('peso_total_piezas_g');
  const inCierreG    = document.getElementById('peso_cierre_g');

  const inTiros      = document.getElementById('tiros_estimados');
  const inPzasOrdKg  = document.getElementById('peso_piezas_total_orden_kg');
  const inOrdenTotKg = document.getElementById('peso_total_orden_kg');

  const inTeoricoKg  = document.getElementById('peso_teorico_total_kg');
  const inHoras      = document.getElementById('duracion_min');

  const tbBody = document.querySelector('#tablaProductos tbody');
  const btnAdd = document.getElementById('btnAddProd');
  const MAX_PRODS = 6;

  let productosCache = [];
  let productosByName = Object.create(null);

  // Utils
  const fmt = (n,d)=>{const x=Number(n);return isFinite(x)?x.toFixed(d):''};
  const toNumber = v => {const n=parseFloat(String(v||'').replace(',','.'));return isFinite(n)?n:0};
  const warn = m => alert(m);

  // ===== MP vs KG a producir =====
  function getMPTotalKg(){
    let total=0;
    document.querySelectorAll('#tablaMP .mp-kgplan').forEach(inp=>{ total += toNumber(inp.value); });
    return total;
  }
  function ensureMpInfoEl(){
    let el = document.getElementById('mpTotalesInfo');
    if(!el){
      el = document.createElement('div');
      el.id='mpTotalesInfo';
      el.className='small mt-2';
      const cardBody = document.querySelector('#tablaMP')?.closest('.card-body');
      if(cardBody) cardBody.appendChild(el);
    }
    return el;
  }
  function validateMPvsGoal(){
    const goal = toNumber(inKgProd?.value);
    const total = getMPTotalKg();
    const restante = goal - total;

    const info = ensureMpInfoEl();
    if(info){
      info.textContent = `MP total: ${fmt(total,3)} kg — Restante: ${fmt(restante,3)} kg`;
      info.style.color = (restante < -0.0005) ? '#dc3545' : '#6c757d';
    }

    const invalid = goal > 0 && total > goal + 1e-6;
    const btn1 = document.getElementById('btnGuardar');
    const btn2 = document.getElementById('btnGuardarImprimir');
    if (btn1) btn1.disabled = invalid;
    if (btn2) btn2.disabled = invalid;
    return !invalid;
  }

  // Helper URL robusto (respeta subcarpeta)
  function withParam(baseUrl, params){
    const u = new URL(baseUrl, window.location.href);
    Object.entries(params||{}).forEach(([k,v])=>u.searchParams.set(k,v));
    return u.toString();
  }

  function enforceLimit(){
    if (btnAdd) btnAdd.disabled = (tbBody.querySelectorAll('tr').length >= MAX_PRODS);
  }
  function isDupId(id, exceptTr){
    const ids=[];
    for(const tr of tbBody.querySelectorAll('tr')){
      if (exceptTr && tr===exceptTr) continue;
      const v=(tr.querySelector('.id_producto')||{}).value||'';
      if(v) ids.push(v);
    }
    return ids.indexOf(String(id))>=0;
  }

  function addRow(prefill){
    if (tbBody.querySelectorAll('tr').length >= MAX_PRODS){ warn('Máximo 6 productos por orden.'); return; }
    const tr=document.createElement('tr');
    tr.innerHTML =
      '<td><input type="text" class="form-control form-control-sm id_producto_display" readonly>' +
      '<input type="hidden" class="id_producto"></td>' +
      '<td><input type="text" class="form-control form-control-sm prod_nombre" placeholder="Escribe y elige..." list="dlProductos"></td>' +
      '<td><input type="number" min="1" step="1" class="form-control form-control-sm cantidad" value="1"></td>' +
      '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm peso_g" placeholder="0.00" readonly></td>' +
      '<td><input type="number" min="0" step="0.01" class="form-control form-control-sm cpm" placeholder="0.00" readonly></td>' +
      '<td><input type="number" class="form-control form-control-sm unid_est" readonly></td>' +
      '<td><input type="number" step="0.001" class="form-control form-control-sm peso_est_kg" readonly></td>' +
      '<td><button type="button" class="btn btn-sm btn-outline-danger del-row"><i class="fa-solid fa-xmark"></i></button></td>';
    tbBody.appendChild(tr);
    if(prefill){
      tr.querySelector('.prod_nombre').value = prefill.nombre || '';
      tr.querySelector('.id_producto').value = prefill.id || '';
      tr.querySelector('.id_producto_display').value = prefill.id || '';
      tr.querySelector('.peso_g').value = prefill.peso_g!=null?fmt(prefill.peso_g,2):'';
      tr.querySelector('.cpm').value = prefill.cpm!=null?fmt(prefill.cpm,2):'';
    }
    enforceLimit();
  }

  function recalcular(){
    let total_g_por_tiro=0, minPorTiro=0;
    for(const tr of tbBody.querySelectorAll('tr')){
      const cant = toNumber((tr.querySelector('.cantidad')||{}).value);
      const peso = toNumber((tr.querySelector('.peso_g')||{}).value);
      const cpm  = toNumber((tr.querySelector('.cpm')||{}).value);
      if (cant>0 && peso>=0) total_g_por_tiro += cant*peso;
      if (cpm>0 && cant>0)  minPorTiro += (cant/cpm);
    }
    if(inTotPiezasG) inTotPiezasG.value = fmt(total_g_por_tiro,2);
    if(inTeoricoKg)  inTeoricoKg.value  = fmt(total_g_por_tiro/1000,3);

    const colada_g = toNumber(inColadaG?inColadaG.value:0);
    const cierre_g = total_g_por_tiro + colada_g;
    if(inCierreG) inCierreG.value = fmt(cierre_g,2);

    const kgProd = toNumber(inKgProd?inKgProd.value:0);
    const tiros = (kgProd>0 && cierre_g>0)? Math.floor((kgProd*1000)/cierre_g) : 0;
    if(inTiros) inTiros.value = tiros || '';

    let total_piezas_orden_g=0;
    for(const tr of tbBody.querySelectorAll('tr')){
      const cant = toNumber((tr.querySelector('.cantidad')||{}).value);
      const peso = toNumber((tr.querySelector('.peso_g')||{}).value);
      const unidades = (tiros>0)? (tiros*cant) : cant;
      const pesoProdKg = (unidades*peso)/1000;
      tr.querySelector('.unid_est').value    = unidades || '';
      tr.querySelector('.peso_est_kg').value = pesoProdKg ? fmt(pesoProdKg,3) : '0.000';
      total_piezas_orden_g += (unidades*peso);
    }
    const piezasOrdenKg = total_piezas_orden_g/1000;
    if(inPzasOrdKg)  inPzasOrdKg.value  = piezasOrdenKg ? fmt(piezasOrdenKg,3) : '0.000';
    const coladaTotalKg = (tiros>0 ? (tiros*colada_g) : colada_g)/1000;
    const ordenTotalKgV = piezasOrdenKg + coladaTotalKg;
    if(inOrdenTotKg) inOrdenTotKg.value = ordenTotalKgV ? fmt(ordenTotalKgV,3) : '0.000';

    const dev_tiro_g = Math.max(0, colada_g - total_g_por_tiro);
    const horas = (tiros>0 && minPorTiro>0) ? (tiros*minPorTiro/60) : 0;
    if(inHoras) inHoras.value = horas ? fmt(horas,2) : '';
  }

  function snapshot(){
    let total_g_por_tiro=0;
    for(const tr of tbBody.querySelectorAll('tr')){
      const cant = toNumber((tr.querySelector('.cantidad')||{}).value);
      const peso = toNumber((tr.querySelector('.peso_g')||{}).value);
      if(cant>0 && peso>=0) total_g_por_tiro += cant*peso;
    }
    const colada_g = toNumber(inColadaG?inColadaG.value:0);
    const cierre_g = total_g_por_tiro + colada_g;
    const kgProd   = toNumber(inKgProd?inKgProd.value:0);
    const tiros = (kgProd>0 && cierre_g>0)? Math.floor((kgProd*1000)/cierre_g) : 0;

    const pt_kg = +(fmt(total_g_por_tiro/1000,3));
    const dev_tiro_g = Math.max(0, colada_g - total_g_por_tiro);
    const dev_kg = +(fmt((tiros>0 ? (dev_tiro_g*tiros)/1000 : dev_tiro_g/1000),3));
    return { pt_kg, dev_kg };
  }

  // Eventos UI
  btnAdd?.addEventListener('click', () => { addRow(); recalcular(); validateMPvsGoal(); });

  document.addEventListener('click', e => {
    const btn = e.target.closest && e.target.closest('.del-row');
    if(!btn) return;
    e.preventDefault();
    const tr = btn.closest('tr');
    if(tr) tr.remove();
    enforceLimit();
    recalcular();
    validateMPvsGoal();
  });

  document.addEventListener('input', e => {
    if (e.target.matches('.cantidad,.peso_g') || e.target.id==='kg_a_producir' || e.target.id==='peso_colada_g'){
      recalcular();
    }
    if (e.target.matches('.mp-kgplan') || e.target===inKgProd){
      validateMPvsGoal();
    }
  });

  // Cambio de molde: carga máquinas + productos y setea colada
  selMolde.addEventListener('change', () => {
    const opt = selMolde.selectedOptions && selMolde.selectedOptions[0];
    const colada = opt ? (opt.getAttribute('data-colada') || '') : '';
    inColadaG.value = colada;

    // máquinas
    selMaquina.innerHTML = '<option value="">Cargando...</option>';
    selMaquina.disabled = true;

    fetch(withParam(API_MAQ,{id_molde: selMolde.value}),{ headers:{'Accept':'application/json'}, cache:'no-store' })
      .then(async r => {
        const txt = await r.text();
        let j; try{ j = JSON.parse(txt); } catch(_){ throw new Error('Respuesta no JSON: '+txt.slice(0,120)); }
        if(!r.ok || !j.ok) throw new Error(j.error || ('HTTP '+r.status));
        return j.data || [];
      })
      .then(arr => {
        if(!Array.isArray(arr) || arr.length===0){
          selMaquina.innerHTML = '<option value="">(sin máquinas compatibles)</option>';
          selMaquina.disabled = true; return;
        }
        selMaquina.innerHTML = '<option value="">Seleccione...</option>' +
          arr.map(m => '<option value="'+m.id+'">'+(m.nombre || ('Maq '+m.id))+'</option>').join('');
        selMaquina.disabled = false;
      })
      .catch(err => {
        console.error('Error al cargar máquinas:', err);
        selMaquina.innerHTML = '<option value="">(error)</option>';
        selMaquina.disabled = true;
      });

    // productos
    fetch(withParam(API_PROD,{id_molde: selMolde.value}),{ headers:{'Accept':'application/json'}, cache:'no-store' })
      .then(async r => {
        const txt = await r.text();
        let j; try{ j = JSON.parse(txt); } catch{ throw new Error('Respuesta no JSON de productos: '+txt.slice(0,160)); }
        if(!r.ok || !j.ok) throw new Error(j.error || j.msg || ('HTTP '+r.status));
        return Array.isArray(j.data)? j.data : [];
      })
      .then(arr => {
        productosCache = arr.map(p => ({
          id:String(p.id||''), nombre:String(p.nombre||''),
          peso_g:(p.peso_g!=null)?Number(p.peso_g):null,
          cpm:(p.cpm!=null)?Number(p.cpm):null
        }));
        productosByName = Object.create(null);
        for(const p of productosCache) productosByName[p.nombre]=p;

        const dl = document.getElementById('dlProductos') || (()=>{const d=document.createElement('datalist'); d.id='dlProductos'; document.body.appendChild(d); return d;})();
        dl.innerHTML = productosCache.map(p=>`<option value="${p.nombre}"></option>`).join('');

        tbBody.innerHTML = '';
        enforceLimit();
        addRow();
        recalcular();
        validateMPvsGoal();
      })
      .catch(err => {
        console.error('Error al cargar productos:', err);
        productosCache = [];
        productosByName = Object.create(null);
        const dl = document.getElementById('dlProductos'); if(dl) dl.innerHTML='';
        tbBody.innerHTML='';
        enforceLimit();
        addRow();
        recalcular();
        validateMPvsGoal();
        alert('No fue posible cargar los productos. Revisa la consola (F12 > Console).');
      });
  });

  // Autocompletar de producto
  document.addEventListener('change', e => {
    if(!e.target.classList?.contains('prod_nombre')) return;
    const tr = e.target.closest('tr');
    const p  = productosByName[(e.target.value||'').trim()];
    const idh = tr.querySelector('.id_producto');
    const idd = tr.querySelector('.id_producto_display');
    const pg  = tr.querySelector('.peso_g');
    const pc  = tr.querySelector('.cpm');
    if(!p){
      if(idh) idh.value=''; if(idd) idd.value=''; if(pg) pg.value=''; if(pc) pc.value='';
      return setTimeout(()=>{recalcular(); validateMPvsGoal();},0);
    }
    if(isDupId(p.id,tr)){
      alert('Este producto ya está agregado.');
      e.target.value=''; if(idh) idh.value=''; if(idd) idd.value=''; if(pg) pg.value=''; if(pc) pc.value='';
      e.target.focus(); return;
    }
    if(idh) idh.value=p.id;
    if(idd) idd.value=p.id;
    if(pg)  pg.value=p.peso_g!=null?fmt(p.peso_g,2):'';
    if(pc)  pc.value=p.cpm!=null?fmt(p.cpm,2):'';
    setTimeout(()=>{recalcular(); validateMPvsGoal();},0);
  });

  // Cargar MP refs (3 filas fijas)
  (function loadMP(){
    const ENDPOINT = 'PRESENTACION/OrdenProduccion/crear_ajax.php';
    function loadRefs(tipo, selectEl){
      selectEl.innerHTML = '<option value="">Cargando…</option>';
      fetch(ENDPOINT+'?action=mp_refs&tipo='+encodeURIComponent(tipo),{cache:'no-store'})
        .then(r=>r.json())
        .then(json=>{
          if(!json.ok) throw new Error(json.msg||'No ok');
          const data=json.data||[];
          selectEl.innerHTML = '<option value="">Seleccione referencia…</option>';
          for(const it of data){
            const opt=document.createElement('option');
            opt.value=it.codigo;
            opt.textContent=it.referencia+' ('+it.codigo+')';
            selectEl.appendChild(opt);
          }
        })
        .catch(()=>{ selectEl.innerHTML='<option value="">Error al cargar</option>'; });
    }
    document.querySelectorAll('#tablaMP select.mp-codigo').forEach(s=>{
      const tipo=s.getAttribute('data-tipo'); if(tipo) loadRefs(tipo,s);
    });
  })();

  // Guardar
  async function guardar(e, imprimir=false){
    if(!form.checkValidity()){ form.reportValidity(); return; }
    e.preventDefault();

    if(!selMolde.value)   return warn('Selecciona un molde.');
    if(!selMaquina.value) return warn('Selecciona una máquina.');

    // Validación estricta MP <= KG a producir
    const kgProdGoal = toNumber(inKgProd?.value);
    const mpTotal = getMPTotalKg();
    if (kgProdGoal <= 0) return warn('Ingresa los kilos a producir.');
    if (mpTotal > kgProdGoal + 1e-6){
      validateMPvsGoal();
      return warn(`La suma de la materia prima (${fmt(mpTotal,3)} kg) no puede superar los kilos a producir (${fmt(kgProdGoal,3)} kg).`);
    }

    const rows = Array.from(tbBody.querySelectorAll('tr'));
    if(rows.length===0) return warn('Agrega al menos un producto.');
    if(rows.length>MAX_PRODS) return warn('Máximo 6 productos por orden.');

    const prods=[];
    for(const tr of rows){
      const idp = (tr.querySelector('.id_producto')||{}).value||'';
      const cant= toNumber((tr.querySelector('.cantidad')||{}).value);
      const pes = toNumber((tr.querySelector('.peso_g')||{}).value);
      const cpm = toNumber((tr.querySelector('.cpm')||{}).value);
      if(idp && cant>0){
        if(isDupId(idp,tr)){ warn('Hay productos repetidos.'); return; }
        prods.push({ id_producto:idp, cantidad:Math.round(cant), peso_g:isFinite(pes)?pes:null, cpm:isFinite(cpm)?cpm:null });
      }
    }
    if(prods.length===0) return warn('Debes ingresar al menos un producto válido.');

    // MP
    const mpItems=[];
    document.querySelectorAll('#tablaMP tbody tr.row-mp').forEach(rr=>{
      const tipo = rr.getAttribute('data-tipo')||'original';
      const codigo = (rr.querySelector('.mp-codigo')||{}).value||'';
      const kg = parseFloat((rr.querySelector('.mp-kgplan')||{}).value||'0');
      if(codigo && !isNaN(kg) && kg>0) mpItems.push({ tipo, codigo, porcentaje_plan:null, kg_plan:kg, kg_real:null });
    });

    const snap = snapshot();
    const fd = new FormData(form);
    fd.append('productos', JSON.stringify(prods));
    fd.append('mp', JSON.stringify(mpItems));
    fd.set('kg_a_producir', inKgProd ? (inKgProd.value||'') : '');
    fd.set('peso_teorico_total_kg', snap.pt_kg.toFixed(3));
    fd.set('devolucion_teorica_kg',  snap.dev_kg.toFixed(3));

    try{
      const resp = await fetch('PRESENTACION/OrdenProduccion/guardar.php',{ method:'POST', body:fd });
      const data = await resp.json();
      if(!data.ok) return warn(data.msg || 'No fue posible crear la OP.');

      if(imprimir){
        window.open(URL_PDF+'?id='+encodeURIComponent(data.id), '_blank');
        return;
      }

      document.getElementById('opSuccessMsg').textContent = '¡La orden de producción se creó exitosamente!';
      document.getElementById('opIdText').textContent = 'ID de registro: ' + data.id;

      document.getElementById('btnVerOrden').onclick = ()=>{ location.href = URL_VER + '&id=' + encodeURIComponent(data.id); };
      document.getElementById('btnNuevaOP').onclick = ()=>{ location.href = URL_CREAR; };
      const btnPdf = document.getElementById('btnPdf');
      if(btnPdf){ btnPdf.onclick = ()=> window.open(URL_PDF+'?id='+encodeURIComponent(data.id), '_blank'); }

      if(window.bootstrap && bootstrap.Modal){
        new bootstrap.Modal(document.getElementById('opSuccessModal')).show();
      } else {
        if(confirm('¡La orden de producción se creó exitosamente!\n\n¿Ver orden ahora?')){
          location.href = URL_VER + '&id=' + encodeURIComponent(data.id);
        }
      }
    } catch {
      warn('Error de red: no se pudo guardar.');
    }
  }

  document.getElementById('btnGuardar')?.addEventListener('click', e=>guardar(e,false));
  document.getElementById('btnGuardarImprimir')?.addEventListener('click', e=>guardar(e,true));

  // Arranque
  addRow();
  enforceLimit();
  if(inTeoricoKg) inTeoricoKg.value='0.000';
  validateMPvsGoal();
});
</script>

<!-- Modal: OP creada -->
<div class="modal fade" id="opSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header border-0">
        <h5 class="modal-title text-success fw-bold">
          <i class="fa-solid fa-circle-check me-2"></i>Orden creada
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body pt-0">
        <p id="opSuccessMsg" class="mb-1">¡La orden de producción se creó exitosamente!</p>
        <small id="opIdText" class="text-muted"></small>
      </div>
      <div class="modal-footer border-0 d-flex justify-content-between">
        <button type="button" class="btn btn-outline-success" id="btnNuevaOP">
          <i class="fa-solid fa-plus me-1"></i> Crear otra orden
        </button>
        <button type="button" class="btn btn-success" id="btnVerOrden">
          <i class="fa-solid fa-eye me-1"></i> Ver orden
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btnPdf">
          <i class="fa-solid fa-file-pdf me-1"></i> PDF
        </button>
      </div>
    </div>
  </div>
</div>

<datalist id="dlProductos"></datalist>
