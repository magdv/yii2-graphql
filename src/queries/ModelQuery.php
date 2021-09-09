<?php
namespace yii\graphql\queries;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\graphql\base\GraphQLQuery;
use yii\graphql\GraphQL;

class ModelQuery extends GraphQLQuery
{
    public $type;
    public $model;

    public function type()
    {
        return Type::listOf(GraphQL::type($this->type));
    }

    public function args()
    {
        return [
            'id' => Type::id(),
        ];
    }

    protected function resolve($value, $args, $context, ResolveInfo $info)
    {
        return $this->model::find()->where($args)->all();
    }
}
