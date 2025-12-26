<?php
/**
 * Date Range Filter Form
 *
 * @var array $filters Current filters
 * @var string $action Action name to submit to
 */

$dateRange = $filters['date_range'] ?? 'all';
$startDate = $filters['start_date'] ?? '';
$endDate = $filters['end_date'] ?? '';
?>

<div class="card rounded-0 border-0 mb-4">
    <div class="card-body p-0">
        <form method="get" action="<?= $this->Url->build(['action' => $action]) ?>" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Período</label>
                <select name="range" class="form-select rounded-0" id="date-range-select">
                    <option value="all" <?= $dateRange === 'all' ? 'selected' : '' ?>>Todo el tiempo</option>
                    <option value="today" <?= $dateRange === 'today' ? 'selected' : '' ?>>Hoy</option>
                    <option value="week" <?= $dateRange === 'week' ? 'selected' : '' ?>>Últimos 7 días</option>
                    <option value="month" <?= $dateRange === 'month' ? 'selected' : '' ?>>Últimos 30 días</option>
                    <option value="custom" <?= $dateRange === 'custom' ? 'selected' : '' ?>>Rango personalizado</option>
                </select>
            </div>
            <div class="col-md-3" id="start-date-field" style="display: <?= $dateRange === 'custom' ? 'block' : 'none' ?>;">
                <label class="form-label fw-bold">Desde</label>
                <input type="date" name="start_date" class="form-control rounded-0" value="<?= h($startDate) ?>">
            </div>
            <div class="col-md-3" id="end-date-field" style="display: <?= $dateRange === 'custom' ? 'block' : 'none' ?>;">
                <label class="form-label fw-bold">Hasta</label>
                <input type="date" name="end_date" class="form-control rounded-0" value="<?= h($endDate) ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary rounded-0 w-100">
                    <i class="bi bi-funnel me-1"></i> Aplicar Filtro
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const rangeSelect = document.getElementById('date-range-select');
    const startDateField = document.getElementById('start-date-field');
    const endDateField = document.getElementById('end-date-field');

    if (rangeSelect) {
        rangeSelect.addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            startDateField.style.display = isCustom ? 'block' : 'none';
            endDateField.style.display = isCustom ? 'block' : 'none';
        });
    }
})();
</script>
