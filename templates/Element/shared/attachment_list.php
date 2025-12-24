<?php
/**
 * Shared Element: Attachment List
 *
 * Renders a list of attachments with icons and download links
 * Works for Tickets, PQRS, and Compras
 *
 * @var array $attachments Array of Attachment entities
 * @var string $entityType 'ticket', 'pqrs', or 'compra'
 */

$entityType = $entityType ?? 'ticket';

// Icon mapping for file types
$iconMap = [
    'pdf' => ['icon' => 'bi-file-earmark-pdf', 'color' => 'text-danger'],
    'doc' => ['icon' => 'bi-file-earmark-word', 'color' => 'text-primary'],
    'docx' => ['icon' => 'bi-file-earmark-word', 'color' => 'text-primary'],
    'xls' => ['icon' => 'bi-file-earmark-excel', 'color' => 'text-success'],
    'xlsx' => ['icon' => 'bi-file-earmark-excel', 'color' => 'text-success'],
    'ppt' => ['icon' => 'bi-file-earmark-ppt', 'color' => 'text-warning'],
    'pptx' => ['icon' => 'bi-file-earmark-ppt', 'color' => 'text-warning'],
    'png' => ['icon' => 'bi-file-earmark-image', 'color' => 'text-success'],
    'jpg' => ['icon' => 'bi-file-earmark-image', 'color' => 'text-success'],
    'jpeg' => ['icon' => 'bi-file-earmark-image', 'color' => 'text-success'],
    'gif' => ['icon' => 'bi-file-earmark-image', 'color' => 'text-success'],
    'bmp' => ['icon' => 'bi-file-earmark-image', 'color' => 'text-success'],
    'webp' => ['icon' => 'bi-file-earmark-image', 'color' => 'text-success'],
    'zip' => ['icon' => 'bi-file-earmark-zip', 'color' => 'text-warning'],
    'rar' => ['icon' => 'bi-file-earmark-zip', 'color' => 'text-warning'],
    '7z' => ['icon' => 'bi-file-earmark-zip', 'color' => 'text-warning'],
    'txt' => ['icon' => 'bi-file-earmark-text', 'color' => 'text-secondary'],
    'csv' => ['icon' => 'bi-file-earmark-spreadsheet', 'color' => 'text-success'],
];

// Determine controller based on entity type
$controllerMap = [
    'ticket' => 'Tickets',
    'pqrs' => 'Pqrs',
    'compra' => 'Compras'
];
$controller = $controllerMap[$entityType] ?? 'Tickets';
?>

<?php if (!empty($attachments)): ?>
    <div class="mt-2">
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($attachments as $attachment): ?>
                <?php
                // Use original_filename for display, fallback to filename if not available
                $displayName = $attachment->original_filename ?? $attachment->filename;
                $ext = strtolower(pathinfo($displayName, PATHINFO_EXTENSION));
                $icon = $iconMap[$ext]['icon'] ?? 'bi-file-earmark';
                $color = $iconMap[$ext]['color'] ?? 'text-secondary';
                $sizeKB = number_format($attachment->file_size / 1024, 1);
                ?>
                <?= $this->Html->link(
                    '<i class="bi ' . $icon . ' ' . $color . ' fs-3 me-1 text-center"></i> ' .
                    '<span class="small text-truncate text-center">' . h($displayName) . '</span> ' .
                    '<span class="badge bg-light text-dark border">' . $sizeKB . ' KB</span>',
                    ['controller' => $controller, 'action' => 'downloadAttachment', $attachment->id],
                    [
                        'class' => 'd-flex justify-content-between flex-column gap-1 border rounded text-decoration-none bg-white hover-bg-light p-2',
                        'style' => 'padding: 8px; width: 125px; height: 125px;',
                        'escape' => false,
                        'title' => 'Descargar ' . h($displayName)
                    ]
                ) ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
