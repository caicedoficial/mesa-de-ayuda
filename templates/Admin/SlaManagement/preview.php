<?php
/**
 * @var \App\View\AppView $this
 * @var array $pqrsPreview
 * @var array $comprasPreview
 * @var \Cake\I18n\DateTime $now
 */
$this->assign('title', 'Vista Previa SLA');
?>

<style>
:root {
    --admin-purple: #7C3AED;
    --gray-50: #F9FAFB;
    --gray-100: #F3F4F6;
    --gray-200: #E5E7EB;
    --gray-600: #4B5563;
    --gray-700: #374151;
    --gray-900: #111827;
    --radius-lg: 12px;
    --radius-md: 8px;
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.preview-page {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%);
    border: 2px solid var(--admin-purple);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-icon i {
    font-size: 28px;
    color: var(--admin-purple);
}

.header-text h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0;
}

.header-text p {
    color: var(--gray-600);
    margin: 0.25rem 0 0 0;
}

.preview-card {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 2rem;
    margin-bottom: 2rem;
}

.card-header {
    border-bottom: 2px solid var(--gray-100);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.card-header h4 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--gray-900);
}

.timestamp-info {
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 1rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.timestamp-info i {
    color: var(--gray-600);
    font-size: 1.25rem;
}

.timestamp-info strong {
    color: var(--gray-900);
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.preview-item {
    background: var(--gray-50);
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 1.25rem;
}

.preview-item h5 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-900);
}

.preview-metric {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.preview-metric:last-child {
    margin-bottom: 0;
}

.metric-label {
    font-size: 0.85rem;
    color: var(--gray-600);
    font-weight: 500;
}

.metric-value {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-900);
    font-family: 'Courier New', monospace;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.6rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.type-badge.peticion {
    background: #DBEAFE;
    color: #1E40AF;
}

.type-badge.queja {
    background: #FEE2E2;
    color: #991B1B;
}

.type-badge.reclamo {
    background: #FED7AA;
    color: #9A3412;
}

.type-badge.sugerencia {
    background: #D1FAE5;
    color: #065F46;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    border: 2px solid var(--gray-300);
    background: white;
    color: var(--gray-700);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.btn:hover {
    background: var(--gray-50);
    border-color: var(--gray-400);
}
</style>

<div class="preview-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-icon">
            <i class="bi bi-eye"></i>
        </div>
        <div class="header-text">
            <h3>Vista Previa de Cálculos SLA</h3>
            <p>Ejemplo de fechas límite calculadas desde ahora</p>
        </div>
    </div>

    <!-- Timestamp Info -->
    <div class="timestamp-info">
        <i class="bi bi-calendar-event"></i>
        <div>
            <strong>Fecha/Hora de Creación:</strong> <?= $now->i18nFormat('EEEE, dd \'de\' MMMM \'de\' yyyy \'a las\' HH:mm:ss') ?>
        </div>
    </div>

    <!-- PQRS Preview -->
    <div class="preview-card">
        <div class="card-header">
            <h4><i class="bi bi-chat-square-text"></i> PQRS - Fechas Límite por Tipo</h4>
        </div>
        <div class="preview-grid">
            <?php foreach ($pqrsPreview as $type => $deadlines): ?>
                <div class="preview-item">
                    <h5>
                        <?= ucfirst($type) ?>
                        <span class="type-badge <?= $type ?>"><?= strtoupper($type) ?></span>
                    </h5>
                    <div class="preview-metric">
                        <span class="metric-label">Primera Respuesta</span>
                        <span class="metric-value"><?= $deadlines['first_response'] ?></span>
                    </div>
                    <div class="preview-metric">
                        <span class="metric-label">Resolución</span>
                        <span class="metric-value"><?= $deadlines['resolution'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Compras Preview -->
    <div class="preview-card">
        <div class="card-header">
            <h4><i class="bi bi-cart3"></i> Compras - Fechas Límite</h4>
        </div>
        <div class="preview-grid">
            <div class="preview-item">
                <h5>Solicitud de Compra Estándar</h5>
                <div class="preview-metric">
                    <span class="metric-label">Primera Respuesta</span>
                    <span class="metric-value"><?= $comprasPreview['first_response'] ?></span>
                </div>
                <div class="preview-metric">
                    <span class="metric-label">Resolución</span>
                    <span class="metric-value"><?= $comprasPreview['resolution'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div style="margin-top: 2rem;">
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn">
            <i class="bi bi-arrow-left"></i>
            Volver a Configuración SLA
        </a>
    </div>
</div>
