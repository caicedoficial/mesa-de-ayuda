<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Compra Entity
 *
 * @property int $id
 * @property string $compra_number
 * @property string|null $original_ticket_number
 * @property string $subject
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property int $requester_id
 * @property int|null $assignee_id
 * @property \Cake\I18n\DateTime|null $sla_due_date
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $resolved_at
 * @property \Cake\I18n\DateTime|null $first_response_at
 *
 * @property \App\Model\Entity\User $requester
 * @property \App\Model\Entity\User $assignee
 * @property \App\Model\Entity\ComprasComment[] $compras_comments
 * @property \App\Model\Entity\ComprasAttachment[] $compras_attachments
 * @property \App\Model\Entity\ComprasHistory[] $compras_history
 */
class Compra extends Entity
{
    protected array $_accessible = [
        'compra_number' => true,
        'original_ticket_number' => true,
        'subject' => true,
        'description' => true,
        'status' => true,
        'priority' => true,
        'requester_id' => true,
        'assignee_id' => true,
        'sla_due_date' => true,
        'created' => true,
        'modified' => true,
        'resolved_at' => true,
        'first_response_at' => true,
        'requester' => true,
        'assignee' => true,
        'compras_comments' => true,
        'compras_attachments' => true,
        'compras_history' => true,
    ];
}
