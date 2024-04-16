<?php

namespace yiiunit\extensions\graphql\objects\types;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\Type;
use yii\graphql\base\GraphQLType;
use yii\graphql\GraphQL;
use yii\graphql\types\Types;
use yii\graphql\types\UrlType;
use yii\web\Application;
use yiiunit\extensions\graphql\data\Image;

class ImageType extends GraphQLType
{
    protected array $attributes = [
        'name' => 'image',
        'description' => 'a common image type'
    ];

    public function interfaces()
    {
        return [GraphQL::type(NodeType::class)];
    }

    public function fields()
    {
        return [
            'id' => Type::id(),
            'type' => new EnumType(
                [
                    'name' => 'ImageTypeEnum',
                    'values' => [
                        'USERPIC' => 'userpic'
                    ]
                ]
            ),
            'size' => ImageSizeEnumType::class,
            'width' => Type::int(),
            'height' => Type::int(),
            'url' => [
                'type' => GraphQL::Type(UrlType::class),
                'resolve' => fn(\yiiunit\extensions\graphql\data\Image $value, $args, \yii\web\Application $context) => $this->resolveUrl($value, $args, $context)
            ],

            // Just for the sake of example
            'fieldWithError' => [
                'type' => Type::string(),
                'resolve' => static function () {
                    throw new \Exception("Field with exception");
                }
            ],
            'nonNullFieldWithError' => [
                'type' => Type::nonNull(Type::string()),
                'resolve' => static function () {
                    throw new \Exception("Non-null field with exception");
                }
            ]
        ];
    }

    public function resolveUrl(Image $value, $args, Application $context)
    {
        $path = match ($value->type) {
            Image::TYPE_USERPIC => sprintf('/images/user/%s-%s.jpg', $value->id, $value->size),
            default => throw new \UnexpectedValueException("Unexpected image type: " . $value->type),
        };
        return $context->getHomeUrl() . $path;
    }
}
