<?php
/**
 * Top Agents Performance Table
 *
 * @var \Cake\ORM\ResultSet|array $topAgents Top agents data
 * @var string $entityType 'ticket', 'pqrs', or 'compra'
 */

$entityLabels = [
    'ticket' => 'tickets',
    'pqrs' => 'PQRS',
    'compra' => 'compras',
];
$label = $entityLabels[$entityType] ?? 'entidades';
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-trophy"></i> Top Agentes</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($topAgents) && count($topAgents) > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;" class="text-center">Ranking</th>
                            <th>Agente</th>
                            <th style="width: 100px;" class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($topAgents as $agent): ?>
                            <tr>
                                <td class="text-center">
                                    <?php if ($rank === 1): ?>
                                        <i class="bi bi-trophy-fill text-warning" style="font-size: 1.3rem;"></i>
                                    <?php elseif ($rank === 2): ?>
                                        <i class="bi bi-trophy-fill text-secondary" style="font-size: 1.2rem;"></i>
                                    <?php elseif ($rank === 3): ?>
                                        <i class="bi bi-trophy-fill text-danger" style="font-size: 1.1rem;"></i>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark"><?= $rank ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= h($agent->agent_name ?? ($agent->Assignees->first_name . ' ' . $agent->Assignees->last_name)) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?= number_format($agent->count) ?></span>
                                </td>
                            </tr>
                        <?php $rank++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">No hay datos de agentes disponibles.</p>
        <?php endif; ?>
    </div>
</div>
