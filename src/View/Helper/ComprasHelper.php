<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\Compra;
use App\Service\SlaManagementService;
use Cake\View\Helper;

/**
 * Compras Helper
 *
 * Encapsulates presentation logic for purchase order views.
 */
class ComprasHelper extends Helper
{
    private SlaManagementService $slaService;

    /**
     * Initialize
     *
     * @param array $config Configuration
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->slaService = new SlaManagementService();
    }
    /**
     * Get badge color for compra status
     *
     * @param string $status Compra status
     * @return string Bootstrap color class
     */
    public function getStatusColor(string $status): string
    {
        $colors = [
            'nuevo' => 'info',
            'en_revision' => 'warning',
            'aprobado' => 'success',
            'en_proceso' => 'primary',
            'completado' => 'success',
            'rechazado' => 'danger',
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Get label for compra status
     *
     * @param string $status Compra status
     * @return string Human-readable label
     */
    public function getStatusLabel(string $status): string
    {
        $labels = [
            'nuevo' => 'Nuevo',
            'en_revision' => 'En Revisión',
            'aprobado' => 'Aprobado',
            'en_proceso' => 'En Proceso',
            'completado' => 'Completado',
            'rechazado' => 'Rechazado',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get badge color for priority
     *
     * @param string $priority Priority level
     * @return string Bootstrap color class
     */
    public function getPriorityColor(string $priority): string
    {
        $colors = [
            'baja' => 'secondary',
            'media' => 'primary',
            'alta' => 'warning',
            'urgente' => 'danger',
        ];

        return $colors[$priority] ?? 'secondary';
    }

    /**
     * Get label for priority
     *
     * @param string $priority Priority level
     * @return string Human-readable label
     */
    public function getPriorityLabel(string $priority): string
    {
        $labels = [
            'baja' => 'Baja',
            'media' => 'Media',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];

        return $labels[$priority] ?? ucfirst($priority);
    }

    /**
     * Render status badge
     *
     * @param string $status Compra status
     * @return string HTML badge
     */
    public function statusBadge(string $status): string
    {
        $color = $this->getStatusColor($status);
        $label = $this->getStatusLabel($status);

        return sprintf(
            '<span style="border-radius: 8px;" class="small px-2 py-1 text-white fw-bold text-uppercase bg-%s">%s</span>',
            h($color),
            h($label)
        );
    }

    /**
     * Render priority badge
     *
     * @param string $priority Priority level
     * @return string HTML badge
     */
    public function priorityBadge(string $priority): string
    {
        $color = $this->getPriorityColor($priority);
        $label = $this->getPriorityLabel($priority);

        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            h($color),
            h($label)
        );
    }

    /**
     * Get view URL for a compra
     *
     * @param \App\Model\Entity\Compra $compra Compra entity
     * @return array URL array for Router
     */
    public function getViewUrl(Compra $compra): array
    {
        return [
            'controller' => 'Compras',
            'action' => 'view',
            $compra->id
        ];
    }

    /**
     * Calculate SLA status using SlaManagementService (resolution SLA priority)
     *
     * @param \App\Model\Entity\Compra $compra Compra entity
     * @param string $type 'resolution' or 'first_response' (default: 'resolution')
     * @return array SLA data with color, percentage, and status
     */
    public function getSlaStatus(Compra $compra, string $type = 'resolution'): array
    {
        // Use new fields if available, fallback to legacy
        $slaDue = $type === 'first_response'
            ? $compra->first_response_sla_due
            : ($compra->resolution_sla_due ?? $compra->sla_due_date);

        $completedAt = $type === 'first_response'
            ? $compra->first_response_at
            : $compra->resolved_at;

        // If no SLA date or already completed, return N/A
        if (!$slaDue || in_array($compra->status, ['completado', 'rechazado', 'convertido'])) {
            return [
                'color' => 'secondary',
                'textColor' => 'text-muted',
                'bgColor' => 'bg-secondary',
                'percentage' => 0,
                'status' => 'completed',
                'label' => 'N/A',
                'icon' => 'bi-check-circle',
                'type' => $type,
            ];
        }

        // Use SlaManagementService to get status
        $slaServiceStatus = $this->slaService->getSlaStatus($slaDue, $completedAt, $compra->status);

        $now = new \Cake\I18n\DateTime();
        $created = $compra->created;

        // Calculate time elapsed and remaining
        $totalSeconds = $slaDue->diffInSeconds($created);
        $elapsedSeconds = $now->diffInSeconds($created);
        $remainingSeconds = $slaDue->diffInSeconds($now);

        // Calculate percentage of time used (0-100)
        $percentageUsed = $totalSeconds > 0 ? ($elapsedSeconds / $totalSeconds) * 100 : 100;
        $percentageUsed = min(100, max(0, $percentageUsed));

        // Map SlaManagementService status to helper format
        $statusMap = [
            'met' => [
                'color' => 'success',
                'textColor' => 'text-success',
                'bgColor' => 'bg-success',
                'icon' => 'bi-check-circle-fill',
                'label' => 'Cumplido',
            ],
            'breached' => [
                'color' => 'danger',
                'textColor' => 'text-danger',
                'bgColor' => 'bg-danger',
                'icon' => 'bi-exclamation-triangle-fill',
                'label' => 'Vencido',
                'hoursOver' => ceil($remainingSeconds / 3600),
            ],
            'breached_resolved' => [
                'color' => 'warning',
                'textColor' => 'text-warning',
                'bgColor' => 'bg-warning',
                'icon' => 'bi-exclamation-circle',
                'label' => 'Vencido (Resuelto)',
            ],
            'approaching' => [
                'color' => 'warning',
                'textColor' => 'text-warning',
                'bgColor' => 'bg-warning',
                'icon' => 'bi-exclamation-circle-fill',
                'label' => 'Próximo a vencer',
                'hoursLeft' => ceil($remainingSeconds / 3600),
            ],
            'on_track' => [
                'color' => 'success',
                'textColor' => 'text-success',
                'bgColor' => 'bg-success',
                'icon' => 'bi-check-circle-fill',
                'label' => 'En tiempo',
                'hoursLeft' => ceil($remainingSeconds / 3600),
            ],
            'none' => [
                'color' => 'secondary',
                'textColor' => 'text-muted',
                'bgColor' => 'bg-secondary',
                'icon' => 'bi-dash-circle',
                'label' => 'N/A',
            ],
        ];

        $statusInfo = $statusMap[$slaServiceStatus['status']] ?? $statusMap['none'];
        $statusInfo['percentage'] = round($percentageUsed, 1);
        $statusInfo['status'] = $slaServiceStatus['status'];
        $statusInfo['type'] = $type;
        $statusInfo['sla_due'] = $slaDue;

        return $statusInfo;
    }

    /**
     * Render SLA badge with traffic light colors
     *
     * @param \App\Model\Entity\Compra $compra Compra entity
     * @param bool $showPercentage Show percentage in badge (default: false)
     * @return string HTML badge
     */
    public function slaBadge(Compra $compra, bool $showPercentage = false): string
    {
        $sla = $this->getSlaStatus($compra);

        if ($sla['status'] === 'completed') {
            return '<span class="text-muted">N/A</span>';
        }

        $label = $sla['label'];
        if ($showPercentage && isset($sla['hoursLeft'])) {
            $label .= ' (' . $sla['hoursLeft'] . 'h)';
        } elseif ($showPercentage && isset($sla['hoursOver'])) {
            $label .= ' (+' . $sla['hoursOver'] . 'h)';
        }

        return sprintf(
            '<span class="badge %s"><i class="%s"></i> %s</span>',
            h($sla['bgColor']),
            h($sla['icon']),
            h($label)
        );
    }

    /**
     * Render simple SLA icon indicator (for index views) - Shows resolution SLA
     *
     * @param \App\Model\Entity\Compra $compra Compra entity
     * @return string HTML icon
     */
    public function slaIcon(Compra $compra): string
    {
        $sla = $this->getSlaStatus($compra, 'resolution');

        if ($sla['status'] === 'completed' || !isset($sla['sla_due'])) {
            return '<i class="bi bi-dash-circle text-muted" title="N/A"></i>';
        }

        $dateFormatted = $sla['sla_due']->format('h:m a, d M');

        // Build tooltip text
        $tooltip = $sla['label'] . ' (Resolución) - ' . $dateFormatted;
        if (isset($sla['hoursLeft'])) {
            $tooltip .= ' (' . $sla['hoursLeft'] . 'h restantes)';
        } elseif (isset($sla['hoursOver'])) {
            $tooltip .= ' (+' . $sla['hoursOver'] . 'h de retraso)';
        }

        return sprintf(
            '<i class="%s %s" style="font-size: 1.2rem;" title="%s"></i>',
            h($sla['icon']),
            h($sla['textColor']),
            h($tooltip)
        );
    }

    /**
     * Render dual SLA indicator showing both first response and resolution
     *
     * @param \App\Model\Entity\Compra $compra Compra entity
     * @return string HTML
     */
    public function dualSlaIndicator(Compra $compra): string
    {
        $firstResponse = $this->getSlaStatus($compra, 'first_response');
        $resolution = $this->getSlaStatus($compra, 'resolution');

        $html = '<div class="d-flex flex-column gap-2">';

        // First Response SLA
        $html .= '<div class="p-2 border rounded" style="background-color: #f8f9fa;">';
        $html .= '<div class="d-flex align-items-center justify-content-between mb-1">';
        $html .= '<small class="text-muted fw-semibold">Primera Respuesta</small>';
        if ($firstResponse['status'] === 'completed') {
            $html .= '<span class="badge bg-secondary">N/A</span>';
        } else {
            $html .= sprintf(
                '<i class="%s %s" style="font-size: 1.1rem;"></i>',
                h($firstResponse['icon']),
                h($firstResponse['textColor'])
            );
        }
        $html .= '</div>';

        if ($firstResponse['status'] !== 'completed') {
            $html .= sprintf(
                '<div class="%s fw-semibold small">%s</div>',
                h($firstResponse['textColor']),
                h($firstResponse['label'])
            );
            if (isset($firstResponse['sla_due'])) {
                $html .= sprintf(
                    '<div class="small text-muted mt-1">%s</div>',
                    h($firstResponse['sla_due']->format('d M Y, h:i a'))
                );
            }
        }
        $html .= '</div>';

        // Resolution SLA
        $html .= '<div class="p-2 border rounded" style="background-color: #f8f9fa;">';
        $html .= '<div class="d-flex align-items-center justify-content-between mb-1">';
        $html .= '<small class="text-muted fw-semibold">Resolución</small>';
        if ($resolution['status'] === 'completed') {
            $html .= '<span class="badge bg-secondary">N/A</span>';
        } else {
            $html .= sprintf(
                '<i class="%s %s" style="font-size: 1.1rem;"></i>',
                h($resolution['icon']),
                h($resolution['textColor'])
            );
        }
        $html .= '</div>';

        if ($resolution['status'] !== 'completed') {
            $html .= sprintf(
                '<div class="%s fw-semibold small">%s</div>',
                h($resolution['textColor']),
                h($resolution['label'])
            );
            if (isset($resolution['sla_due'])) {
                $html .= sprintf(
                    '<div class="small text-muted mt-1">%s</div>',
                    h($resolution['sla_due']->format('d M Y, h:i a'))
                );
            }
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Render detailed SLA indicator with date and icon (for detail views)
     *
     * @param \App\Model\Entity\Compra $compra Compra entity
     * @param bool $showProgressBar Show progress bar (default: false)
     * @return string HTML indicator
     */
    public function slaIndicator(Compra $compra, bool $showProgressBar = false): string
    {
        if (!$compra->sla_due_date || in_array($compra->status, ['completado', 'rechazado'])) {
            return '<span class="text-muted">N/A</span>';
        }

        $sla = $this->getSlaStatus($compra);
        $dateFormatted = $compra->sla_due_date->format('h:m a, d');

        $html = '<div class="d-flex align-items-center gap-2">';
        $html .= sprintf('<i class="%s %s"></i>', h($sla['icon']), h($sla['textColor']));

        if ($sla['status'] === 'breached') {
            $html .= sprintf(
                '<span class="%s fw-bold">%s</span>',
                h($sla['textColor']),
                h($dateFormatted)
            );
        } else {
            $html .= sprintf(
                '<span class="%s">%s</span>',
                h($sla['textColor']),
                h($dateFormatted)
            );
        }

        if (isset($sla['hoursLeft'])) {
            $html .= sprintf(
                '<small class="%s">(%sh)</small>',
                h($sla['textColor']),
                h($sla['hoursLeft'])
            );
        } elseif (isset($sla['hoursOver'])) {
            $html .= sprintf(
                '<small class="%s">(+%sh)</small>',
                h($sla['textColor']),
                h($sla['hoursOver'])
            );
        }

        $html .= '</div>';

        // Optional progress bar
        if ($showProgressBar && $sla['status'] !== 'breached') {
            $html .= sprintf(
                '<div class="progress mt-1" style="height: 6px;">
                    <div class="progress-bar %s" role="progressbar" style="width: %s%%" aria-valuenow="%s" aria-valuemin="0" aria-valuemax="100"></div>
                </div>',
                h($sla['bgColor']),
                h($sla['percentage']),
                h($sla['percentage'])
            );
        }

        return $html;
    }
}
