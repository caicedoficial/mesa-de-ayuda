<!-- Left Sidebar - PQRS Info (with independent scroll) -->
<div class="sidebar-left d-flex flex-column p-3">
    <div class="sidebar-scroll flex-grow-1 overflow-auto p-3 shadow-sm bg-white" style="border-radius: 8px;">
        <section class="mb-4">
            <h3 class="fs-6 fw-semibold mb-3">Información del PQRS</h3>

            <div class="mb-3">
                <label class="small text-muted fw-semibold mb-1">Tipo:</label>
                <div><?= $this->Pqrs->typeBadge($pqrs->type) ?></div>
            </div>

            <div class="mb-3">
                <label class="small text-muted text-muted fw-semibold mb-1">Estado:</label>
                <div><?= $this->Pqrs->statusBadge($pqrs->status) ?></div>
            </div>

            <div class="mb-3">
                <label class="small text-muted text-muted fw-semibold mb-1">Prioridad:</label>
                <?= $this->Form->create(null, ['url' => ['action' => 'changePriority', $pqrs->id], 'class' => 'm-0']) ?>
                <?= $this->Form->select('priority', [
                    'baja' => '🟢 Baja',
                    'media' => '🟡 Media',
                    'alta' => '🟠 Alta',
                    'urgente' => '🔴 Urgente'
                ], [
                    'value' => $pqrs->priority,
                    'class' => 'form-select form-select-sm',
                    'onchange' => 'this.form.submit()'
                ]) ?>
                <?= $this->Form->end() ?>
            </div>

            <div>
                <label class="small text-muted text-muted fw-semibold">Canal:</label>
                <div class="small text-uppercase"><?= h($pqrs->channel) ?></div>
            </div>
        </section>

        <section class="mb-4">
            <h3 class="fs-6 fw-semibold mb-3">Asignación</h3>
            <?= $this->Form->create(null, ['url' => ['action' => 'assign', $pqrs->id], 'class' => 'm-0', 'id' => 'assign-form']) ?>
            <?= $this->Form->select('assignee_id', $agents, [
                'empty' => '-- Sin asignar --',
                'value' => $pqrs->assignee_id,
                'class' => 'form-select form-select-sm',
                'id' => 'agent-select'
            ]) ?>
            <?= $this->Form->end() ?>
        </section>
    </div>
</div>