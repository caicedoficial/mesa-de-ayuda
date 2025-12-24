<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ComprasAttachment Entity
 *
 * @property int $id
 * @property int $compra_id
 * @property int|null $compras_comment_id
 * @property string $filename
 * @property string $original_filename
 * @property string $file_path
 * @property int $file_size
 * @property string $mime_type
 * @property bool $is_inline
 * @property string|null $content_id
 * @property int|null $uploaded_by_user_id
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Compra $compra
 * @property \App\Model\Entity\ComprasComment $compras_comment
 * @property \App\Model\Entity\User $uploaded_by_user
 */
class ComprasAttachment extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'compra_id' => true,
        'compras_comment_id' => true,
        'filename' => true,
        'original_filename' => true,
        'file_path' => true,
        'file_size' => true,
        'mime_type' => true,
        'is_inline' => true,
        'content_id' => true,
        'uploaded_by_user_id' => true,
        'created' => true,
        'compra' => true,
        'compras_comment' => true,
        'uploaded_by_user' => true,
    ];
}
