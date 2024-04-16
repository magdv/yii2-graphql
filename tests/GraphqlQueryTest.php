<?php

namespace yiiunit\extensions\graphql;

/**
 * Created by PhpStorm.

 */
class GraphqlQueryTest extends TestCase
{
    /**
     * @var \yii\graphql\GraphQL GraphQL
     */
    protected $graphQL;


    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->mockWebApplication();
        $this->graphQL = \Yii::$app->getModule('graphql')->getGraphQL();
    }

    /**
     * test if work
     */
    public function testQueryValid()
    {
        $result = $this->graphQL->query($this->queries['hello']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }

    /**
     * test sample object query
     */
    public function testQueryWithSingleObject()
    {
        $result = $this->graphQL->query($this->queries['singleObject'], null, \Yii::$app);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }

    /**
     * test multi object in a query
     */
    public function testQueryWithMultiObject()
    {
        $result = $this->graphQL->query($this->queries['multiObject'], null, \Yii::$app);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }

    public function testQueryWithUnion()
    {
        $query = '
            query search
            {
                search(query:"a",limit:2,after:1,type:story){
                    nodes{                       
                       ... on story{
                          id                          
                       }
                    }
                }
            }
        ';
        $result = $this->graphQL->query($query, null, \Yii::$app);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }

    public function testQueryWithInterface()
    {
        $query = '
            query {
                node(id:"1",type:"story"){
                    id,
                    ... on story{
                        author{
                            id,
                            email
                        }
                    }
                }
            }
        ';
        $result = $this->graphQL->query($query, null, \Yii::$app);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('errors', $result);
    }
}
