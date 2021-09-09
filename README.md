yii2-graphql
==========
Using [GraphQL](https://graphql.org/) PHP server implementation. Extends [graphql-php](https://github.com/webonyx/graphql-php) to apply to [Yii2](https://yiiframework.com).


## Guide (For Yii Basic Template)

Same as for Yii Advanced Template, but 
- instead of the `backend` namespace, it's `app` 
    - eg the namespace should be `namespace app\modules\graphql\...`
- Instead of `main.php`, it's `web.php` eg `
    - eg the config is in `config/web.php`

## Guide (For Yii Advanced Template)

### Install

Using [composer](https://getcomposer.org/)
```
composer require tsingsun/yii2-graphql
```

### Enable [Yii JsonParser](https://www.yiiframework.com/doc/api/2.0/yii-web-jsonparser)

To enable parsing for JSON requests in `backend/config/main.php`
```php
'components' => [
    'request' => [
        // ... other config
        'parsers' => [
            'application/json' => 'yii\web\JsonParser',
        ]
    ]
]
```

### Create a [GraphQLModule](https://www.yiiframework.com/doc/guide/2.0/en/structure-modules)


1. Create a folder `modules` in your base path (ie `backend`)

1. Create a `graphql` folder in the modules folder. Thus `backend/modules/graphql`

3. Create a `GraphqlModule.php` file in there with the following content:
 `backend/modules/graphql/GraphqlModule.php`
 ```php
 <?php
  namespace backend\modules\graphql;
  
  use yii\base\Module;
  use yii\graphql\GraphQLModuleTrait;
  
  class GraphqlModule extends Module{
      use GraphQLModuleTrait;
  }
 ```
 4. In `backend/config/main.php` find the `modules` config and add to it so it looks like this:
 ```php
 'modules' => [
     'graphql => [
         'class' => 'backend\modules\graphql\GraphqlModule',
     ]
 ]
 ```

### Create a Controller
1. In your `modules/graphql` folder create a `controllers` folder.
2. Create a `DefaultController.php` file in there with the following content:
```php
<?php

namespace backend\modules\graphql\controllers;

use Yii;
use yii\rest\Controller;

class DefaultController extends Controller
{
    public function actions()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return [
            'index' => [
                'class' => 'yii\graphql\GraphQLAction'
            ],
        ];
    }
}
```

### Create GraphQL Types

For a model in folder `backend/models` like the example below,
```php
<?php
/**
 * This is the model class for table "country".
 *
 * @property string $code
 * @property string $name
 * @property int $population
 * @property Person $leader
 */
class Country extends \yii\db\ActiveRecord
{
```
where Person is another Model with it's own attributes just like the `Country`

1. Create a folder in your module `modules/graphql/` and name it `types`.
2. Create a `CountryType.php` (name it after your model class, suffix with Type) the following content
```php
<?php

namespace backend\modules\graphql\types;

use GraphQL\Type\Definition\Type;
use yii\graphql\base\GraphQLType;
use yii\graphql\GraphQL;

class CountryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'country',
        'description' => 'description here'
    ];

    public function fields()
    {
        return [
            'id' => Type::id(),
            'name' => Type::string(),
            'population' => Type::int(),
            'leader' => GraphQLType::type(PersonType::class)
        ];
    }
}
```
Do the above for all the models in `backend\models`.

For a full list of the types available under Type, see Scalar Types below.



### Create GraphQL Queries
1. Create a folder in your module `modules/graphql/` and name it `queries`.
2. Create a `CountryQuery.php` (name it after your model class, suffix with Query) the following content
```php
<?php

namespace backend\modules\graphql\queries;

use GraphQL\Type\Definition\Type;
use yii\graphql\base\GraphQLQuery;
use backend\modules\graphql\types\CountryType;
use backend\models\Country;

class CountryQuery extends GraphQLQuery
{
    public $type = CountryType::class;

    public $model = Country::class;

    public function args()
    {
        return [
            'id' => Type::id(),
            'name' => Type::string(),
            'population' => Type::int(),
            'leaderId' => Type::id() 
        ];
        // replace the non-scalar type with it's id in the args
    }
}
```

Do the above for all the models in `backend\models` you want to have queries for.


### Set Up Schema
1. In `backend/modules/graphql/` create a php file `schema.php` with the content:
```php
<?php

return [
    'query' => [
        'country' => 'backend\modules\graphql\queries\CountryQuery',
        //... add all your queries here
    ],
    'mutation' => [
        //... add all your mutations here
    ],
    'types' => [
        'country' => 'backend\modules\graphql\types\CountryType',
        //... add all your types here
    ]
];

```

2. In `backend/config/main.php` in the part about `modules` add a path to the `schema.php` as follows (make sure the directory path to `schema` is right).
```php
    'modules' => [
        'graphql' => [
            'class' => 'backend\modules\graphql\GraphqlModule',
            'schema' => require __DIR__ . '/../../backend/modules/graphql/schema.php',
        ]
    ],
```



## Docs

### Type
The type system is the core of GraphQL, which is embodied in `GraphQLType`. By deconstructing the GraphQL protocol and using the [graph-php](https://github.com/webonyx/graphql-php) library to achieve fine-grained control of all elements, it is convenient to extend the class according to its own needs

#### Scalar Types
The GraphQL specification describes several built-in scalar types. In graphql-php they are exposed as static methods of the class GraphQL\Type\Definition\Type:

Data Type | GraphQL Type
---|---
id|Type::id()
int | Type::int()
string | Type::string()
boolean | Type::boolean()
float | Type::float()


#### The main elements of `GraphQLType`

The following elements can be declared in the `$attributes` property of the class, or as a method, unless stated otherwise. This also applies to all elements after this.

Element  | Type | Description
----- | ----- | -----
`name` | string | **Required** Each type needs to be named, with unique names preferred to resolve potential conflicts. The property needs to be defined in the `$attributes` property.
`description` | string | A description of the type and its use. The property needs to be defined in the `$attributes` property.
`fields` | array | **Required** The included field content is represented by the fields () method.
`resolveField` | callback | **function($value, $args, $context, GraphQL\Type\Definition\ResolveInfo $info)** For the interpretation of a field. For example: the fields definition of the user property, the corresponding method is `resolveUserField()`, and `$value` is the passed type instance defined by `type`.

### Query

`GraphQLQuery` and `GraphQLMutation` inherit `GraphQLField`. The element structure is consistent, and if you would like a reusable `Field`, you can inherit it.
Each query of `Graphql` needs to correspond to a `GraphQLQuery` object

#### The main elements of `GraphQLField`

 Element | Type  | Description
----- | ----- | -----
`type` | ObjectType | For the corresponding query type. The single type is specified by `GraphQL::type`, and a list by `Type::listOf(GraphQL::type)`.
`args` | array | The available query parameters, each of which is defined by `Field`.
`resolve` | callback | **function($value, $args, $context, GraphQL\Type\Definition\ResolveInfo $info)** `$value` is the root data, `$args` is the query parameters, `$context` is the `yii\web\Application` object, and `$info` resolves the object for the query. The root object is handled in this method.

### Mutation

Definition is similar to `GraphQLQuery`, please refer to the above.

### Simplified Field Definition

Simplifies the declarations of `Field`, removing the need to defined as an array with the type key.

#### Standard Definition

```php
//...
'id' => [
    'type' => Type::id(),
],
//...
```

#### Simplified Definition

```php
//...
'id' => Type::id(),
//...
```

### Input validation

Validation rules are supported.
In addition to graphql based validation, you can also use Yii Model validation, which is currently used for the validation of input parameters. The rules method is added directly to the mutation definition.

```php
public function rules() {
    return [
        ['password','boolean']
    ];
}
```

### Authorization verification

Since graphql queries can be combined, such as when a query merges two query, and the two query have different authorization constraints, custom authentication is required.
I refer to this query as "graphql actions"; when all graphql actions conditions are configured, it passes the authorization check.

#### Authenticate

In the behavior method of controller, the authorization method is set as follows

```php
function behaviors() {
    return [
        'authenticator'=>[
            'class' => 'yii\graphql\filter\auth\CompositeAuth',
            'authMethods' => [
                \yii\filters\auth\QueryParamAuth::class,
            ],
            'except' => ['hello']
        ],
    ];
}
```
If you want to support IntrospectionQuery authorization, the corresponding graphql action is `__schema`

#### Authorization

If the user has passed authentication, you may want to check the access for the resource. You can use `GraphqlAction`'s `checkAccess` method
in the controller. It will check all graphql actions.

```php
class GraphqlController extends Controller
{
    public function actions() {
        return [
            'index' => [
                'class' => 'yii\graphql\GraphQLAction',
                'checkAccess'=> [$this,'checkAccess'],
            ]
        ];
    }

    /**
     * authorization
     * @param $actionName
     * @throws yii\web\ForbiddenHttpException
     */
    public function checkAccess($actionName) {
        $permissionName = $this->module->id . '/' . $actionName;
        $pass = Yii::$app->getAuthManager()->checkAccess(Yii::$app->user->id,$permissionName);
        if (!$pass){
            throw new yii\web\ForbiddenHttpException('Access Denied');
        }
    }
}
```

