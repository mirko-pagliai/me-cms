<?php
namespace MeCms\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use MeCms\Model\Entity\PostsTag;

/**
 * PostsTags model
 */
class PostsTagsTable extends Table {
    /**
     * Returns a rules checker object that will be used for validating application integrity
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules) {
        $rules->add($rules->existsIn(['tag_id'], 'Tags'));
        $rules->add($rules->existsIn(['post_id'], 'Posts'));
        return $rules;
    }
	
    /**
     * Initialize method
     * @param array $config The table configuration
     */
    public function initialize(array $config) {
        $this->table('posts_tags');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('CounterCache', ['Tags' => ['post_count']]);
        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id',
            'className' => 'MeCms.Tags'
        ]);
        $this->belongsTo('Posts', [
            'foreignKey' => 'post_id',
            'className' => 'MeCms.Posts'
        ]);
    }

    /**
     * Default validation rules
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator) {
        return $validator;
    }
}