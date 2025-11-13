// resources/js/dashboard.js
import ApexCharts from 'apexcharts';

// ======================
// Helpers globales
// ======================
const DEBUG = true;
const log = (...a) => { if (DEBUG) console.log(...a); };

function $(sel) { return document.querySelector(sel); }

function renderEmpty(el, msg = 'Sin datos en este rango') {
  const node = typeof el === 'string' ? $(el) : el;
  if (!node) return false;
  node.innerHTML = `<div class="h-64 flex items-center justify-center text-gray-500 text-sm">${msg}</div>`;
  return true;
}







function isAllZero(arr){ return !arr || arr.length===0 || arr.every(v => Number(v||0) === 0); }

function safeRender(selector, options) {
  const node = $(selector);
  if (!node) { log('No existe el contenedor:', selector); return; }
  const chart = new ApexCharts(node, options);
  chart.render();
}

function ensurePercentFromTotals(rows, keyTotal='total', keyPct='pct') {
  // Si el backend no mandó pct, lo calculamos aquí
  const sum = rows.reduce((acc, r) => acc + Number(r[keyTotal] || 0), 0) || 1;
  rows.forEach(r => { if (r[keyPct] === undefined) r[keyPct] = Math.round((Number(r[keyTotal]||0)/sum)*1000)/10; });
  return rows;
}

// ======================
// Datos desde Blade
// (asegúrate que window.DASH exista en la vista)
// ======================
const DASH = window.DASH || {};
log('DASH:', DASH);













function paintDelta(el, delta) {
  const node = typeof el === 'string' ? document.querySelector(el) : el;
  if (!node) return;
  if (delta === null || delta === undefined) {
    node.textContent = '—';
    node.className = 'text-sm text-gray-400';
    return;
  }
  const sign = delta > 0 ? '+' : '';
  node.textContent = `${sign}${delta}%`;
  node.className = 'text-sm ' + (delta >= 0 ? 'text-green-600' : 'text-red-600');
}

(function renderKPIs(){
  const k = (window.DASH && window.DASH.kpis) || {};
  const nf = new Intl.NumberFormat('es-HN');

  const ventas    = typeof k.ventas === 'number' ? k.ventas : 0;
  const unidades  = typeof k.unidades === 'number' ? k.unidades : 0;

  const ventasEl   = document.querySelector('#kpi-ventas');
  const unidadesEl = document.querySelector('#kpi-unidades');

  if (ventasEl)   ventasEl.textContent   = nf.format(ventas);
  if (unidadesEl) unidadesEl.textContent = nf.format(unidades);

  paintDelta('#kpi-ventas-delta',   k.ventas_delta);
  paintDelta('#kpi-unidades-delta', k.unidades_delta);
})();







(function () {
  const data = window.DASH?.salesByDay ?? {labels:[], tickets:[], amount:[], units:[]};

  const el = document.querySelector('#chartByDay');
  if (!el) return;

  let current = 'tickets'; // default

  const options = {
    chart: { type: 'bar', height: 320, toolbar: {show:false} },
    plotOptions: { bar: { horizontal: false, borderRadius: 6 } },
    dataLabels: { enabled: true },
    xaxis: { categories: data.labels },
    series: [{ name: 'Tickets', data: data.tickets }],
    tooltip: { y: { formatter: (val) => current==='amount' ? `L ${val.toFixed(2)}` : val } },
    yaxis: { labels: { formatter: (val) => current==='amount' ? `L ${Number(val).toFixed(0)}` : val } }
  };

  const chart = new ApexCharts(el, options);
  chart.render();

  function setMode(mode){
    current = mode;
    if (mode === 'tickets') {
      chart.updateSeries([{ name: 'Tickets', data: data.tickets }]);
    } else if (mode === 'amount') {
      chart.updateSeries([{ name: 'Monto (L)', data: data.amount }]);
    } else {
      chart.updateSeries([{ name: 'Unidades', data: data.units }]);
    }
  }

  document.querySelector('#btnTickets')?.addEventListener('click', ()=>setMode('tickets'));
  document.querySelector('#btnAmount') ?.addEventListener('click', ()=>setMode('amount'));
  document.querySelector('#btnUnits')  ?.addEventListener('click', ()=>setMode('units'));
})();









// Paleta por clase ABC
const ABC_COLORS = { A: '#10B981', B: '#F59E0B', C: '#94A3B8' }; // verde, ámbar, gris

(function renderParetoABC(){
  const data = (window.DASH && window.DASH.abcChart) || [];
  const el = document.querySelector('#chart-abc');
  if (!el || !data.length) return;

  // Barras horizontales por % del total y color por clase
  const labels = data.map(d => d.name);
  const valores = data.map(d => Number(d.pct || 0));
  const colores = data.map(d => ABC_COLORS[d.class] || '#94A3B8');

  const chart = new ApexCharts(el, {
    chart: { type: 'bar', height: 380, toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
    dataLabels: {
      enabled: true,
      formatter: (val, opts) => `${val.toFixed(1)}% · ${data[opts.dataPointIndex].class}`,
      style: { colors: ['#111827'] }
    },
    xaxis: { title: { text: '% de unidades (del total del rango)' }, max: 100 },
    colors: colores,
    series: [{ name: '% del total', data: valores }],
    tooltip: {
      y: {
        formatter: (v, opts) => {
          const i = opts.dataPointIndex;
          const it = data[i];
          return `${v.toFixed(2)}% • ${it.unidades} uds · Acum: ${it.acum}% · Clase ${it.class}`;
        }
      }
    }
  });
  chart.render();
})();

(function renderStars(){
  const data = (window.DASH && window.DASH.starsChart) || [];
  const el = document.querySelector('#chart-stars');
  if (!el || !data.length) return;

  const labels = data.map(d => d.name);
  const scores = data.map(d => Number(d.score || 0));

  const chart = new ApexCharts(el, {
    chart: { type: 'bar', height: 380, toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true, borderRadius: 6 } },
    dataLabels: { enabled: true, formatter: (v) => v.toFixed(0) },
    colors: ['#3B82F6'],
    series: [{ name: 'Puntaje (margen% × unidades)', data: scores }],
    xaxis: { title: { text: 'Puntaje (más alto = mejor combinación)' }, categories: labels },
    tooltip: {
      y: {
        formatter: (v, opts) => {
          const i = opts.dataPointIndex;
          const it = data[i];
          return `⭐ ${v} · ${it.unidades} uds · Margen ${it.margen_pct}%`;
        }
      }
    }
  });
  chart.render();
})();







// Convierte 0..23 a 'a.m./p.m.' como pediste
function hourLabel(h) {
  h = Number(h);
  if (h === 0)  return '12 a.m.';
  if (h < 12)   return (h < 10 ? '0' + h : h) + ' a.m.';
  if (h === 12) return '12 p.m.';
  const hh = h - 12;
  return (hh < 10 ? '0' + hh : hh) + ' p.m.';
}

(function renderHeatmap() {
  const H = (window.DASH && window.DASH.heatmap) || null;
  if (!H || !H.series || !Array.isArray(H.series)) return;

  // Mapeamos las horas a etiquetas a.m./p.m.
  const xCats = (H.hours || []).map(hourLabel);

  // Los puntos ya vienen como {x: 0..23, y: valor}; convertimos x a string etiqueta
  const series = H.series.map(day => ({
    name: day.name,
    data: day.data.map(p => ({ x: hourLabel(p.x), y: Number(p.y || 0) }))
  }));

  const el = document.querySelector('#chart-heatmap');
  if (!el) return;

  const options = {
    chart: { type: 'heatmap', height: 380, toolbar: { show: false } },
    dataLabels: { enabled: false },
    plotOptions: {
      heatmap: {
        shadeIntensity: 0.5,
        colorScale: {
          ranges: [
            { from: 0,  to: 0,   color: '#F1F5F9', name: 'Sin ventas' },
            { from: 1,  to: 2,   color: '#BFDBFE' },
            { from: 3,  to: 5,   color: '#93C5FD' },
            { from: 6,  to: 10,  color: '#60A5FA' },
            { from: 11, to: 999, color: '#3B82F6' }
          ]
        }
      }
    },
    xaxis: { categories: xCats, title: { text: 'Hora del día' } },
    yaxis: { title: { text: 'Día de la semana' } },
    tooltip: {
      y: {
        formatter: (val) => `${val} ventas`
      }
    },
    series
  };

  const chart = new ApexCharts(el, options);
  chart.render();
})();





// ======================
// 1) Ventas por mes (en % del total)
// ======================
(() => {
  try {
    const dataRaw = Array.isArray(DASH.salesByMonth) ? DASH.salesByMonth : [];
    const data = ensurePercentFromTotals(dataRaw, 'total', 'pct');

    const categories = data.map(d => d.ym);
    const series = data.map(d => Number(d.pct || 0));

    if (!categories.length || isAllZero(series))
      return renderEmpty('#chartMonthly');

    safeRender('#chartMonthly', {
      chart: { type: 'bar', height: 280, toolbar: { show:false } },
      series: [{ name: '% del total', data: series }],
      xaxis: { categories },
      yaxis: { labels: { formatter: v => `${v}%` }, max: 100 },
      dataLabels: { enabled: false },
      grid: { strokeDashArray: 4 },
      tooltip: {
        y: { formatter: v => `${v}%` }
      }
    });
  } catch(e){ console.error('chartMonthly error', e); renderEmpty('#chartMonthly'); }
})();

// ======================
// 2) Horas pico (a. m. / p. m.)
// ======================
(() => {
  try {
    const rows = Array.isArray(DASH.hourly) ? DASH.hourly : [];
    // rows: [{hour:'07', cnt: 3}, ...]
    const map = Object.fromEntries(rows.map(r => [Number(r.hour), Number(r.cnt||0)]));

    const labels = Array.from({length:24}, (_,i)=>{
      const h = i===0 ? 12 : i>12 ? i-12 : i;
      const suffix = i<12 ? 'a.m.' : 'p.m.';
      return `${h} ${suffix}`;
    });
    const series = Array.from({length:24}, (_,i)=> map[i] ?? 0);

    if (isAllZero(series))
      return renderEmpty('#chartHourly','Sin ventas en este rango');

    safeRender('#chartHourly', {
      chart: { type: 'line', height: 260, toolbar: { show:false } },
      xaxis: { categories: labels, title: { text: 'Hora del día' } },
      series: [{ name: 'Tickets', data: series }],
      dataLabels: { enabled: false },
      stroke: { width: 3 },
      grid: { strokeDashArray: 4 },
      tooltip: { y: { formatter: v => `${v} tickets` } }
    });
  } catch(e){ console.error('chartHourly error', e); renderEmpty('#chartHourly'); }
})();

// ======================
// 3) Más vendidos (TOP)
// ======================
(() => {
  try {
    const data = Array.isArray(DASH.topMovers) ? DASH.topMovers : [];
    const cats = data.map(d => d.product);
    const vals = data.map(d => Number(d.qty||0));

    if (!cats.length || isAllZero(vals))
      return renderEmpty('#chartTopMovers','Sin ventas en este rango');

    safeRender('#chartTopMovers', {
      chart: { type: 'bar', height: 320, toolbar: { show:false } },
      series: [{ name: 'Unidades', data: vals }],
      xaxis: { categories: cats, labels: { rotate: -30, trim: true } },
      plotOptions: { bar: { horizontal: true } },
      dataLabels: { enabled: false },
      tooltip: { y: { formatter: v => `${v} uds` } }
    });
  } catch(e){ console.error('chartTopMovers error', e); renderEmpty('#chartTopMovers'); }
})();

// ======================
// 4) Menos movimiento (BOTTOM)
// ======================
(() => {
  try {
    const data = Array.isArray(DASH.slowMovers) ? DASH.slowMovers : [];
    const cats = data.map(d => d.product);
    const vals = data.map(d => Number(d.qty||0));

    if (!cats.length || isAllZero(vals))
      return renderEmpty('#chartSlowMovers','Sin ventas en este rango');

    safeRender('#chartSlowMovers', {
      chart: { type: 'bar', height: 320, toolbar: { show:false } },
      series: [{ name: 'Unidades', data: vals }],
      xaxis: { categories: cats, labels: { rotate: -30, trim: true } },
      plotOptions: { bar: { horizontal: true } },
      dataLabels: { enabled: false },
      tooltip: { y: { formatter: v => `${v} uds` } }
    });
  } catch(e){ console.error('chartSlowMovers error', e); renderEmpty('#chartSlowMovers'); }
})();

// ======================
// 5) Mayor margen (ganancia por unidad)
// ======================
(() => {
  const data = Array.isArray(DASH.margins) ? DASH.margins : [];
  const cats = data.map(d => d.name);
  const vals = data.map(d => Number(d.margin || 0));

  if (!cats.length || vals.every(v => v === 0))
    return renderEmpty('#chartMargins','Sin productos con margen');

  new ApexCharts(document.querySelector("#chartMargins"), {
    chart: { type:'bar', height:320, toolbar:{show:false} },
    series: [{ name:'Margen % por unidad', data: vals }],
    xaxis: { categories: cats, labels: { rotate:-30, trim:true } },
    plotOptions: { bar:{ horizontal:true } },
    dataLabels: { enabled:false },
    tooltip: { y: { formatter: v => `${v}%` } }
  }).render();
})();

// ======================
// 6) Métodos de pago (porcentaje, ES)
// ======================
// ======================
// 6) Métodos de pago (porcentaje) — versión limpia según tus datos reales
// ======================
(() => {
  try {
    const data = Array.isArray(window.DASH?.paymentShare)
      ? window.DASH.paymentShare
      : [];

    // Traducción exacta según tus claves reales
    const mapES = {
      cash: "Efectivo",
      credit: "Crédito",
      transfer: "Transferencia",
      card: "Tarjeta",
    };

    // Mapeo de etiquetas y valores
    const labels = data.map((d) => {
      const key = String(d?.method ?? "").toLowerCase().trim();
      return mapES[key] ?? key;
    });

    const series = data.map((d) => Number(d?.pct ?? 0));

    // Si no hay datos o todo es 0
    if (!labels.length || series.every((v) => v === 0)) {
      return renderEmpty("#chartPayments", "Sin datos en este rango");
    }

    // Render seguro del donut
    safeRender("#chartPayments", {
      chart: { type: "donut", height: 280, toolbar: { show: false } },
      labels,
      series,
      colors: ["#1E88E5", "#43A047", "#FB8C00", "#E53935"], // Azul, Verde, Naranja, Rojo
      dataLabels: {
        enabled: true,
        formatter: (v) => `${v.toFixed(1)}%`,
      },
      legend: { position: "bottom" },
      tooltip: { y: { formatter: (v) => `${v}%` } },
    });
  } catch (e) {
    console.error("chartPayments error:", e);
    renderEmpty("#chartPayments", "No se pudo dibujar");
  }
})();





