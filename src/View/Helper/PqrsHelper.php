<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\Pqr;
use App\Service\SlaManagementService;
use Cake\View\Helper;

/**
 * PQRS Helper
 *
 * Encapsulates presentation logic for PQRS views.
 */
class PqrsHelper extends Helper
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
     * Get badge color for PQRS type
     *
     * @param string $type PQRS type
     * @return string Bootstrap color class
     */
    public function getTypeColor(string $type): string
    {
        $colors = [
            'peticion' => 'primary',
            'queja' => 'warning',
            'reclamo' => 'danger',
            'sugerencia' => 'success',
        ];

        return $colors[$type] ?? 'secondary';
    }

    /**
     * Get label for PQRS type
     *
     * @param string $type PQRS type
     * @return string Human-readable label
     */
    public function getTypeLabel(string $type): string
    {
        $labels = [
            'peticion' => 'Petición',
            'queja' => 'Queja',
            'reclamo' => 'Reclamo',
            'sugerencia' => 'Sugerencia',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Get badge color for PQRS status
     *
     * @param string $status PQRS status
     * @return string Bootstrap color class
     */
    public function getStatusColor(string $status): string
    {
        $colors = [
            'nuevo' => 'warning',
            'en_revision' => 'info',
            'en_proceso' => 'primary',
            'resuelto' => 'success',
            'cerrado' => 'secondary',
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Get label for PQRS status
     *
     * @param string $status PQRS status
     * @return string Human-readable label
     */
    public function getStatusLabel(string $status): string
    {
        $labels = [
            'nuevo' => 'Nuevo',
            'en_revision' => 'En Revisión',
            'en_proceso' => 'En Proceso',
            'resuelto' => 'Resuelto',
            'cerrado' => 'Cerrado',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Render type badge
     *
     * @param string $type PQRS type
     * @return string HTML badge
     */
    public function typeBadge(string $type): string
    {
        $color = $this->getTypeColor($type);
        $label = $this->getTypeLabel($type);

        return sprintf(
            '<span class="fw-bold text-dark text-uppercase %s">%s</span>',
            h($color),
            h($label)
        );
    }

    /**
     * Render status badge
     *
     * @param string $status PQRS status
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
     * Calculate SLA status using SlaManagementService
     *
     * @param \App\Model\Entity\Pqr $pqr PQRS entity
     * @param string $type 'resolution' or 'first_response' (default: 'resolution')
     * @return array SLA data with color, percentage, and status
     */
    public function getSlaStatus(Pqr $pqr, string $type = 'resolution'): array
    {
        $slaDue = $type === 'first_response'
            ? $pqr->first_response_sla_due
            : $pqr->resolution_sla_due;

        $completedAt = $type === 'first_response'
            ? $pqr->first_response_at
            : $pqr->resolved_at;

        // If no SLA date or already completed, return N/A
        if (!$slaDue || in_array($pqr->status, ['completado', 'cerrado', 'resuelto'])) {
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
        $slaServiceStatus = $this->slaService->getSlaStatus($slaDue, $completedAt, $pqr->status);

        $now = new \Cake\I18n\DateTime();
        $created = $pqr->created;

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
     * Render simple SLA icon indicator (for index views) - Shows resolution SLA
     *
     * @param \App\Model\Entity\Pqr $pqr PQRS entity
     * @return string HTML icon
     */
    public function slaIcon(Pqr $pqr): string
    {
        $sla = $this->getSlaStatus($pqr, 'resolution');

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
     * @param \App\Model\Entity\Pqr $pqr PQRS entity
     * @return string HTML
     */
    public function dualSlaIndicator(Pqr $pqr): string
    {
        $firstResponse = $this->getSlaStatus($pqr, 'first_response');
        $resolution = $this->getSlaStatus($pqr, 'resolution');

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
}
