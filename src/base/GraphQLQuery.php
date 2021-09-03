<?php
namespace yii\graphql\base;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use yii\graphql\GraphQL;

class GraphQLQuery extends GraphQLField
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

    public function resolve($value, $args, $context, ResolveInfo $info)
    {
        return $this->model::find()->where($args)->all();
    }
}
