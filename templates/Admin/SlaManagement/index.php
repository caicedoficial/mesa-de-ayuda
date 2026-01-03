<?php
/**
 * @var \App\View\AppView $this
 * @var array $slaConfigurations
 */
$this->assign('title', 'Gestión de SLA');
?>

<style>
:root {
    --admin-green: #00A85E;
    --admin-orange: #CD6A15;
    --admin-blue: #0066cc;
    --admin-purple: #7C3AED;
    --gray-50: #F9FAFB;
    --gray-100: #F3F4F6;
    --gray-200: #E5E7EB;
    --gray-300: #D1D5DB;
    --gray-400: #9CA3AF;
    --gray-600: #4B5563;
    --gray-700: #374151;
    --gray-900: #111827;
    --radius-lg: 12px;
    --radius-md: 8px;
    --radius-sm: 6px;
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.sla-page {
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Page Header */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2.5rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
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
    flex-shrink: 0;
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
    line-height: 1.2;
}

.header-text p {
    color: var(--gray-600);
    font-size: 0.95rem;
    margin: 0.25rem 0 0 0;
}

/* Info Banner */
.info-banner {
    background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
    border: 1px solid #93C5FD;
    border-left: 4px solid var(--admin-blue);
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
    margin-bottom: 2rem;
    display: flex;
    gap: 1rem;
    align-items: start;
}

.info-banner i {
    color: var(--admin-blue);
    font-size: 1.25rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.info-banner-content p {
    margin: 0;
    color: var(--gray-700);
    line-height: 1.6;
}

/* SLA Sections */
.sla-section {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 2rem;
    margin-bottom: 2rem;
    transition: var(--transition);
}

.sla-section:hover {
    box-shadow: var(--shadow-lg);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-100);
}

.section-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
}

.section-icon i {
    font-size: 1.25rem;
    color: var(--gray-700);
}

.section-header h4 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 600;
    color: var(--gray-900);
}

.section-description {
    color: var(--gray-600);
    font-size: 0.9rem;
    margin: 0 0 1.5rem 0;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: var(--gray-700);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.type-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
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

.input-wrapper {
    position: relative;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    padding-right: 3rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: 1rem;
    transition: var(--transition);
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--admin-purple);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.input-suffix {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-500);
    font-size: 0.875rem;
    font-weight: 600;
    pointer-events: none;
}

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

.metric-label {
    font-size: 0.85rem;
    color: var(--gray-600);
    margin-bottom: 0.25rem;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 2px solid var(--gray-100);
}

.btn {
    padding: 0.875rem 2rem;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--admin-purple) 0%, #6D28D9 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
}

.btn-secondary {
    background: white;
    color: var(--gray-700);
    border: 2px solid var(--gray-300);
}

.btn-secondary:hover {
    background: var(--gray-50);
    border-color: var(--gray-400);
}

/* Type Cards for PQRS */
.type-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.type-card {
    background: white;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    transition: var(--transition);
}

.type-card:hover {
    border-color: var(--admin-purple);
    box-shadow: var(--shadow-md);
}

.type-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.type-card-header h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-900);
}
</style>

<div class="sla-page">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <div class="header-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="header-text">
                <h3>Gestión de SLA</h3>
                <p>Configuración de tiempos de respuesta y resolución</p>
            </div>
        </div>
        <a href="<?= $this->Url->build(['action' => 'preview']) ?>" class="btn btn-secondary">
            <i class="bi bi-eye"></i>
            Vista Previa
        </a>
    </div>

    <!-- Info Banner -->
    <div class="info-banner">
        <i class="bi bi-info-circle-fill"></i>
        <div class="info-banner-content">
            <p><strong>¿Qué es SLA?</strong> Service Level Agreement (Acuerdo de Nivel de Servicio) define los tiempos máximos para primera respuesta y resolución de solicitudes. Los cambios se aplican automáticamente a nuevas solicitudes.</p>
        </div>
    </div>

    <?= $this->Form->create(null, ['url' => ['action' => 'save']]) ?>

    <!-- PQRS Section -->
    <div class="sla-section">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-chat-square-text"></i>
            </div>
            <h4>PQRS (Peticiones, Quejas, Reclamos y Sugerencias)</h4>
        </div>
        <p class="section-description">
            Cada tipo de PQRS tiene tiempos de SLA diferenciados según su criticidad y naturaleza.
        </p>

        <div class="type-cards">
            <!-- Petición -->
            <div class="type-card">
                <div class="type-card-header">
                    <h5>Petición</h5>
                    <span class="type-badge peticion">Petición</span>
                </div>
                <div class="form-group">
                    <label class="metric-label">Primera Respuesta</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_peticion_first_response_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['peticion']['first_response_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="metric-label">Resolución</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_peticion_resolution_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['peticion']['resolution_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
            </div>

            <!-- Queja -->
            <div class="type-card">
                <div class="type-card-header">
                    <h5>Queja</h5>
                    <span class="type-badge queja">Queja</span>
                </div>
                <div class="form-group">
                    <label class="metric-label">Primera Respuesta</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_queja_first_response_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['queja']['first_response_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="metric-label">Resolución</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_queja_resolution_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['queja']['resolution_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
            </div>

            <!-- Reclamo -->
            <div class="type-card">
                <div class="type-card-header">
                    <h5>Reclamo</h5>
                    <span class="type-badge reclamo">Reclamo</span>
                </div>
                <div class="form-group">
                    <label class="metric-label">Primera Respuesta</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_reclamo_first_response_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['reclamo']['first_response_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="metric-label">Resolución</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_reclamo_resolution_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['reclamo']['resolution_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
            </div>

            <!-- Sugerencia -->
            <div class="type-card">
                <div class="type-card-header">
                    <h5>Sugerencia</h5>
                    <span class="type-badge sugerencia">Sugerencia</span>
                </div>
                <div class="form-group">
                    <label class="metric-label">Primera Respuesta</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_sugerencia_first_response_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['sugerencia']['first_response_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="metric-label">Resolución</label>
                    <div class="input-wrapper">
                        <?= $this->Form->control('sla_pqrs_sugerencia_resolution_days', [
                            'type' => 'number',
                            'min' => 1,
                            'value' => $slaConfigurations['pqrs']['sugerencia']['resolution_days'],
                            'class' => 'form-control',
                            'label' => false,
                            'required' => true
                        ]) ?>
                        <span class="input-suffix">días</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compras Section -->
    <div class="sla-section">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-cart3"></i>
            </div>
            <h4>Compras (Solicitudes de Compra)</h4>
        </div>
        <p class="section-description">
            Tiempos estándar de respuesta y resolución para todas las solicitudes de compra.
        </p>

        <div class="form-grid">
            <div class="form-group">
                <label>Primera Respuesta</label>
                <div class="input-wrapper">
                    <?= $this->Form->control('sla_compras_first_response_days', [
                        'type' => 'number',
                        'min' => 1,
                        'value' => $slaConfigurations['compras']['first_response_days'],
                        'class' => 'form-control',
                        'label' => false,
                        'required' => true
                    ]) ?>
                    <span class="input-suffix">días</span>
                </div>
            </div>
            <div class="form-group">
                <label>Resolución</label>
                <div class="input-wrapper">
                    <?= $this->Form->control('sla_compras_resolution_days', [
                        'type' => 'number',
                        'min' => 1,
                        'value' => $slaConfigurations['compras']['resolution_days'],
                        'class' => 'form-control',
                        'label' => false,
                        'required' => true
                    ]) ?>
                    <span class="input-suffix">días</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons pb-3">
        <?= $this->Form->button('<i class="bi bi-check-circle-fill"></i> Guardar Configuración', [
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'escapeTitle' => false
        ]) ?>
        <a href="<?= $this->Url->build(['controller' => 'Admin/Settings', 'action' => 'index']) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>
            Volver a Configuración
        </a>
    </div>

    <?= $this->Form->end() ?>
</div>
