<?php

/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2016/11/15
 * Time: 上午10:25
 */

namespace yii\graphql\base;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use yii\base\InvalidConfigException;
use yii\graphql\GraphQL;

/**
 * Class GraphQLUnionType for UnionType
 * @package yii\graphql\base
 */
class GraphQLUnionType extends GraphQLType
{
    /**
     * @return Type[]
     */
    public function types()
    {
        return [];
    }

    protected function getTypeResolver()
    {
        if (!method_exists($this, 'resolveType')) {
            throw new InvalidConfigException(static::class . ' must implement resolveType method');
        }

        $resolver = [$this, 'resolveType'];
        return static fn(...$args) => $resolver(...$args);
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes($name = null, $except = null)
    {
        $attributes = $this->attributes;

        $resolver = $this->getTypeResolver();
        if (isset($resolver)) {
            $attributes['resolveType'] = $resolver;
        }

        $types = array_map(
            static function ($item) {
                if (is_string($item)) {
                    return GraphQL::type($item);
                } else {
                    return $item;
                }
            },
            static::types()
        );

        $attributes['types'] = $types;
        //TODO support $name and $except??
        return $attributes;
    }

    public function toType()
    {
        return new UnionType($this->toArray());
    }
}
