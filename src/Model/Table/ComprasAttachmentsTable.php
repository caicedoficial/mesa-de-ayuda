<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ComprasAttachments Model
 *
 * @property \App\Model\Table\ComprasTable&\Cake\ORM\Association\BelongsTo $Compras
 * @property \App\Model\Table\ComprasCommentsTable&\Cake\ORM\Association\BelongsTo $ComprasComments
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $UploadedByUsers
 */
class ComprasAttachmentsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('compras_attachments');
        $this->setDisplayField('original_filename');
        $this->setPrimaryKey('id');

        $this->belongsTo('Compras', [
            'foreignKey' => 'compra_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ComprasComments', [
            'foreignKey' => 'compras_comment_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('UploadedByUsers', [
            'className' => 'Users',
            'foreignKey' => 'uploaded_by_user_id',
            'joinType' => 'LEFT',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('compra_id')
            ->requirePresence('compra_id', 'create')
            ->notEmptyString('compra_id');

        $validator
            ->integer('compras_comment_id')
            ->allowEmptyString('compras_comment_id');

        $validator
            ->scalar('filename')
            ->maxLength('filename', 255)
            ->requirePresence('filename', 'create')
            ->notEmptyString('filename');

        $validator
            ->scalar('original_filename')
            ->maxLength('original_filename', 255)
            ->requirePresence('original_filename', 'create')
            ->notEmptyString('original_filename');

        $validator
            ->scalar('file_path')
            ->maxLength('file_path', 500)
            ->requirePresence('file_path', 'create')
            ->notEmptyString('file_path');

        $validator
            ->integer('file_size')
            ->allowEmptyString('file_size');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 100)
            ->allowEmptyString('mime_type');

        $validator
            ->boolean('is_inline')
            ->notEmptyString('is_inline');

        $validator
            ->scalar('content_id')
            ->maxLength('content_id', 255)
            ->allowEmptyString('content_id');

        $validator
            ->integer('uploaded_by_user_id')
            ->allowEmptyString('uploaded_by_user_id');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['compra_id'], 'Compras'), ['errorField' => 'compra_id']);
        $rules->add($rules->existsIn(['compras_comment_id'], 'ComprasComments'), ['errorField' => 'compras_comment_id']);
        $rules->add($rules->existsIn(['uploaded_by_user_id'], 'UploadedByUsers'), ['errorField' => 'uploaded_by_user_id']);

        return $rules;
    }
}
