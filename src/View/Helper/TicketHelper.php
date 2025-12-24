<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * Ticket Helper
 *
 * Encapsulates presentation logic for ticket views.
 */
class TicketHelper extends Helper
{
    /**
     * Determine if the current user can assign tickets
     *
     * @param object $ticket Ticket entity
     * @param object|null $user Current user
     * @return bool
     */
    public function canAssign($ticket, $user): bool
    {
        if (!$user) {
            return false;
        }

        $userRole = $user->get('role');

        // Compras users cannot reassign tickets
        if ($userRole === 'compras') {
            return false;
        }

        // Admin and agents can assign
        return in_array($userRole, ['admin', 'agent']);
    }

    /**
     * Get the appropriate view URL for a ticket
     *
     * @param object $ticket Ticket entity
     * @return array URL array for Html helper
     */
    public function getViewUrl($ticket): array
    {
        // If ticket is assigned to a compras user, use viewCompras
        if ($ticket->assignee && $ticket->assignee->role === 'compras') {
            return ['action' => 'view_compras', $ticket->id];
        }

        return ['action' => 'view', $ticket->id];
    }

    /**
     * Determine if assignment dropdown should be disabled
     *
     * @param object|null $user Current user
     * @return bool
     */
    public function isAssignmentDisabled($user): bool
    {
        if (!$user) {
            return true;
        }

        $userRole = $user->get('role');

        // Compras users cannot change assignments
        return $userRole === 'compras';
    }
}
