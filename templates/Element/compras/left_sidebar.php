<!-- Left Sidebar - Compra Info (with independent scroll) -->
<div class="sidebar-left d-flex flex-column p-3">
    <div class="sidebar-scroll flex-grow-1 overflow-auto p-3 shadow-sm bg-white" style="border-radius: 8px;">
        <section class="mb-3">
            <h3 class="fs-6 fw-semibold mb-3">Información de la Compra</h3>

            <div class="mb-3">
                <label class="small text-muted fw-semibold mb-1">Estado:</label>
                <div><?= $this->Compras->statusBadge($compra->status) ?></div>
            </div>

            <div class="mb-3">
                <label class="small text-muted fw-semibold mb-1">Prioridad:</label>
                <?= $this->Form->create(null, ['url' => ['action' => 'changePriority', $compra->id], 'class' => '']) ?>
                <?= $this->Form->select('priority', [
                    'baja' => '🟢 Baja',
                    'media' => '🟡 Media',
                    'alta' => '🟠 Alta',
                    'urgente' => '🔴 Urgente'
                ], [
                    'value' => $compra->priority,
                    'class' => 'form-select form-select-sm',
                    'onchange' => 'this.form.submit()'
                ]) ?>
                <?= $this->Form->end() ?>
            </div>

            <div class="mb-3">
                <label class="small text-muted fw-semibold mb-1">SLA:</label>
                <div><?= $this->Compras->slaIndicator($compra, true) ?></div>
            </div>
        </section>

        <section class="mb-3">
            <h3 class="small text-muted fw-semibold mb-1">Asignación:</h3>
            <?= $this->Form->create(null, ['url' => ['action' => 'assign', $compra->id], 'class' => 'm-0', 'id' => 'assign-form']) ?>
            <?= $this->Form->select('agent_id', $comprasUsers, [
                'empty' => '-- Sin asignar --',
                'value' => $compra->assignee_id,
                'class' => 'form-select form-select-sm',
                'id' => 'agent-select',
                'disabled' => $this->User->isAssignmentDisabled($user),
            ]) ?>
            <?= $this->Form->end() ?>
        </section>
    </div>
</div>
