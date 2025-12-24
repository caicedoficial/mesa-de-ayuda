<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PqrsAttachments Model
 *
 * @property \App\Model\Table\PqrsTable&\Cake\ORM\Association\BelongsTo $Pqrs
 * @property \App\Model\Table\PqrsCommentsTable&\Cake\ORM\Association\BelongsTo $PqrsComments
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $UploadedByUsers
 *
 * @method \App\Model\Entity\PqrsAttachment newEmptyEntity()
 * @method \App\Model\Entity\PqrsAttachment newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\PqrsAttachment> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PqrsAttachment get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PqrsAttachment findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PqrsAttachment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\PqrsAttachment> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PqrsAttachment|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PqrsAttachment saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class PqrsAttachmentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('pqrs_attachments');
        $this->setDisplayField('original_filename');
        $this->setPrimaryKey('id');

        $this->belongsTo('Pqrs', [
            'foreignKey' => 'pqrs_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('PqrsComments', [
            'foreignKey' => 'pqrs_comment_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('UploadedByUsers', [
            'className' => 'Users',
            'foreignKey' => 'uploaded_by_user_id',
            'joinType' => 'LEFT',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('pqrs_id')
            ->requirePresence('pqrs_id', 'create')
            ->notEmptyString('pqrs_id');

        $validator
            ->integer('pqrs_comment_id')
            ->allowEmptyString('pqrs_comment_id');

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
            ->requirePresence('file_size', 'create')
            ->notEmptyString('file_size');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 100)
            ->requirePresence('mime_type', 'create')
            ->notEmptyString('mime_type');

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

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['pqrs_id'], 'Pqrs'), ['errorField' => 'pqrs_id']);
        $rules->add($rules->existsIn(['pqrs_comment_id'], 'PqrsComments'), ['errorField' => 'pqrs_comment_id']);
        $rules->add($rules->existsIn(['uploaded_by_user_id'], 'UploadedByUsers'), ['errorField' => 'uploaded_by_user_id']);

        return $rules;
    }
}
