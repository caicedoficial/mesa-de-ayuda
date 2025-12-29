# Diseño: Estadísticas con Neumorphism Suave e Interactividad Equilibrada

**Fecha**: 2025-12-27
**Módulos afectados**: Tickets, PQRS, Compras
**Tipo**: Mejora incremental de UI/UX
**Tiempo estimado**: 4 horas

## Resumen Ejecutivo

Rediseño visual de la sección de estadísticas del sistema de helpdesk aplicando neumorphism suave con interactividad equilibrada. El enfoque es **moderno, minimalista e interactivo** manteniendo la estructura de código existente (mejora incremental, no reemplazo).

### Objetivos

- ✅ Diseño más moderno y distintivo (no genérico Bootstrap)
- ✅ Mayor interactividad con animaciones sutiles
- ✅ Estética neumorphic con tonos claros neutrales
- ✅ Mantener compatibilidad con los 3 módulos existentes
- ✅ Zero breaking changes (mejora incremental)

### Características Clave

- **Neumorphism suave**: Sombras duales para efecto de profundidad
- **Microinteracciones**: Counter animations, hover effects, scroll-triggered animations
- **Transiciones fluidas**: Loading states, fade-ins, stagger effects
- **Solo desktop**: Optimizado para pantallas ≥1200px
- **Accesible**: Respeta `prefers-reduced-motion`

---

## 1. Arquitectura y Estructura de Archivos

### Nuevos Archivos

```
webroot/css/
├── neumorphic-statistics.css          # Estilos neumorphic + variables CSS

webroot/js/
├── statistics-animations.js           # Animaciones: counters, charts, scroll
```

### Archivos Modificados

```
templates/layout/
├── admin.php                          # Agregar CSS/JS
├── agent.php                          # Agregar CSS/JS
├── compras.php                        # Agregar CSS/JS
├── servicio_cliente.php               # Agregar CSS/JS

templates/Element/shared/statistics/
├── kpi_cards.php                      # Agregar clases neumorphic
├── status_chart.php                   # Agregar wrapper + skeleton
├── priority_chart.php                 # Agregar wrapper + skeleton
├── trend_chart.php                    # Agregar wrapper + skeleton

templates/Element/Tickets/
├── response_metrics.php               # Aplicar estilos neumorphic

templates/Element/shared/statistics/
├── agent_performance_table.php        # Animaciones de entrada
```

### Estrategia de Integración

**Approach no-invasivo:**
- Agregar clases neumorphic **junto a** clases Bootstrap existentes (no reemplazar)
- CSS nuevo tiene mayor especificidad para override selectivo
- JavaScript es progresivo (funciona sin él, mejor con él)
- Fácil rollback: solo comentar 2 líneas en layouts

---

## 2. Sistema de Diseño: Variables CSS y Neumorphism

### Paleta de Colores (Tonos Claros Neutrales)

```css
:root {
  /* Base neumorphic */
  --neuro-bg: #e0e5ec;
  --neuro-surface: #e0e5ec;

  /* Sombras neumorphic (clave del efecto) */
  --neuro-shadow-light: #ffffff;
  --neuro-shadow-dark: #a3b1c6;

  /* Sombras para elevación */
  --neuro-shadow-sm:
    4px 4px 8px var(--neuro-shadow-dark),
    -4px -4px 8px var(--neuro-shadow-light);

  --neuro-shadow-md:
    8px 8px 16px var(--neuro-shadow-dark),
    -8px -8px 16px var(--neuro-shadow-light);

  --neuro-shadow-lg:
    12px 12px 24px var(--neuro-shadow-dark),
    -12px -12px 24px var(--neuro-shadow-light);

  /* Sombra inset (para elementos presionados) */
  --neuro-shadow-inset:
    inset 4px 4px 8px var(--neuro-shadow-dark),
    inset -4px -4px 8px var(--neuro-shadow-light);

  /* Acentos de color (para iconos y gráficos) */
  --neuro-primary: #5e72e4;
  --neuro-success: #2dce89;
  --neuro-warning: #fb6340;
  --neuro-danger: #f5365c;
  --neuro-info: #11cdef;

  /* Texto */
  --neuro-text-primary: #32325d;
  --neuro-text-secondary: #8898aa;
  --neuro-text-muted: #adb5bd;

  /* Transiciones suaves */
  --neuro-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  --neuro-transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
```

### Cómo Funciona el Neumorphism

El efecto se logra con **dos sombras simultáneas**:
- **Sombra oscura** (#a3b1c6, abajo-derecha) → crea profundidad
- **Sombra clara** (#ffffff, arriba-izquierda) → simula luz

Los elementos parecen **"salir"** del fondo porque comparten el mismo color base (#e0e5ec), diferenciándose solo por las sombras.

### Clases Base

```css
.neuro-card {
  background: var(--neuro-surface);
  border-radius: 16px;
  box-shadow: var(--neuro-shadow-md);
  border: none;
  transition: var(--neuro-transition);
}

.neuro-card-sm {
  box-shadow: var(--neuro-shadow-sm);
  border-radius: 12px;
}

.neuro-hover:hover {
  box-shadow: var(--neuro-shadow-lg);
  transform: translateY(-4px);
}
```

---

## 3. Componentes: KPI Cards

### HTML Actualizado

```php
<!-- templates/Element/shared/statistics/kpi_cards.php -->
<div class="col-md-3">
    <div class="card neuro-card neuro-hover" data-animate-in="fade-up">
        <div class="card-body text-center py-4">
            <!-- Ícono con efecto neuro inset -->
            <div class="neuro-icon-wrapper mb-3">
                <i class="bi <?= $icon ?> neuro-icon"
                   style="color: var(--neuro-primary);"></i>
            </div>

            <!-- Counter animado -->
            <h3 class="neuro-counter mb-2"
                data-counter
                data-target="<?= $total ?>"
                aria-live="polite"
                aria-atomic="true">0</h3>

            <p class="neuro-label mb-0">Total <?= h($label) ?></p>
        </div>
    </div>
</div>
```

### CSS Específico

```css
/* Card container */
.neuro-card {
  background: var(--neuro-surface);
  box-shadow: var(--neuro-shadow-md);
  border-radius: 16px;
  border: none;
  padding: 1.5rem;
  transition: var(--neuro-transition);
}

/* Hover effect - lift suave */
.neuro-hover:hover {
  box-shadow: var(--neuro-shadow-lg);
  transform: translateY(-6px);
}

/* Ícono con efecto inset */
.neuro-icon-wrapper {
  width: 80px;
  height: 80px;
  margin: 0 auto;
  border-radius: 50%;
  background: var(--neuro-surface);
  box-shadow: var(--neuro-shadow-inset);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--neuro-transition);
}

.neuro-icon {
  font-size: 2.5rem;
  transition: var(--neuro-transition);
}

.neuro-hover:hover .neuro-icon {
  transform: scale(1.1);
}

/* Counter número grande */
.neuro-counter {
  font-size: 2.5rem;
  font-weight: 300;
  color: var(--neuro-text-primary);
  letter-spacing: -0.02em;
}

/* Label texto */
.neuro-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--neuro-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Animación de entrada */
[data-animate-in="fade-up"] {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}

[data-animate-in="fade-up"].animated {
  opacity: 1;
  transform: translateY(0);
}
```

### Efecto Visual

- Cards que "flotan" sobre el fondo con sombras sutiles
- Ícono en círculo hundido (inset shadow) → profundidad
- Hover eleva la card (+6px con sombra más grande)
- Ícono escala ligeramente al hover
- Números grandes con tipografía minimalista

---

## 4. Componentes: Charts con Loading Animations

### HTML para Charts

```php
<!-- Ejemplo: status_chart.php -->
<div class="neuro-card neuro-chart-container" data-animate-in="fade-up" data-delay="200">
    <!-- Header del chart -->
    <div class="neuro-chart-header mb-3">
        <h5 class="neuro-chart-title">
            <i class="bi bi-pie-chart me-2" style="color: var(--neuro-info);"></i>
            Estado de <?= ucfirst($entityType) ?>s
        </h5>
    </div>

    <!-- Canvas con loading overlay -->
    <div class="neuro-chart-wrapper" data-chart-loader>
        <!-- Skeleton loader -->
        <div class="neuro-chart-skeleton">
            <div class="skeleton-circle"></div>
        </div>

        <!-- Canvas real -->
        <canvas id="statusChart"
                data-chart="status"
                style="opacity: 0; transition: opacity 0.5s ease;"></canvas>
    </div>
</div>
```

### CSS para Charts

```css
/* Chart card container */
.neuro-chart-container {
  background: var(--neuro-surface);
  box-shadow: var(--neuro-shadow-md);
  border-radius: 16px;
  padding: 1.5rem;
  height: 100%;
  transition: var(--neuro-transition);
}

.neuro-chart-container:hover {
  box-shadow: var(--neuro-shadow-lg);
}

/* Chart header */
.neuro-chart-header {
  border-bottom: 1px solid rgba(163, 177, 198, 0.15);
  padding-bottom: 0.75rem;
}

.neuro-chart-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--neuro-text-primary);
  margin: 0;
  display: flex;
  align-items: center;
}

/* Chart wrapper */
.neuro-chart-wrapper {
  position: relative;
  min-height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Skeleton loader (shimmer effect) */
.neuro-chart-skeleton {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.3s ease;
}

.neuro-chart-skeleton.hidden {
  opacity: 0;
  pointer-events: none;
}

.skeleton-circle {
  width: 180px;
  height: 180px;
  border-radius: 50%;
  background: linear-gradient(
    90deg,
    var(--neuro-surface) 0%,
    #d0d5dd 50%,
    var(--neuro-surface) 100%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  box-shadow: var(--neuro-shadow-inset);
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Canvas visible después de cargar */
canvas.loaded {
  opacity: 1 !important;
}
```

### Customización Chart.js

```javascript
// Configuración global para neumorphism
Chart.defaults.font.family = "'Inter', -apple-system, sans-serif";
Chart.defaults.color = '#8898aa';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.padding = 15;

const neuroChartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: '#32325d',
        font: { size: 12, weight: '500' }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(224, 229, 236, 0.95)',
      titleColor: '#32325d',
      bodyColor: '#8898aa',
      borderColor: '#a3b1c6',
      borderWidth: 1,
      cornerRadius: 8,
      padding: 12
    }
  },
  animation: {
    duration: 1500,
    easing: 'easeInOutQuart'
  }
};
```

---

## 5. JavaScript: Counter Animations e Interacciones

### Counter Animation

```javascript
// statistics-animations.js

// Counter con easing
function animateCounter(element, target, duration = 2000) {
  const start = 0;
  const increment = target / (duration / 16);
  let current = start;

  const timer = setInterval(() => {
    current += increment;

    if (current >= target) {
      element.textContent = formatNumber(target);
      clearInterval(timer);
    } else {
      element.textContent = formatNumber(Math.floor(current));
    }
  }, 16);
}

function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
```

### Intersection Observer

```javascript
const observerOptions = {
  threshold: 0.2,
  rootMargin: '0px 0px -100px 0px'
};

const animateOnScroll = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const element = entry.target;

      // Animar counters
      if (element.hasAttribute('data-counter')) {
        const target = parseInt(element.dataset.target);
        animateCounter(element, target);
        animateOnScroll.unobserve(element);
      }

      // Animar entrada (fade-up)
      if (element.hasAttribute('data-animate-in')) {
        const delay = parseInt(element.dataset.delay || 0);
        setTimeout(() => {
          element.classList.add('animated');
        }, delay);
        animateOnScroll.unobserve(element);
      }
    }
  });
}, observerOptions);
```

### Hover Parallax Effect

```javascript
document.querySelectorAll('.neuro-hover').forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotateX = (y - centerY) / 20;
    const rotateY = (centerX - x) / 20;

    card.style.transform = `
      perspective(1000px)
      rotateX(${rotateX}deg)
      rotateY(${rotateY}deg)
      translateY(-6px)
    `;
  });

  card.addEventListener('mouseleave', () => {
    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
  });
});
```

---

## 6. JavaScript: Chart Loading y Scroll Effects

### Chart Loader Class

```javascript
class ChartLoader {
  constructor(canvasId, chartConfig) {
    this.canvas = document.getElementById(canvasId);
    this.wrapper = this.canvas.closest('[data-chart-loader]');
    this.skeleton = this.wrapper.querySelector('.neuro-chart-skeleton');
    this.config = chartConfig;
  }

  async load() {
    await this.delay(800); // UX delay

    const chart = new Chart(this.canvas, this.config);
    await this.delay(300);

    // Skeleton → canvas
    this.skeleton.classList.add('hidden');
    this.canvas.classList.add('loaded');

    return chart;
  }

  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}
```

### Inicializar Charts con Stagger

```javascript
async function initializeCharts() {
  const charts = [
    new ChartLoader('statusChart', { /* config */ }),
    new ChartLoader('priorityChart', { /* config */ }),
    new ChartLoader('trendChart', { /* config */ })
  ];

  // Stagger: 250ms entre cada chart
  for (let i = 0; i < charts.length; i++) {
    setTimeout(() => charts[i].load(), i * 250);
  }
}
```

### Animar Tablas

```javascript
function animateTableRows(tableSelector) {
  const rows = document.querySelectorAll(`${tableSelector} tbody tr`);

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const row = entry.target;
        const index = Array.from(rows).indexOf(row);

        setTimeout(() => {
          row.style.opacity = '1';
          row.style.transform = 'translateX(0)';
        }, index * 50);

        observer.unobserve(row);
      }
    });
  }, { threshold: 0.1 });

  rows.forEach(row => {
    row.style.opacity = '0';
    row.style.transform = 'translateX(-20px)';
    row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    observer.observe(row);
  });
}
```

### Secuencia Visual Completa

1. **Page load** → KPI cards fade-up (stagger 100ms)
2. **Counters** → Números cuentan desde 0 (2s)
3. **Charts** → Skeleton shimmer → fade-in (stagger 250ms)
4. **Tablas** → Filas aparecen progresivamente (stagger 50ms)
5. **Hover** → Parallax sutil en cards

---

## 7. Optimización Desktop y Accesibilidad

### Desktop Optimization

```css
@media (min-width: 1200px) {
  .neuro-card {
    padding: 2rem;
  }

  .neuro-counter {
    font-size: 3rem;
  }
}

.statistics-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem 3rem;
}
```

### Accesibilidad

```css
/* Respetar preferencia de movimiento reducido */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }

  .neuro-hover:hover {
    transform: none !important;
  }
}
```

```javascript
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function animateCounter(element, target, duration = 2000) {
  if (prefersReducedMotion) {
    element.textContent = formatNumber(target);
    return;
  }
  // Animación normal...
}
```

### Focus Visible

```css
.neuro-card:focus-visible {
  outline: 3px solid var(--neuro-primary);
  outline-offset: 4px;
}
```

### Browser Compatibility

- ✅ Chrome/Edge 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ❌ IE11 NO soportado

---

## 8. Plan de Implementación

### Fase 1: Fundación CSS (30 min)

1. Crear `webroot/css/neumorphic-statistics.css`
2. Definir variables CSS (`:root`)
3. Crear clases base
4. Agregar a `templates/layout/admin.php` (testing)

### Fase 2: KPI Cards (45 min)

1. Actualizar `templates/Element/shared/statistics/kpi_cards.php`
   - Agregar clases neumorphic
   - Agregar `.neuro-icon-wrapper`
   - Agregar data-attributes
2. Verificar en `/tickets/statistics`

### Fase 3: Charts (1 hora)

1. Actualizar elementos de charts
   - Wrapper `.neuro-chart-container`
   - Skeleton loaders
2. Crear `webroot/js/statistics-animations.js`
3. Implementar `ChartLoader`
4. Configurar `neuroChartOptions`

### Fase 4: JavaScript Animations (1 hora)

1. Counter animations con Intersection Observer
2. Fade-in animations
3. Chart loading sequence
4. Hover parallax effects
5. Testing cross-browser

### Fase 5: Tablas y Extras (30 min)

1. Estilos neumorphic a tablas
2. Animar filas con scroll-trigger
3. Ajustar comentarios statistics cards

### Fase 6: Rollout Completo (30 min)

1. Agregar CSS/JS a todos los layouts
2. Testing en 3 módulos
3. Verificar accesibilidad
4. Performance audit (Lighthouse)

### Testing Checklist

```
□ KPI cards con sombras neumorphic correctas
□ Counters animan desde 0
□ Charts: skeleton → fade-in smooth
□ Hover effects (lift + parallax)
□ Scroll animations correctas
□ prefers-reduced-motion respetado
□ Navegación por teclado funciona
□ Performance 90+ en Lighthouse
□ Chrome, Firefox, Safari
□ 3 módulos consistentes
```

### Rollback Strategy

Comentar 2 líneas en layouts:
```php
// <?= $this->Html->css('neumorphic-statistics') ?>
// <?= $this->Html->script('statistics-animations') ?>
```

---

## Tiempo Total Estimado

**~4 horas** de implementación completa

---

## Referencias

- Variables CSS: https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties
- Intersection Observer: https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
- Chart.js: https://www.chartjs.org/docs/latest/
- Neumorphism: https://neumorphism.io/
- Accesibilidad: https://www.w3.org/WAI/WCAG21/quickref/
