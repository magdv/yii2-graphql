<?php

/**
 * Created by PhpStorm.
 * User: tsingsun
 * Date: 2017/5/16
 * Time: 下午4:14
 */

namespace yiiunit\extensions\graphql;

use yii\web\Controller;

class DefaultController extends Controller
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }


    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\graphql\GraphQLAction',
            ]
        ];
    }
}
